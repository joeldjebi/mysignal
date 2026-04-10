<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\IncidentReport;
use App\Models\Organization;
use Illuminate\View\View;

class PublicIncidentReportController extends Controller
{
    public function index(): View
    {
        $query = IncidentReport::query()
            ->with(['publicUser.publicUserType', 'application', 'organization', 'commune', 'reparationCase'])
            ->whereNotNull('public_user_id');

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('signal_label', 'like', '%'.$search.'%')
                    ->orWhere('signal_code', 'like', '%'.$search.'%')
                    ->orWhere('incident_type', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', fn ($userQuery) => $userQuery
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%'))
                    ->orWhereHas('organization', fn ($organizationQuery) => $organizationQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('application', fn ($applicationQuery) => $applicationQuery->where('name', 'like', '%'.$search.'%'));
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('application_id'))) {
            $query->where('application_id', request('application_id'));
        }

        if (filled(request('organization_id'))) {
            $query->where('organization_id', request('organization_id'));
        }

        if (filled(request('damage'))) {
            if (request('damage') === 'with_damage') {
                $query->whereNotNull('damage_declared_at');
            }

            if (request('damage') === 'without_damage') {
                $query->whereNull('damage_declared_at');
            }
        }

        if (filled(request('reparation_case'))) {
            if (request('reparation_case') === 'opened') {
                $query->whereHas('reparationCase');
            }

            if (request('reparation_case') === 'missing') {
                $query->whereDoesntHave('reparationCase');
            }
        }

        return view('super-admin.public-reports.index', [
            'reports' => $query->latest()->paginate(15)->withQueryString(),
            'applications' => Application::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
