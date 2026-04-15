<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        $query = SubscriptionPlan::query();

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('is_active', request('status') === 'active');
        }

        return view('super-admin.subscription-plans.index', [
            'subscriptionPlans' => $query->orderByDesc('is_active')->orderBy('name')->paginate(12)->withQueryString(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validatedPayload($request);
        $this->ensureSingleActivePlan((bool) $attributes['is_active']);

        $subscriptionPlan = SubscriptionPlan::query()->create([
            ...$attributes,
            'created_by' => $request->user()?->id,
        ]);

        $activityLogger->log(
            'subscription_plan.created',
            'Creation d un plan d abonnement UP.',
            $subscriptionPlan,
            $this->activityProperties($subscriptionPlan),
            $request
        );

        return redirect()->route('super-admin.subscription-plans.index')
            ->with('success', 'Le plan d abonnement a ete cree.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        return view('super-admin.subscription-plans.edit', [
            'subscriptionPlan' => $subscriptionPlan,
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validatedPayload($request, $subscriptionPlan);
        $this->ensureSingleActivePlan((bool) $attributes['is_active'], $subscriptionPlan);

        $subscriptionPlan->update($attributes);

        $activityLogger->log(
            'subscription_plan.updated',
            'Mise a jour d un plan d abonnement UP.',
            $subscriptionPlan,
            $this->activityProperties($subscriptionPlan),
            $request
        );

        return redirect()->route('super-admin.subscription-plans.index')
            ->with('success', 'Le plan d abonnement a ete mis a jour.');
    }

    public function toggleStatus(Request $request, SubscriptionPlan $subscriptionPlan, ActivityLogger $activityLogger): RedirectResponse
    {
        $targetStatus = ! $subscriptionPlan->is_active;
        $this->ensureSingleActivePlan($targetStatus, $subscriptionPlan);

        $subscriptionPlan->update([
            'is_active' => $targetStatus,
        ]);

        $activityLogger->log(
            'subscription_plan.status_toggled',
            'Changement de statut d un plan d abonnement UP.',
            $subscriptionPlan,
            $this->activityProperties($subscriptionPlan),
            $request
        );

        return back()->with('success', 'Le statut du plan d abonnement a ete mis a jour.');
    }

    private function validatedPayload(Request $request, ?SubscriptionPlan $subscriptionPlan = null): array
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('subscription_plans', 'code')->ignore($subscriptionPlan?->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'price' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'code' => strtoupper(trim((string) $attributes['code'])),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'duration_months' => (int) $attributes['duration_months'],
            'price' => (int) $attributes['price'],
            'currency' => strtoupper(trim((string) $attributes['currency'])),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function ensureSingleActivePlan(bool $willBeActive, ?SubscriptionPlan $currentPlan = null): void
    {
        if (! $willBeActive) {
            return;
        }

        $activePlanExists = SubscriptionPlan::query()
            ->where('is_active', true)
            ->when($currentPlan, fn ($query) => $query->whereKeyNot($currentPlan->id))
            ->exists();

        if ($activePlanExists) {
            throw ValidationException::withMessages([
                'is_active' => 'Un seul plan d abonnement actif est autorise pour le moment.',
            ]);
        }
    }

    private function activityProperties(SubscriptionPlan $subscriptionPlan): array
    {
        return [
            'code' => $subscriptionPlan->code,
            'name' => $subscriptionPlan->name,
            'duration_months' => $subscriptionPlan->duration_months,
            'price' => $subscriptionPlan->price,
            'currency' => $subscriptionPlan->currency,
            'is_active' => $subscriptionPlan->is_active,
        ];
    }
}
