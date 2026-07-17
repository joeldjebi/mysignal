<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PrivilegeCardPaymentSession;
use App\Models\PrivilegeCardType;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PrivilegeCardTypeController extends Controller
{
    public function index(): View
    {
        $query = PrivilegeCardType::query()->withCount(['cards', 'paymentSessions']);
        $purchaseQuery = PrivilegeCardPaymentSession::query()
            ->with(['publicUser', 'type', 'card']);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('purchase_search'))) {
            $search = trim((string) request('purchase_search'));
            $purchaseQuery->where(function ($builder) use ($search): void {
                $builder->where('sync_ref', 'like', '%'.$search.'%')
                    ->orWhere('provider_reference', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', function ($userQuery) use ($search): void {
                        $userQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('card', function ($cardQuery) use ($search): void {
                        $cardQuery->where('card_number', 'like', '%'.$search.'%')
                            ->orWhere('card_uuid', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('purchase_status'))) {
            $purchaseQuery->where('status', request('purchase_status'));
        }

        if (filled(request('purchase_type_id'))) {
            $purchaseQuery->where('privilege_card_type_id', request('purchase_type_id'));
        }

        $cardTypes = PrivilegeCardType::query()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get(['id', 'name', 'code']);

        return view('super-admin.privilege-card-types.index', [
            'types' => $query->orderBy('sort_order')->orderBy('price')->paginate(12, ['*'], 'types_page')->withQueryString(),
            'purchases' => $purchaseQuery->latest('id')->paginate(12, ['*'], 'purchases_page')->withQueryString(),
            'cardTypes' => $cardTypes,
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);
        $type = PrivilegeCardType::query()->create($attributes);

        $activityLogger->log('privilege_card_type.created', 'Creation d une carte privilege.', $type, $type->toArray(), $request);

        return redirect()->route('super-admin.privilege-card-types.index')
            ->with('success', 'La carte privilege a ete creee.');
    }

    public function update(Request $request, PrivilegeCardType $privilegeCardType, ActivityLogger $activityLogger): RedirectResponse
    {
        $before = $privilegeCardType->toArray();
        $privilegeCardType->update($this->validatedAttributes($request, $privilegeCardType));

        $activityLogger->log('privilege_card_type.updated', 'Mise a jour d une carte privilege.', $privilegeCardType, [
            'before' => $before,
            'after' => $privilegeCardType->fresh()?->toArray(),
        ], $request);

        return redirect()->route('super-admin.privilege-card-types.index')
            ->with('success', 'La carte privilege a ete mise a jour.');
    }

    public function destroy(Request $request, PrivilegeCardType $privilegeCardType, ActivityLogger $activityLogger): RedirectResponse
    {
        if ($privilegeCardType->cards()->exists() || $privilegeCardType->paymentSessions()->exists()) {
            return back()->with('error', 'Impossible de supprimer une carte deja achetee ou liee a un paiement.');
        }

        $snapshot = $privilegeCardType->toArray();
        $privilegeCardType->delete();

        $activityLogger->log('privilege_card_type.deleted', 'Suppression d une carte privilege.', PrivilegeCardType::class, $snapshot, $request);

        return redirect()->route('super-admin.privilege-card-types.index')
            ->with('success', 'La carte privilege a ete supprimee.');
    }

    public function toggleStatus(Request $request, PrivilegeCardType $privilegeCardType, ActivityLogger $activityLogger): RedirectResponse
    {
        $privilegeCardType->update([
            'status' => $privilegeCardType->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log('privilege_card_type.status_toggled', 'Changement de statut d une carte privilege.', $privilegeCardType, [
            'status' => $privilegeCardType->status,
        ], $request);

        return back()->with('success', 'Le statut de la carte privilege a ete mis a jour.');
    }

    private function validatedAttributes(Request $request, ?PrivilegeCardType $type = null): array
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('privilege_card_types', 'code')->ignore($type?->id)],
            'price' => ['required', 'integer', 'min:1', 'max:999999999'],
            'currency' => ['required', 'string', 'max:10'],
            'benefits_text' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed_amount'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        if ($attributes['discount_type'] === 'percentage' && (float) $attributes['discount_value'] > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'discount_value' => ['La reduction en pourcentage ne peut pas depasser 100%.'],
            ]);
        }

        return [
            'code' => filled($attributes['code'] ?? null) ? Str::upper(Str::slug($attributes['code'], '_')) : $this->codeFromName($attributes['name'], $type),
            'name' => $attributes['name'],
            'price' => $attributes['price'],
            'currency' => Str::upper($attributes['currency']),
            'benefits' => collect(preg_split('/\r\n|\r|\n/', (string) ($attributes['benefits_text'] ?? '')))
                ->map(fn ($benefit) => trim((string) $benefit))
                ->filter()
                ->values()
                ->all(),
            'discount_type' => $attributes['discount_type'],
            'discount_value' => $attributes['discount_value'],
            'duration_months' => $attributes['duration_months'],
            'sort_order' => $attributes['sort_order'] ?? ($type?->sort_order ?? $this->nextSortOrder()),
            'status' => $type?->status ?? 'active',
        ];
    }

    private function codeFromName(string $name, ?PrivilegeCardType $type = null): string
    {
        $baseCode = Str::upper(Str::slug($name, '_')) ?: 'CARTE_PRIVILEGE';
        $code = Str::limit($baseCode, 40, '');
        $suffix = 2;

        while (PrivilegeCardType::query()->where('code', $code)->when($type, fn ($query) => $query->whereKeyNot($type->id))->exists()) {
            $suffixText = '_'.$suffix++;
            $code = Str::limit($baseCode, 40 - strlen($suffixText), '').$suffixText;
        }

        return $code;
    }

    private function nextSortOrder(): int
    {
        return ((int) PrivilegeCardType::query()->max('sort_order')) + 1;
    }
}
