<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Throwable;

class SignalVideoConverter
{
    public function normalizeForSignalAttachment(UploadedFile $file): array
    {
        $mimeType = (string) ($file->getMimeType() ?: $file->getClientMimeType());

        if (! str_starts_with($mimeType, 'video/')) {
            return [
                'file' => $file,
                'temporary_path' => null,
                'converted' => false,
            ];
        }

        $inputPath = $file->getRealPath();

        if (! is_string($inputPath) || $inputPath === '') {
            throw ValidationException::withMessages([
                'signal_attachment' => ['La vidéo envoyée est illisible.'],
            ]);
        }

        $temporaryDirectory = storage_path('app/private/tmp/videos');
        File::ensureDirectoryExists($temporaryDirectory);

        $temporaryPath = tempnam($temporaryDirectory, 'signal_video_');

        if ($temporaryPath === false) {
            throw ValidationException::withMessages([
                'signal_attachment' => ['Impossible de préparer la conversion de la vidéo.'],
            ]);
        }

        $outputPath = $temporaryPath.'.mp4';
        @unlink($temporaryPath);

        try {
            $process = new Process([
                'ffmpeg',
                '-y',
                '-i',
                $inputPath,
                '-map',
                '0:v:0',
                '-map',
                '0:a?',
                '-t',
                '12',
                '-c:v',
                'libx264',
                '-preset',
                'veryfast',
                '-profile:v',
                'main',
                '-pix_fmt',
                'yuv420p',
                '-movflags',
                '+faststart',
                '-c:a',
                'aac',
                '-b:a',
                '128k',
                $outputPath,
            ]);
            $process->setTimeout(max(30, (int) config('services.public_reports.video_conversion_timeout', 120)));
            $process->run();

            if (! $process->isSuccessful() || ! is_file($outputPath) || filesize($outputPath) === 0) {
                $failureOutput = $this->shorten($process->getErrorOutput() ?: $process->getOutput());
                @unlink($outputPath);

                throw ValidationException::withMessages([
                    'signal_attachment' => ['Impossible de convertir la vidéo au format MP4.'],
                    'signal_attachment_cause' => [$failureOutput ?: 'ffmpeg n’a pas pu produire un fichier MP4 valide.'],
                ]);
            }

            $originalName = pathinfo($file->getClientOriginalName() ?: 'video-signalement', PATHINFO_FILENAME);
            $convertedFile = new UploadedFile(
                $outputPath,
                $this->sanitizeFilename($originalName).'.mp4',
                'video/mp4',
                null,
                true
            );

            return [
                'file' => $convertedFile,
                'temporary_path' => $outputPath,
                'converted' => true,
                'original' => [
                    'name' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size' => $file->getSize(),
                ],
            ];
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            @unlink($outputPath);

            throw ValidationException::withMessages([
                'signal_attachment' => ['Impossible de convertir la vidéo au format MP4.'],
                'signal_attachment_cause' => [$this->shorten($exception->getMessage())],
            ]);
        }
    }

    public function cleanup(?string $temporaryPath): void
    {
        if (is_string($temporaryPath) && $temporaryPath !== '' && is_file($temporaryPath)) {
            @unlink($temporaryPath);
        }
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $filename) ?: 'video-signalement';

        return trim($filename, '-') ?: 'video-signalement';
    }

    private function shorten(string $value, int $limit = 700): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return mb_substr($value, 0, $limit).'...';
    }
}
