<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Organization;
use App\Models\RexFeedback;
use App\Models\RexSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RexFeedbackController extends Controller
{
    public function index(): View
    {
        $query = RexFeedback::query()
            ->with(['publicUser.publicUserType', 'incidentReport', 'application', 'organization']);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('comment', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('incidentReport', function ($reportQuery) use ($search): void {
                        $reportQuery->where('reference', 'like', '%'.$search.'%')
                            ->orWhere('signal_label', 'like', '%'.$search.'%')
                            ->orWhere('signal_code', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('context_type'))) {
            $query->where('context_type', request('context_type'));
        }

        if (filled(request('rating'))) {
            $query->where('rating', (int) request('rating'));
        }

        if (filled(request('application_id'))) {
            $query->where('application_id', (int) request('application_id'));
        }

        if (filled(request('organization_id'))) {
            $query->where('organization_id', (int) request('organization_id'));
        }

        return view('super-admin.rex-feedbacks.index', [
            'feedbacks' => $query->latest('submitted_at')->latest('id')->paginate(15)->withQueryString(),
            'setting' => RexSetting::current(),
            'applications' => Application::query()->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'available_days' => ['required', 'integer', 'min:1', 'max:365'],
            'editable_hours' => ['required', 'integer', 'min:0', 'max:720'],
        ]);

        RexSetting::current()->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'incident_report_enabled' => $request->boolean('incident_report_enabled'),
            'damage_enabled' => $request->boolean('damage_enabled'),
            'reparation_case_enabled' => $request->boolean('reparation_case_enabled'),
            'available_days' => (int) $attributes['available_days'],
            'editable_hours' => (int) $attributes['editable_hours'],
        ]);

        return back()->with('success', 'Le parametrage REX a ete mis a jour.');
    }
}
