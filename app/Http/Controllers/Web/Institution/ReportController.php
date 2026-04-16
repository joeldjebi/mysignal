<?php

namespace App\Http\Controllers\Web\Institution;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\Commune;
use App\Models\IncidentReport;
use App\Models\Meter;
use App\Support\Audit\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $query = $this->institutionReportsQuery($context['network_type'], $context['application_id'], $context['organization_id']);
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $context['feature_codes'], true);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('signal_label', 'like', '%'.$search.'%')
                    ->orWhere('signal_code', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($canViewPaymentInfo && filled(request('payment_status'))) {
            $query->where('payment_status', request('payment_status'));
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('commune_id'))) {
            $query->where('commune_id', request('commune_id'));
        }

        if (filled(request('meter_id'))) {
            $query->where('meter_id', request('meter_id'));
        }

        return view('institution.reports.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'reports',
            'reports' => $query->latest()->paginate(15)->withQueryString(),
            'meters' => Meter::query()
                ->when($context['organization_id'] !== null, fn ($builder) => $builder->where('organization_id', $context['organization_id']))
                ->when($context['application_id'] !== null, fn ($builder) => $builder->where('application_id', $context['application_id']))
                ->when($context['network_type'] !== null, fn ($builder) => $builder->where('network_type', $context['network_type']))
                ->whereIn(
                    'id',
                    IncidentReport::query()
                        ->when($context['organization_id'] !== null, fn ($builder) => $builder->where('organization_id', $context['organization_id']))
                        ->when($context['application_id'] !== null, fn ($builder) => $builder->where('application_id', $context['application_id']))
                        ->when($context['network_type'] !== null, fn ($builder) => $builder->where('network_type', $context['network_type']))
                        ->whereNotNull('meter_id')
                        ->distinct()
                        ->select('meter_id')
                )
                ->orderBy('meter_number')
                ->orderBy('label')
                ->get(['id', 'meter_number', 'label']),
            'communes' => Commune::query()
                ->whereIn(
                    'id',
                    IncidentReport::query()
                        ->when($context['organization_id'] !== null, fn ($builder) => $builder->where('organization_id', $context['organization_id']))
                        ->when($context['application_id'] !== null, fn ($builder) => $builder->where('application_id', $context['application_id']))
                        ->when($context['network_type'] !== null, fn ($builder) => $builder->where('network_type', $context['network_type']))
                        ->whereNotNull('commune_id')
                        ->distinct()
                        ->select('commune_id')
                )
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, IncidentReport $report): View
    {
        abort_unless($this->canManageReport($request, $report), 403);

        $context = $this->institutionContext();
        $report->load([
            'meter',
            'commune',
            'city',
            'country',
            'assignedTo',
            'publicUser.meters',
            'publicUser.ownedHousehold',
        ]);

        if (in_array('INSTITUTION_PAYMENT_INFO', $context['feature_codes'], true)) {
            $report->load('payments.pricingRule');
        }

        return view('institution.reports.show', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'reports',
            'report' => $report,
            'resolvedSignalPayload' => $report->resolvedSignalPayload(),
            'resolvedDamageAttachment' => $report->resolvedDamageAttachment(),
            'slaState' => $this->resolveSlaState($report),
        ]);
    }

    public function takeOver(Request $request, IncidentReport $report, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless($this->canManageReport($request, $report), 403);

        $report->update([
            'status' => IncidentReportStatus::InProgress->value,
            'assigned_to_user_id' => $request->user()->id,
            'taken_in_charge_at' => $report->taken_in_charge_at ?? now(),
        ]);

        $activityLogger->log(
            'institution.report.take_over',
            'Prise en charge d un signalement.',
            $report,
            [
                'report_reference' => $report->reference,
            ],
            $request,
            $request->user(),
            'institution',
        );

        return back()->with('success', 'Le signalement a ete pris en charge.');
    }

    public function resolve(Request $request, IncidentReport $report, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless($this->canManageReport($request, $report), 403);

        $attributes = $request->validate([
            'official_response' => ['required', 'string', 'max:2000'],
        ]);

        $report->update([
            'status' => IncidentReportStatus::Resolved->value,
            'assigned_to_user_id' => $request->user()->id,
            'taken_in_charge_at' => $report->taken_in_charge_at ?? now(),
            'resolved_at' => now(),
            'official_response' => $attributes['official_response'],
            'resolution_confirmation_status' => 'pending',
            'resolution_confirmed_at' => null,
        ]);

        $activityLogger->log(
            'institution.report.resolved',
            'Resolution d un signalement.',
            $report,
            [
                'report_reference' => $report->reference,
            ],
            $request,
            $request->user(),
            'institution',
        );

        return back()->with('success', 'Le signalement a ete marque comme resolu.');
    }

    public function reject(Request $request, IncidentReport $report, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless($this->canManageReport($request, $report), 403);

        $attributes = $request->validate([
            'official_response' => ['required', 'string', 'max:2000'],
        ]);

        $report->update([
            'status' => IncidentReportStatus::Rejected->value,
            'assigned_to_user_id' => $request->user()->id,
            'taken_in_charge_at' => $report->taken_in_charge_at ?? now(),
            'resolved_at' => now(),
            'official_response' => $attributes['official_response'],
            'resolution_confirmation_status' => null,
            'resolution_confirmed_at' => null,
        ]);

        $activityLogger->log(
            'institution.report.rejected',
            'Rejet d un signalement.',
            $report,
            [
                'report_reference' => $report->reference,
            ],
            $request,
            $request->user(),
            'institution',
        );

        return back()->with('success', 'Le signalement a ete rejete.');
    }

    public function updateDamageResolution(Request $request, IncidentReport $report, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless($this->canManageReport($request, $report), 403);
        abort_unless($report->damage_declared_at !== null, 422, 'Aucun dommage n a ete declare sur ce signalement.');

        $attributes = $request->validate([
            'damage_resolution_status' => ['required', 'in:submitted,in_progress,resolved,rejected'],
            'damage_resolution_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $report->update([
            'damage_resolution_status' => $attributes['damage_resolution_status'],
            'damage_resolution_notes' => $attributes['damage_resolution_notes'] ?? null,
            'damage_resolved_at' => in_array($attributes['damage_resolution_status'], ['resolved', 'rejected'], true) ? now() : null,
        ]);

        $activityLogger->log(
            'institution.damage_resolution.updated',
            'Mise a jour du statut de resolution d un dommage.',
            $report,
            [
                'report_reference' => $report->reference,
                'damage_resolution_status' => $report->damage_resolution_status,
            ],
            $request,
            $request->user(),
            'institution',
        );

        return back()->with('success', 'Le statut de resolution du dommage a ete mis a jour.');
    }

    private function canManageReport(Request $request, IncidentReport $report): bool
    {
        $organization = $request->user()?->organization;
        $applicationId = $organization?->application_id;
        $organizationId = $organization?->id;

        if ($organizationId !== null && (int) $report->organization_id !== (int) $organizationId) {
            return false;
        }

        if ($applicationId !== null && (int) $report->application_id !== (int) $applicationId) {
            return false;
        }

        return true;
    }

    private function resolveSlaState(IncidentReport $report): array
    {
        if (blank($report->target_sla_hours) || blank($report->created_at)) {
            return [
                'code' => 'unconfigured',
                'label' => 'Sans configuration TCM',
                'elapsed_hours' => null,
            ];
        }

        $endReference = $report->resolved_at ?? now();
        $elapsedHours = round($report->created_at->diffInMinutes($endReference) / 60, 1);
        $ratio = $report->target_sla_hours > 0 ? ($elapsedHours / $report->target_sla_hours) : 0;

        $label = match (true) {
            $ratio >= 1 => 'SLA depasse',
            $ratio >= 0.8 => 'SLA a risque',
            default => 'Dans le TCM',
        };

        $code = match (true) {
            $ratio >= 1 => 'breached',
            $ratio >= 0.8 => 'risk',
            default => 'within',
        };

        return [
            'code' => $code,
            'label' => $label,
            'elapsed_hours' => $elapsedHours,
        ];
    }
}