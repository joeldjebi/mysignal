<?php

namespace App\Http\Requests\Api\V1\Public\Reports;

use App\Models\IncidentReport;
use App\Support\Audit\ActivityLogger;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Process\Process;
use Throwable;

class StoreIncidentReportRequest extends FormRequest
{
    private const MAX_VIDEO_DURATION_SECONDS = 12.0;

    public function authorize(): bool
    {
        $timeout = (int) config('services.public_reports.request_timeout', 240);

        if ($timeout > 0) {
            @set_time_limit($timeout);
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'meter_id' => ['nullable', 'integer', 'exists:meters,id'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'organization_type_id' => ['nullable', 'integer', 'exists:organization_types,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'signal_code' => ['required', 'string'],
            'signal_sub_type_code' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'occurred_at' => ['nullable', 'date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'location_source' => ['nullable', 'string', 'max:30'],
            'signal_attachment' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,video/mp4,video/quicktime,video/x-msvideo,video/mpeg'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            $file = $this->file('signal_attachment');

            if (! $file instanceof UploadedFile) {
                return;
            }

            $mimeType = (string) ($file->getMimeType() ?: $file->getClientMimeType());

            if (! str_starts_with($mimeType, 'video/')) {
                return;
            }

            $duration = $this->videoDurationInSeconds($file);

            if ($duration !== null && $duration > self::MAX_VIDEO_DURATION_SECONDS) {
                $validator->errors()->add(
                    'signal_attachment',
                    'La vidéo ne doit pas dépasser 12 secondes.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'application_id' => 'catégorie',
            'organization_type_id' => 'sous catégorie',
            'organization_id' => 'institution',
            'meter_id' => 'identifiant',
            'signal_code' => 'type de signal',
            'signal_sub_type_code' => 'sous-type de signal',
        ];
    }

    protected function failedValidation(ValidationValidator $validator): void
    {
        try {
            app(ActivityLogger::class)->log(
                'public.report.validation_failed',
                'Échec de validation lors de la création d’un signalement public.',
                IncidentReport::class,
                [
                    'errors' => $validator->errors()->toArray(),
                    'payload' => $this->safePayloadForLog(),
                    'signal_attachment' => $this->signalAttachmentForLog(),
                ],
                $this,
                $this->user('public_api'),
                'public'
            );
        } catch (Throwable) {
            // La journalisation ne doit jamais empêcher la réponse de validation.
        }

        parent::failedValidation($validator);
    }

    private function videoDurationInSeconds(UploadedFile $file): ?float
    {
        try {
            $path = $file->getRealPath();

            if (! is_string($path) || $path === '') {
                return null;
            }

            $process = new Process([
                'ffprobe',
                '-v',
                'error',
                '-show_entries',
                'format=duration',
                '-of',
                'default=noprint_wrappers=1:nokey=1',
                $path,
            ]);
            $process->setTimeout(10);
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            $duration = trim($process->getOutput());

            return is_numeric($duration) ? (float) $duration : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function safePayloadForLog(): array
    {
        return $this->only([
            'meter_id',
            'application_id',
            'organization_type_id',
            'organization_id',
            'signal_code',
            'signal_sub_type_code',
            'occurred_at',
            'latitude',
            'longitude',
            'location_accuracy',
            'location_source',
        ]);
    }

    private function signalAttachmentForLog(): array
    {
        $file = $this->file('signal_attachment');

        if (! $file instanceof UploadedFile) {
            return ['present' => false];
        }

        return [
            'present' => true,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'client_mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'error' => $file->getError(),
        ];
    }
}
