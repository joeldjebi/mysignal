<?php

namespace App\Services\Reports;

use App\Models\IncidentReport;
use App\Models\IncidentReportPaymentSession;
use App\Models\ReparationCase;
use App\Models\RexFeedback;
use App\Services\WasabiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class IncidentReportDeletionService
{
    public function __construct(private readonly WasabiService $wasabiService)
    {
    }

    public function delete(IncidentReport $report): array
    {
        $report->loadMissing([
            'payments',
            'notificationContexts',
            'reparationCase.steps',
            'reparationCase.histories',
        ]);

        $paymentSessions = IncidentReportPaymentSession::query()
            ->where('incident_report_id', $report->id)
            ->get();

        $filePaths = $this->filePathsFor($report, $paymentSessions);
        $summary = [
            'payments' => $report->payments->count(),
            'payment_sessions' => $paymentSessions->count(),
            'notification_contexts' => $report->notificationContexts->count(),
            'reparation_cases' => $report->reparationCase instanceof ReparationCase ? 1 : 0,
            'rex_feedbacks' => RexFeedback::query()->where('incident_report_id', $report->id)->count(),
            'files' => count($filePaths),
            'failed_files' => 0,
        ];

        DB::transaction(function () use ($report, $paymentSessions): void {
            RexFeedback::query()
                ->where('incident_report_id', $report->id)
                ->delete();

            $paymentSessions->each->delete();
            $report->payments()->delete();
            $report->notificationContexts()->delete();
            $report->reparationCase?->delete();
            $report->delete();
        });

        foreach ($filePaths as $path) {
            try {
                $this->wasabiService->deleteFile($path);
            } catch (Throwable $exception) {
                $summary['failed_files']++;

                Log::warning('Impossible de supprimer un fichier lie a un signalement supprime.', [
                    'incident_report_id' => $report->id,
                    'path' => $path,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $summary;
    }

    private function filePathsFor(IncidentReport $report, iterable $paymentSessions): array
    {
        $payloads = [
            $report->signal_attachment,
            $report->damage_attachment,
            $report->signal_payload,
        ];

        foreach ($paymentSessions as $paymentSession) {
            $payloads[] = $paymentSession->signal_attachment;
            $payloads[] = $paymentSession->damage_attachment;
            $payloads[] = $paymentSession->report_payload;
            $payloads[] = $paymentSession->damage_payload;
        }

        if ($report->reparationCase instanceof ReparationCase) {
            foreach ($report->reparationCase->steps as $step) {
                $payloads[] = $step->meta;
            }

            foreach ($report->reparationCase->histories as $history) {
                $payloads[] = $history->meta;
            }
        }

        return collect($payloads)
            ->flatMap(fn (mixed $payload): array => $this->extractFilePaths($payload))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function extractFilePaths(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $paths = [];

        if (filled($payload['path'] ?? null)) {
            $paths[] = (string) $payload['path'];
        }

        foreach ($payload as $value) {
            if (is_array($value)) {
                array_push($paths, ...$this->extractFilePaths($value));
            }
        }

        return $paths;
    }
}
