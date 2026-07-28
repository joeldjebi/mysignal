<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Reports\OnDemandReportService;
use App\Services\Reports\OpenAiReportService;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OnDemandReportController extends Controller
{
    public function index(Request $request, OnDemandReportService $reports, OpenAiReportService $ai): View
    {
        $filters = $this->filters($request);
        $report = $request->boolean('preview') ? $reports->build($filters) : null;
        $useAi = (bool) config('openai.reports_enabled') && filled(config('openai.api_key'));
        $analysis = $report
            ? ($useAi ? $ai->summarize($report) : $reports->localAnalysis($report))
            : null;

        return view('super-admin.on-demand-reports.index', [
            'options' => $reports->options(),
            'filters' => $filters,
            'report' => $report,
            'analysis' => $analysis,
        ]);
    }

    public function download(Request $request, OnDemandReportService $reports, OpenAiReportService $ai, ReportExportService $exports): Response
    {
        $filters = $this->filters($request);
        $format = (string) $request->input('format', 'csv');
        $report = $reports->build($filters);
        $useAi = (bool) config('openai.reports_enabled') && filled(config('openai.api_key'));
        $analysis = $useAi ? $ai->summarize($report) : $reports->localAnalysis($report);

        return $exports->download($report, $format, $analysis);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'in:global,reports,payments,privilege_cards,privilege_purchases,privilege_scans,public_users,reparation_cases,activity_logs'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'status' => ['nullable', 'string', 'max:60'],
            'group_by' => ['nullable', 'string', 'in:none,day,month,application,organization,status,payment_status,card_type,commune,partner'],
            'second_group_by' => ['nullable', 'string', 'in:none,day,month,application,organization,status,payment_status,card_type,commune,partner'],
            'metrics' => ['nullable', 'array'],
            'metrics.*' => ['string', 'in:count,amount,paid,resolved,damages,active,expired'],
            'metrics_present' => ['nullable', 'boolean'],
            'format' => ['nullable', 'string', 'in:html,csv,xls,pdf,pptx'],
            'preview' => ['nullable', 'boolean'],
        ]);

        $defaultMetrics = ['count', 'amount', 'paid', 'resolved'];

        return [
            'subject' => $validated['subject'] ?? 'global',
            'date_from' => $validated['date_from'] ?? now()->startOfMonth()->toDateString(),
            'date_to' => $validated['date_to'] ?? now()->toDateString(),
            'application_id' => $validated['application_id'] ?? null,
            'organization_id' => $validated['organization_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'group_by' => $validated['group_by'] ?? 'none',
            'second_group_by' => $validated['second_group_by'] ?? 'none',
            'metrics' => array_key_exists('metrics', $validated)
                ? $validated['metrics']
                : ($request->boolean('metrics_present') ? [] : $defaultMetrics),
            'format' => $validated['format'] ?? 'html',
            'preview' => (bool) ($validated['preview'] ?? false),
        ];
    }
}
