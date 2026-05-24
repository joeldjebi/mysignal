<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\Meter;
use Illuminate\View\View;

class MeterController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();

        $query = $this->institutionMetersQuery($context['application_id'], $context['organization_id'], $context['network_type']);

        $query->with(['application', 'organization'])
            ->withCount([
                'assignments as gbonhi_assignments_count' => fn ($builder) => $builder->where('assignment_source', 'gbonhi'),
            ]);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('meter_number', 'like', '%'.$search.'%')
                    ->orWhere('label', 'like', '%'.$search.'%')
                    ->orWhere('commune', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('institution.meters.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'meters',
            'meters' => $query->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Meter $meter): View
    {
        $context = $this->institutionContext();

        abort_unless($this->canAccessMeter($meter, $context['application_id'], $context['organization_id'], $context['network_type']), 403);

        $meter->load([
            'application',
            'organization',
            'publicUsers',
            'assignments',
            'incidentReports' => fn ($query) => $query
                ->with('commune')
                ->when($context['application_id'] !== null, fn ($builder) => $builder->where('application_id', $context['application_id']))
                ->when($context['organization_id'] !== null, fn ($builder) => $builder->where('organization_id', $context['organization_id']))
                ->latest()
                ->limit(10),
        ]);

        return view('institution.meters.show', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'meters',
            'meter' => $meter,
        ]);
    }

    private function institutionMetersQuery(?int $applicationId, ?int $organizationId, ?string $networkType)
    {
        return Meter::query()
            ->where('application_id', $applicationId)
            ->where('organization_id', $organizationId)
            ->when($networkType !== null, fn ($query) => $query->where('network_type', $networkType));
    }

    private function canAccessMeter(Meter $meter, ?int $applicationId, ?int $organizationId, ?string $networkType): bool
    {
        if ($applicationId === null || (int) $meter->application_id !== (int) $applicationId) {
            return false;
        }

        if ($organizationId === null || (int) $meter->organization_id !== (int) $organizationId) {
            return false;
        }

        if ($networkType !== null && $meter->network_type !== $networkType) {
            return false;
        }

        return true;
    }
}
