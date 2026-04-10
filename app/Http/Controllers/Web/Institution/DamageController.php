<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\Commune;
use App\Models\IncidentReport;
use Illuminate\View\View;

class DamageController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $query = $this->institutionReportsQuery($context['network_type'], $context['application_id'], $context['organization_id'])
            ->whereNotNull('damage_declared_at');

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('signal_label', 'like', '%'.$search.'%')
                    ->orWhere('signal_code', 'like', '%'.$search.'%')
                    ->orWhere('damage_summary', 'like', '%'.$search.'%')
                    ->orWhere('damage_notes', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('commune_id'))) {
            $query->where('commune_id', request('commune_id'));
        }

        if (filled(request('damage_resolution_status'))) {
            $damageResolutionStatus = (string) request('damage_resolution_status');

            $query->where(function ($builder) use ($damageResolutionStatus): void {
                if ($damageResolutionStatus === 'submitted') {
                    $builder->whereNull('damage_resolution_status')
                        ->orWhere('damage_resolution_status', 'submitted');

                    return;
                }

                $builder->where('damage_resolution_status', $damageResolutionStatus);
            });
        }

        if (filled(request('attachment'))) {
            if (request('attachment') === 'with') {
                $query->whereNotNull('damage_attachment');
            }

            if (request('attachment') === 'without') {
                $query->whereNull('damage_attachment');
            }
        }

        return view('institution.damages.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'damages',
            'damages' => $query->latest('damage_declared_at')->paginate(15)->withQueryString(),
            'communes' => Commune::query()
                ->whereIn(
                    'id',
                    IncidentReport::query()
                        ->when($context['organization_id'] !== null, fn ($builder) => $builder->where('organization_id', $context['organization_id']))
                        ->when($context['application_id'] !== null, fn ($builder) => $builder->where('application_id', $context['application_id']))
                        ->when($context['network_type'] !== null, fn ($builder) => $builder->where('network_type', $context['network_type']))
                        ->whereNotNull('damage_declared_at')
                        ->whereNotNull('commune_id')
                        ->distinct()
                        ->select('commune_id')
                )
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
