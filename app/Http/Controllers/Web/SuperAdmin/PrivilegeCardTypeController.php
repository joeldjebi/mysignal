<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PartnerDiscountTransaction;
use App\Models\PrivilegeCard;
use App\Models\PrivilegeCardPaymentSession;
use App\Models\PrivilegeCardType;
use App\Models\PublicUser;
use App\Services\Wallet\GoogleWalletService;
use App\Services\Wallet\WalletConfigurationException;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PrivilegeCardTypeController extends Controller
{
    public function index(): View
    {
        $query = PrivilegeCardType::query()->withCount(['cards', 'paymentSessions']);

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

        return view('super-admin.privilege-card-types.index', [
            'types' => $query->orderBy('sort_order')->orderBy('price')->paginate(12)->withQueryString(),
            ...$this->commonViewData(),
            ...$this->issueFormViewData(),
        ]);
    }

    public function issuedCards(GoogleWalletService $googleWallet): View
    {
        $issuedCardQuery = PrivilegeCard::query()
            ->with(['publicUser', 'type'])
            ->withExists([
                'paymentSessions as has_paid_session' => fn ($query) => $query->where('status', PaymentStatus::Paid->value),
            ]);

        if (filled(request('issued_search'))) {
            $search = trim((string) request('issued_search'));
            $issuedCardQuery->where(function ($builder) use ($search): void {
                $builder->where('card_number', 'like', '%'.$search.'%')
                    ->orWhere('card_uuid', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', function ($userQuery) use ($search): void {
                        $userQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('issued_type_id'))) {
            $issuedCardQuery->where('privilege_card_type_id', request('issued_type_id'));
        }

        if (filled(request('issued_status'))) {
            $issuedCardQuery->where('status', request('issued_status'));
        }

        $issuedCards = $issuedCardQuery->latest('id')->paginate(12)->withQueryString();

        return view('super-admin.privilege-card-types.issued-cards', [
            'issuedCards' => $issuedCards,
            'walletLinks' => $this->walletLinksForCards($issuedCards->getCollection(), $googleWallet),
            ...$this->commonViewData(),
            ...$this->issueFormViewData(),
        ]);
    }

    public function purchases(): View
    {
        $request = request();
        $perPage = min(max((int) $request->integer('per_page', 12), 1), 100);
        $purchaseQuery = PrivilegeCardPaymentSession::query()
            ->with(['publicUser', 'type', 'card']);

        if (filled($request->input('purchase_search'))) {
            $search = trim((string) $request->input('purchase_search'));
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

        if (filled($request->input('purchase_status'))) {
            $purchaseQuery->where('status', $request->input('purchase_status'));
        }

        if (filled($request->input('purchase_type_id'))) {
            $purchaseQuery->where('privilege_card_type_id', $request->input('purchase_type_id'));
        }

        $this->applyPurchasePeriodFilter($purchaseQuery, $request, 'privilege_card_payment_sessions.initiated_at');
        $statsQuery = clone $purchaseQuery;

        return view('super-admin.privilege-card-types.purchases', [
            'purchases' => (clone $purchaseQuery)->latest('id')->paginate($perPage)->withQueryString(),
            'purchaseStats' => $this->purchaseStats(clone $statsQuery),
            'purchaseStatusBreakdown' => $this->purchaseStatusBreakdown(clone $statsQuery),
            'purchaseTypeBreakdown' => $this->purchaseTypeBreakdown(clone $statsQuery),
            'purchaseTrend' => $this->purchaseTrend(clone $statsQuery),
            'perPage' => $perPage,
            ...$this->commonViewData(),
        ]);
    }

    public function scans(): View
    {
        $scanQuery = PartnerDiscountTransaction::query()
            ->with(['privilegeCard.type', 'partnerUser', 'organization', 'publicUser'])
            ->where('card_source', 'privilege_card');

        if (filled(request('scan_search'))) {
            $search = trim((string) request('scan_search'));
            $scanQuery->where(function ($builder) use ($search): void {
                $builder->where('scan_reference', 'like', '%'.$search.'%')
                    ->orWhereHas('privilegeCard', function ($cardQuery) use ($search): void {
                        $cardQuery->where('card_number', 'like', '%'.$search.'%')
                            ->orWhere('card_uuid', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('partnerUser', function ($userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('organization', function ($organizationQuery) use ($search): void {
                        $organizationQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('code', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('scan_type_id'))) {
            $scanQuery->whereHas('privilegeCard', function ($cardQuery): void {
                $cardQuery->where('privilege_card_type_id', request('scan_type_id'));
            });
        }

        if (filled(request('scan_status'))) {
            $scanQuery->where('status', request('scan_status'));
        }

        return view('super-admin.privilege-card-types.scans', [
            'scans' => $scanQuery->latest('applied_at')->latest('id')->paginate(12)->withQueryString(),
            ...$this->commonViewData(),
        ]);
    }

    private function commonViewData(): array
    {
        $cardTypes = PrivilegeCardType::query()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get(['id', 'name', 'code']);
        return [
            'cardTypes' => $cardTypes,
            'cardStatusLabels' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'expired' => 'Expirée',
                'suspended' => 'Suspendue',
                'revoked' => 'Révoquée',
            ],
            'paymentStatusLabels' => [
                'pending' => 'En attente',
                'paid' => 'Payé',
                'failed' => 'Échoué',
                'cancelled' => 'Annulé',
                'expired' => 'Expiré',
            ],
            'scanStatusLabels' => [
                'validated' => 'Validé',
                'cancelled' => 'Annulé',
                'reversed' => 'Annulé après contrôle',
                'rejected' => 'Rejeté',
            ],
            'verificationStatusLabels' => [
                'verified' => 'Vérifié',
                'valid' => 'Valide',
                'pending' => 'En attente',
                'failed' => 'Échoué',
                'rejected' => 'Rejeté',
            ],
        ];
    }

    private function applyPurchasePeriodFilter($query, Request $request, string $column): void
    {
        [$startDate, $endDate] = $this->purchasePeriodBounds($request);

        if ($startDate !== null) {
            $query->where($column, '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where($column, '<=', $endDate);
        }
    }

    private function purchasePeriodBounds(Request $request): array
    {
        $period = (string) $request->input('period', '30d');

        if ($period === 'today') {
            return [now()->startOfDay(), now()->endOfDay()];
        }

        if ($period === '7d') {
            return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
        }

        if ($period === 'month') {
            return [now()->startOfMonth(), now()->endOfMonth()];
        }

        if ($period === 'year') {
            return [now()->startOfYear(), now()->endOfYear()];
        }

        if ($period === 'custom') {
            $start = filled($request->input('date_from')) ? Carbon::parse($request->input('date_from'))->startOfDay() : null;
            $end = filled($request->input('date_to')) ? Carbon::parse($request->input('date_to'))->endOfDay() : null;

            return [$start, $end];
        }

        return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
    }

    private function purchaseStats($purchaseQuery): array
    {
        $paidPurchases = (clone $purchaseQuery)->where('status', PaymentStatus::Paid->value);

        return [
            'total' => (clone $purchaseQuery)->count(),
            'paid' => (clone $paidPurchases)->count(),
            'pending' => (clone $purchaseQuery)->where('status', PaymentStatus::Pending->value)->count(),
            'failed' => (clone $purchaseQuery)->where('status', PaymentStatus::Failed->value)->count(),
            'paid_amount' => (float) (clone $paidPurchases)->sum('amount'),
        ];
    }

    private function purchaseStatusBreakdown($purchaseQuery): array
    {
        $statuses = [
            PaymentStatus::Pending->value => 'En attente',
            PaymentStatus::Paid->value => 'Payés',
            PaymentStatus::Failed->value => 'Échoués',
        ];

        return collect($statuses)
            ->map(fn (string $label, string $status): array => [
                'label' => $label,
                'value' => (clone $purchaseQuery)->where('status', $status)->count(),
            ])
            ->values()
            ->all();
    }

    private function purchaseTypeBreakdown($purchaseQuery): array
    {
        return (clone $purchaseQuery)
            ->reorder()
            ->selectRaw('privilege_card_type_id, COUNT(*) as total')
            ->with('type:id,name')
            ->groupBy('privilege_card_type_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->type?->name ?: 'Carte non renseignée',
                'value' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function purchaseTrend($purchaseQuery): array
    {
        return (clone $purchaseQuery)
            ->reorder()
            ->where('status', PaymentStatus::Paid->value)
            ->selectRaw('DATE(privilege_card_payment_sessions.initiated_at) as period_label')
            ->selectRaw('SUM(amount) as paid_amount')
            ->groupByRaw('DATE(privilege_card_payment_sessions.initiated_at)')
            ->orderByRaw('DATE(privilege_card_payment_sessions.initiated_at)')
            ->get()
            ->map(fn ($row): array => [
                'label' => Carbon::parse($row->period_label)->format('d/m'),
                'amount' => (float) $row->paid_amount,
            ])
            ->values()
            ->all();
    }

    private function issueFormViewData(): array
    {
        return [
            'activeCardTypes' => PrivilegeCardType::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get(['id', 'name', 'code', 'duration_months']),
            'publicUsers' => PublicUser::query()
                ->where('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->orderBy('phone')
                ->get(['id', 'first_name', 'last_name', 'phone', 'email']),
        ];
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);
        $type = PrivilegeCardType::query()->create($attributes);

        $activityLogger->log('privilege_card_type.created', 'Création d’une carte privilège.', $type, $type->toArray(), $request);

        return redirect()->route('super-admin.privilege-card-types.index')
            ->with('success', 'La carte privilège a été créée.');
    }

    public function issueCard(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'public_user_id' => ['required', 'integer', Rule::exists('public_users', 'id')],
            'privilege_card_type_id' => ['required', 'integer', Rule::exists('privilege_card_types', 'id')],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $publicUser = PublicUser::query()
            ->whereKey($attributes['public_user_id'])
            ->where('status', 'active')
            ->firstOrFail();
        $type = PrivilegeCardType::query()
            ->whereKey($attributes['privilege_card_type_id'])
            ->where('status', 'active')
            ->firstOrFail();

        $card = DB::transaction(function () use ($attributes, $publicUser, $type): PrivilegeCard {
            return PrivilegeCard::query()->create([
                'public_user_id' => $publicUser->id,
                'privilege_card_type_id' => $type->id,
                'card_uuid' => (string) Str::uuid(),
                'card_number' => $this->generateCardNumber($type->code),
                'status' => 'active',
                'issued_at' => now(),
                'activated_at' => now(),
                'expires_at' => filled($attributes['expires_at'] ?? null)
                    ? \Illuminate\Support\Carbon::parse($attributes['expires_at'])->endOfDay()
                    : now()->addMonthsNoOverflow((int) $type->duration_months),
                'metadata' => [
                    'issued_by' => 'super_admin',
                    'issued_from' => 'web',
                ],
            ]);
        });

        $activityLogger->log('privilege_card.issued_by_super_admin', 'Émission manuelle d’une carte privilège.', $card, [
            'public_user_id' => $publicUser->id,
            'privilege_card_type_id' => $type->id,
            'card_number' => $card->card_number,
            'card_uuid' => $card->card_uuid,
        ], $request);

        return redirect()->route('super-admin.privilege-card-types.issued-cards')
            ->with('success', 'La carte privilège '.$card->card_number.' a été émise. Code à scanner: '.$card->card_uuid);
    }

    public function update(Request $request, PrivilegeCardType $privilegeCardType, ActivityLogger $activityLogger): RedirectResponse
    {
        $before = $privilegeCardType->toArray();
        $privilegeCardType->update($this->validatedAttributes($request, $privilegeCardType));

        $activityLogger->log('privilege_card_type.updated', 'Mise à jour d’une carte privilège.', $privilegeCardType, [
            'before' => $before,
            'after' => $privilegeCardType->fresh()?->toArray(),
        ], $request);

        return redirect()->route('super-admin.privilege-card-types.index')
            ->with('success', 'La carte privilège a été mise à jour.');
    }

    public function destroy(Request $request, PrivilegeCardType $privilegeCardType, ActivityLogger $activityLogger): RedirectResponse
    {
        if ($privilegeCardType->cards()->exists() || $privilegeCardType->paymentSessions()->exists()) {
            return back()->with('error', 'Impossible de supprimer une carte déjà achetée ou liée à un paiement.');
        }

        $snapshot = $privilegeCardType->toArray();
        $privilegeCardType->delete();

        $activityLogger->log('privilege_card_type.deleted', 'Suppression d’une carte privilège.', PrivilegeCardType::class, $snapshot, $request);

        return redirect()->route('super-admin.privilege-card-types.index')
            ->with('success', 'La carte privilège a été supprimée.');
    }

    public function toggleStatus(Request $request, PrivilegeCardType $privilegeCardType, ActivityLogger $activityLogger): RedirectResponse
    {
        $privilegeCardType->update([
            'status' => $privilegeCardType->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log('privilege_card_type.status_toggled', 'Changement d’état d’une carte privilège.', $privilegeCardType, [
            'status' => $privilegeCardType->status,
        ], $request);

        return back()->with('success', 'L’état de la carte privilège a été mis à jour.');
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
                'discount_value' => ['La réduction en pourcentage ne peut pas dépasser 100%.'],
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

    private function generateCardNumber(string $typeCode): string
    {
        $normalizedCode = preg_replace('/[^A-Z0-9]/', '', Str::upper($typeCode)) ?: 'CARD';
        $prefix = Str::substr($normalizedCode, 0, 4);

        do {
            $number = 'PVC-'.$prefix.'-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (PrivilegeCard::query()->where('card_number', $number)->exists());

        return $number;
    }

    private function walletLinksForCards(iterable $cards, GoogleWalletService $googleWallet): array
    {
        $links = [];

        foreach ($cards as $card) {
            $isEligible = $card->status === 'active'
                && ($card->expires_at === null || $card->expires_at->isFuture())
                && (bool) $card->has_paid_session;

            if (! $isEligible) {
                $links[$card->id] = [
                    'eligible' => false,
                    'apple' => null,
                    'android' => null,
                    'android_error' => null,
                ];

                continue;
            }

            $androidUrl = null;
            $androidError = null;

            try {
                $androidUrl = $googleWallet->buildSaveUrl($card);
            } catch (WalletConfigurationException $exception) {
                $androidError = $exception->getMessage();
            }

            $links[$card->id] = [
                'eligible' => true,
                'apple' => route('api.public.privilege-cards.pass.apple', [
                    'card' => $card->id,
                    'expires' => now()->addMinutes(10)->timestamp,
                ]),
                'android' => $androidUrl,
                'android_error' => $androidError,
            ];
        }

        return $links;
    }
}
