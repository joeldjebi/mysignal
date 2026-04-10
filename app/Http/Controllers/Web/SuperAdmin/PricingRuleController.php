<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PricingRuleController extends Controller
{
    public function edit(): View
    {
        return view('super-admin.pricing.edit', [
            'pricingRules' => PricingRule::query()->orderBy('label')->get(),
            'pricingRule' => filled(request('pricing_rule'))
                ? PricingRule::query()->find(request('pricing_rule'))
                : null,
        ]);
    }

    public function update(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'pricing_rule_id' => ['nullable', 'exists:pricing_rules,id'],
            'code' => ['required', 'string', 'max:60', Rule::unique('pricing_rules', 'code')->ignore($request->integer('pricing_rule_id'))],
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $existingRule = filled($attributes['pricing_rule_id'] ?? null)
            ? PricingRule::query()->find($attributes['pricing_rule_id'])
            : null;

        $pricingRule = PricingRule::query()->updateOrCreate(
            ['id' => $attributes['pricing_rule_id'] ?? null],
            [
                'code' => strtolower(trim((string) $attributes['code'])),
                'label' => $attributes['label'],
                'amount' => $attributes['amount'],
                'currency' => strtoupper($attributes['currency']),
                'starts_at' => $attributes['starts_at'] ?? null,
                'ends_at' => $attributes['ends_at'] ?? null,
                'status' => $existingRule?->status ?? 'active',
            ],
        );

        $activityLogger->log(
            $existingRule ? 'pricing_rule.updated' : 'pricing_rule.created',
            $existingRule ? 'Mise a jour d une tarification.' : 'Creation d une tarification.',
            $pricingRule,
            [
                'code' => $pricingRule->code,
                'label' => $pricingRule->label,
                'amount' => $pricingRule->amount,
                'currency' => $pricingRule->currency,
                'status' => $pricingRule->status,
            ],
            $request
        );

        return redirect()->route('super-admin.pricing.edit')
            ->with('success', 'La tarification a ete enregistree.');
    }

    public function destroy(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $pricingRuleId = request()->integer('pricing_rule_id');

        if ($pricingRuleId > 0) {
            $pricingRule = PricingRule::query()->find($pricingRuleId);

            if ($pricingRule !== null) {
                $snapshot = $pricingRule->only(['id', 'code', 'label', 'amount', 'currency', 'status']);
                $pricingRule->delete();

                $activityLogger->log(
                    'pricing_rule.deleted',
                    'Suppression d une tarification.',
                    PricingRule::class,
                    $snapshot,
                    $request
                );
            }
        }

        return redirect()->route('super-admin.pricing.edit')
            ->with('success', 'La tarification a ete supprimee.');
    }

    public function toggleStatus(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $pricingRule = PricingRule::query()->find(request()->integer('pricing_rule_id'));

        if ($pricingRule !== null) {
            $pricingRule->update([
                'status' => $pricingRule->status === 'active' ? 'inactive' : 'active',
            ]);

            $activityLogger->log(
                'pricing_rule.status_toggled',
                'Changement de statut d une tarification.',
                $pricingRule,
                [
                    'status' => $pricingRule->status,
                ],
                $request
            );
        }

        return back()->with('success', 'Le statut de la tarification a ete mis a jour.');
    }
}
