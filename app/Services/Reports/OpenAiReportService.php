<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiReportService
{
    public function summarize(array $report): array
    {
        if (! (bool) config('openai.reports_enabled') || blank(config('openai.api_key'))) {
            return [
                'enabled' => false,
                'text' => $this->localSummary($report),
                'notice' => 'Analyse OpenAI désactivée ou clé absente.',
            ];
        }

        try {
            $response = Http::withToken((string) config('openai.api_key'))
                ->acceptJson()
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => (string) config('openai.report_model', 'gpt-4.1-mini'),
                    'store' => false,
                    'max_output_tokens' => max(80, min((int) config('openai.report_max_output_tokens', 180), 400)),
                    'input' => $this->prompt($report),
                ]);

            if (! $response->successful()) {
                return [
                    'enabled' => true,
                    'text' => $this->localSummary($report),
                    'notice' => 'Analyse OpenAI indisponible pour cette génération.',
                ];
            }

            return [
                'enabled' => true,
                'text' => trim((string) ($response->json('output_text') ?: data_get($response->json(), 'output.0.content.0.text'))) ?: $this->localSummary($report),
                'notice' => null,
            ];
        } catch (Throwable) {
            return [
                'enabled' => true,
                'text' => $this->localSummary($report),
                'notice' => 'Analyse OpenAI remplacée par une synthèse locale.',
            ];
        }
    }

    private function prompt(array $report): string
    {
        return "Synthèse FR ultra-courte. Format strict: 1 ligne Bilan, 2 lignes Points clés, 1 ligne Action. Aucun chiffre inventé. Pas d'introduction.\n".json_encode($this->compactPayload($report), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function localSummary(array $report): string
    {
        $summary = $report['summary'] ?? [];
        $count = $summary['Nombre'] ?? $summary['Signalements'] ?? $report['record_count'] ?? 0;

        return 'Bilan : '.$count.' élément(s) sur la période sélectionnée. Points clés : consultez les indicateurs et groupes du tableau. Action : prioriser les groupes les plus élevés.';
    }

    private function compactPayload(array $report): array
    {
        $payload = [
            't' => $report['title'] ?? 'Rapport',
            'p' => [
                $report['filters']['Période du'] ?? null,
                $report['filters']['Période au'] ?? null,
            ],
            'k' => $this->compactMap($report['summary'] ?? []),
        ];

        if ((bool) config('openai.report_include_rows', false)) {
            $payload['g'] = collect($report['rows'] ?? [])
                ->take(max(0, min((int) config('openai.report_top_rows', 5), 10)))
                ->map(fn (array $row) => $this->compactMap($row))
                ->values()
                ->all();
        }

        return $payload;
    }

    private function compactMap(array $values): array
    {
        $labels = [
            'Groupe' => 'g',
            'Nombre' => 'n',
            'Montant' => 'm',
            'Payés' => 'p',
            'Résolus' => 'r',
            'Dommages' => 'd',
            'Actifs' => 'a',
            'Expirés' => 'e',
            'Modules analysés' => 'mod',
            'Signalements' => 'sig',
            'Paiements' => 'pay',
            'Cartes privilèges' => 'cp',
        ];

        return collect($values)
            ->mapWithKeys(fn ($value, $key) => [$labels[$key] ?? mb_substr((string) $key, 0, 8) => is_numeric($value) ? (float) $value : (string) $value])
            ->all();
    }
}
