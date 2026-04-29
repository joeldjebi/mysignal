<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\ReparationCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReparationCaseController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $query = $this->institutionReparationCasesQuery($context['organization_id']);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhereHas('incidentReport', fn ($reportQuery) => $reportQuery
                        ->where('reference', 'like', '%'.$search.'%')
                        ->orWhere('signal_label', 'like', '%'.$search.'%')
                        ->orWhere('signal_code', 'like', '%'.$search.'%'))
                    ->orWhereHas('publicUser', fn ($userQuery) => $userQuery
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%'));
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('case_type'))) {
            $query->where('case_type', request('case_type'));
        }

        return view('institution.reparation-cases.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'reparation-cases',
            'reparationCases' => $query->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Request $request, ReparationCase $reparationCase): View
    {
        $context = $this->institutionContext();

        abort_unless((int) $reparationCase->organization_id === (int) $context['organization_id'], 403);

        return view('institution.reparation-cases.show', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'reparation-cases',
            'reparationCase' => $reparationCase->load([
                'incidentReport.commune',
                'incidentReport.city',
                'incidentReport.country',
                'incidentReport.meter',
                'publicUser',
                'organization',
                'application',
                'openedBy',
                'assignedTo',
                'bailiff',
                'lawyer',
                'histories.createdBy',
                'steps.assignedTo',
                'steps.createdBy',
            ]),
        ]);
    }

    private function institutionReparationCasesQuery(?int $organizationId)
    {
        return ReparationCase::query()
            ->with(['incidentReport', 'publicUser', 'assignedTo', 'bailiff', 'lawyer'])
            ->when($organizationId !== null, fn ($query) => $query->where('organization_id', $organizationId));
    }
}
