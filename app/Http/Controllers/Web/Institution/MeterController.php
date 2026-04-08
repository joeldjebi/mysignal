<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\Meter;
use Illuminate\Support\Facades\Abort;
use Illuminate\View\View;

class MeterController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();

        $query = Meter::query();

        if ($context['network_type'] !== null) {
            $query->where('network_type', $context['network_type']);
        }

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

        if ($context['network_type'] !== null && $meter->network_type !== $context['network_type']) {
            abort(403);
        }

        $meter->load([
            'publicUsers',
            'incidentReports' => fn ($query) => $query->with('commune')->latest()->limit(10),
        ]);

        return view('institution.meters.show', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'meters',
            'meter' => $meter,
        ]);
    }
}
