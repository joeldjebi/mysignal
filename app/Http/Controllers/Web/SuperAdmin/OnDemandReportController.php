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
        $analysis = $report ? $ai->summarize($report) : null;

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
        $analysis = $request->boolean('with_ai') ? $ai->summarize($report) : null;

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
            'format' => ['nullable', 'string', 'in:html,csv,xls,pdf,pptx'],
            'with_ai' => ['nullable', 'boolean'],
            'preview' => ['nullable', 'boolean'],
        ]);

        return [
            'subject' => $validated['subject'] ?? 'global',
            'date_from' => $validated['date_from'] ?? now()->startOfMonth()->toDateString(),
            'date_to' => $validated['date_to'] ?? now()->toDateString(),
            'application_id' => $validated['application_id'] ?? null,
            'organization_id' => $validated['organization_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'group_by' => $validated['group_by'] ?? 'none',
            'second_group_by' => $validated['second_group_by'] ?? 'none',
            'metrics' => $validated['metrics'] ?? ['count', 'amount', 'paid', 'resolved'],
            'format' => $validated['format'] ?? 'html',
            'with_ai' => (bool) ($validated['with_ai'] ?? false),
            'preview' => (bool) ($validated['preview'] ?? false),
        ];
    }
}
