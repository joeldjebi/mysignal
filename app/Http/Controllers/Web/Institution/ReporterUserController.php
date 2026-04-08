<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\Meter;
use App\Models\PublicUser;
use Illuminate\View\View;

class ReporterUserController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $context['feature_codes'], true);
        $withCount = [
            'incidentReports as reports_count' => function ($builder) use ($context): void {
                if ($context['application_id'] !== null) {
                    $builder->where('application_id', $context['application_id']);
                }

                if ($context['organization_id'] !== null) {
                    $builder->where('organization_id', $context['organization_id']);
                }

                if ($context['network_type'] !== null) {
                    $builder->where('network_type', $context['network_type']);
                }
            },
        ];

        if ($canViewPaymentInfo) {
            $withCount['incidentReports as paid_reports_count'] = function ($builder) use ($context): void {
                if ($context['application_id'] !== null) {
                    $builder->where('application_id', $context['application_id']);
                }

                if ($context['organization_id'] !== null) {
                    $builder->where('organization_id', $context['organization_id']);
                }

                if ($context['network_type'] !== null) {
                    $builder->where('network_type', $context['network_type']);
                }

                $builder->where('payment_status', 'paid');
            };
        }

        $query = PublicUser::query()->withCount($withCount);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('commune'))) {
            $query->where('commune', request('commune'));
        }

        if (filled(request('has_reports'))) {
            if (request('has_reports') === 'yes') {
                $query->having('reports_count', '>', 0);
            }

            if (request('has_reports') === 'no') {
                $query->having('reports_count', '=', 0);
            }
        }

        return view('institution.report-users.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'report-users',
            'users' => $query->latest()->paginate(15)->withQueryString(),
            'communes' => PublicUser::query()
                ->whereHas('incidentReports', function ($builder) use ($context): void {
                    if ($context['application_id'] !== null) {
                        $builder->where('application_id', $context['application_id']);
                    }

                    if ($context['organization_id'] !== null) {
                        $builder->where('organization_id', $context['organization_id']);
                    }

                    if ($context['network_type'] !== null) {
                        $builder->where('network_type', $context['network_type']);
                    }
                })
                ->whereNotNull('commune')
                ->distinct()
                ->orderBy('commune')
                ->pluck('commune'),
        ]);
    }

    public function show(PublicUser $reportUser): View
    {
        $context = $this->institutionContext();
        $networkType = $context['network_type'];

        $reportUser->load([
            'meters' => function ($query) use ($networkType, $context): void {
                if ($context['organization_id'] !== null) {
                    $query->where('organization_id', $context['organization_id']);
                }

                if ($networkType !== null) {
                    $query->where('network_type', $networkType);
                }
            },
            'incidentReports' => function ($query) use ($networkType, $context): void {
                if ($context['application_id'] !== null) {
                    $query->where('application_id', $context['application_id']);
                }

                if ($context['organization_id'] !== null) {
                    $query->where('organization_id', $context['organization_id']);
                }

                if ($networkType !== null) {
                    $query->where('network_type', $networkType);
                }

                $query->with(['meter', 'commune', 'assignedTo'])->latest();
            },
        ]);

        if ($reportUser->incidentReports->isEmpty() && $reportUser->meters->isEmpty()) {
            abort(404);
        }

        $reportsByMeter = $reportUser->incidentReports
            ->groupBy(fn ($report) => $report->meter?->id ?? 'without-meter')
            ->map(function ($reports, $meterId) use ($reportUser) {
                $meter = $meterId === 'without-meter'
                    ? null
                    : $reportUser->meters->firstWhere('id', (int) $meterId);

                return [
                    'meter' => $meter,
                    'reports' => $reports,
                ];
            })
            ->values();

        return view('institution.report-users.show', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'report-users',
            'reportUser' => $reportUser,
            'reportsByMeter' => $reportsByMeter,
        ]);
    }
}
