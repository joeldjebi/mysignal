<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Contracts\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $query = Payment::query()
            ->with([
                'publicUser.publicUserType',
                'incidentReport.application',
                'incidentReport.organization',
                'pricingRule',
            ]);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));

            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('provider_reference', 'like', '%'.$search.'%')
                    ->orWhere('provider', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('incidentReport', function ($reportQuery) use ($search): void {
                        $reportQuery->where('reference', 'like', '%'.$search.'%')
                            ->orWhere('signal_label', 'like', '%'.$search.'%')
                            ->orWhere('signal_code', 'like', '%'.$search.'%')
                            ->orWhere('incident_type', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('provider'))) {
            $query->where('provider', request('provider'));
        }

        if (filled(request('application_id'))) {
            $applicationId = (int) request('application_id');
            $query->whereHas('incidentReport', fn ($reportQuery) => $reportQuery->where('application_id', $applicationId));
        }

        if (filled(request('organization_id'))) {
            $organizationId = (int) request('organization_id');
            $query->whereHas('incidentReport', fn ($reportQuery) => $reportQuery->where('organization_id', $organizationId));
        }

        return view('super-admin.payments.index', [
            'payments' => $query->latest('initiated_at')->latest('id')->paginate(15)->withQueryString(),
            'applications' => \App\Models\Application::query()->orderBy('name')->get(['id', 'name']),
            'organizations' => \App\Models\Organization::query()->orderBy('name')->get(['id', 'name']),
            'providers' => Payment::query()
                ->select('provider')
                ->whereNotNull('provider')
                ->where('provider', '!=', '')
                ->distinct()
                ->orderBy('provider')
                ->pluck('provider'),
        ]);
    }
}
