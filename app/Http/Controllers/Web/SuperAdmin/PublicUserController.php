<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSector;
use App\Models\Commune;
use App\Models\PublicUser;
use App\Models\PublicUserType;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicUserController extends Controller
{
    public function index(): View
    {
        $perPage = min(max((int) request()->integer('per_page', 12), 1), 100);
        $query = PublicUser::query()->with([
            'publicUserType.pricingRule',
            'latestSubscription.plan',
            'latestSubscription.payments',
            'latestDeviceToken',
            'activeDeviceTokens',
        ]);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('company_name', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('public_user_type_id'))) {
            $query->where('public_user_type_id', request('public_user_type_id'));
        }

        if (request('push_token') === 'active') {
            $query->whereHas('activeDeviceTokens');
        }

        if (request('push_token') === 'none') {
            $query->whereDoesntHave('activeDeviceTokens');
        }

        $this->applyPeriodFilter($query, request(), 'public_users.created_at');

        $statsQuery = clone $query;
        $publicUserStats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('status', 'active')->count(),
            'inactive' => (clone $statsQuery)->where('status', 'inactive')->count(),
            'with_push' => (clone $statsQuery)->whereHas('activeDeviceTokens')->count(),
        ];
        $statusBreakdown = [
            ['label' => 'Actifs', 'value' => $publicUserStats['active']],
            ['label' => 'Inactifs', 'value' => $publicUserStats['inactive']],
        ];
        $typeBreakdown = (clone $statsQuery)
            ->selectRaw('public_user_type_id, COUNT(*) as total')
            ->with('publicUserType:id,name')
            ->groupBy('public_user_type_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->publicUserType?->name ?: 'Non renseigné',
                'value' => (int) $row->total,
            ])
            ->values()
            ->all();
        $trend = (clone $statsQuery)
            ->selectRaw('DATE(created_at) as period_label')
            ->selectRaw('COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->map(fn ($row): array => [
                'label' => Carbon::parse($row->period_label)->format('d/m'),
                'value' => (int) $row->total,
            ])
            ->all();

        return view('super-admin.public-users.index', [
            'publicUsers' => $query
                ->withCount([
                    'activeDeviceTokens as active_device_tokens_count',
                    'deviceTokens as device_tokens_count',
                ])
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'publicUserStats' => $publicUserStats,
            'statusBreakdown' => $statusBreakdown,
            'typeBreakdown' => $typeBreakdown,
            'trend' => $trend,
            'perPage' => $perPage,
            'publicUserTypes' => PublicUserType::query()->with('pricingRule')->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'communes' => Commune::query()->where('status', 'active')->orderBy('name')->get(),
            'businessSectors' => BusinessSector::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'pushEligibleUsersCount' => PublicUser::query()->whereHas('activeDeviceTokens')->count(),
        ]);
    }

    private function applyPeriodFilter($query, Request $request, string $column): void
    {
        [$startDate, $endDate] = $this->periodBounds($request);

        if ($startDate !== null) {
            $query->where($column, '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where($column, '<=', $endDate);
        }
    }

    private function periodBounds(Request $request): array
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

    public function create(): View
    {
        return view('super-admin.public-users.create', [
            'publicUserTypes' => PublicUserType::query()->with('pricingRule')->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'communes' => Commune::query()->where('status', 'active')->orderBy('name')->get(),
            'businessSectors' => BusinessSector::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);

        $publicUser = PublicUser::query()->create([
            'public_user_type_id' => $attributes['public_user_type_id'],
            'first_name' => $attributes['first_name'],
            'last_name' => $attributes['last_name'],
            'phone' => $attributes['phone'],
            'is_whatsapp_number' => (bool) ($attributes['is_whatsapp_number'] ?? false),
            'email' => $attributes['email'] ?? null,
            'company_name' => $attributes['company_name'] ?? null,
            'company_registration_number' => $attributes['company_registration_number'] ?? null,
            'tax_identifier' => $attributes['tax_identifier'] ?? null,
            'business_sector' => $attributes['business_sector'] ?? null,
            'company_address' => $attributes['company_address'] ?? null,
            'commune' => $attributes['commune'],
            'address' => $attributes['address'] ?? null,
            'password' => Hash::make($attributes['password']),
            'status' => 'active',
        ]);

        $activityLogger->log(
            'public_user.created',
            'Création d’un usager public.',
            $publicUser,
            [
                'public_user_type_id' => $publicUser->public_user_type_id,
                'phone' => $publicUser->phone,
            ],
            $request,
            $request->user(),
        );

        return redirect()->route('super-admin.public-users.index')
            ->with('success', 'L’usager public a été créé.');
    }

    public function edit(PublicUser $publicUser): View
    {
        return view('super-admin.public-users.edit', [
            'publicUser' => $publicUser->load([
                'publicUserType.pricingRule',
                'incidentReports.application',
                'incidentReports.organization',
                'incidentReports.meter',
                'incidentReports.payments',
                'incidentReports.reparationCase',
            ]),
            'publicUserTypes' => PublicUserType::query()->with('pricingRule')->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'communes' => Commune::query()->where('status', 'active')->orderBy('name')->get(),
            'businessSectors' => BusinessSector::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function show(PublicUser $publicUser): View
    {
        $subscriptions = $publicUser->subscriptions()
            ->with(['plan', 'payments'])
            ->latest()
            ->paginate(8, ['*'], 'subscriptions_page')
            ->withQueryString();

        $reports = $publicUser->incidentReports()
            ->with([
                'application',
                'organization',
                'meter',
                'payments',
                'reparationCase',
                'commune',
            ])
            ->latest()
            ->get()
            ->filter(function ($report): bool {
                if (filled(request('report_search'))) {
                    $search = mb_strtolower(trim((string) request('report_search')));
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $report->reference,
                        $report->signal_label,
                        $report->signal_code,
                        $report->description,
                        $report->application?->name,
                        $report->organization?->name,
                    ])));

                    if (! str_contains($haystack, $search)) {
                        return false;
                    }
                }

                if (filled(request('report_status')) && $report->status !== request('report_status')) {
                    return false;
                }

                if (filled(request('report_case_status'))) {
                    $hasDamage = $report->damage_declared_at !== null || filled($report->damage_summary) || filled($report->damage_amount_estimated);
                    $slaBreached = filled($report->target_sla_hours) && $report->created_at !== null
                        ? (($report->created_at->diffInMinutes($report->resolved_at ?? now()) / 60) >= (float) $report->target_sla_hours)
                        : false;
                    $isEligibleForReparationCase = $slaBreached || $hasDamage;
                    $caseStatus = (string) request('report_case_status');

                    if ($caseStatus === 'opened' && ! $report->reparationCase) {
                        return false;
                    }

                    if ($caseStatus === 'to_open' && ($report->reparationCase || ! $isEligibleForReparationCase)) {
                        return false;
                    }

                    if ($caseStatus === 'not_eligible' && ($report->reparationCase || $isEligibleForReparationCase)) {
                        return false;
                    }
                }

                return true;
            })
            ->values();

        $perPage = 8;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedReports = new LengthAwarePaginator(
            $reports->forPage($currentPage, $perPage)->values(),
            $reports->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('super-admin.public-users.show', [
            'publicUser' => $publicUser->load([
                'publicUserType.pricingRule',
                'deviceTokens' => fn ($query) => $query->latest('last_seen_at')->latest(),
            ]),
            'subscriptions' => $subscriptions,
            'reports' => $paginatedReports,
            'reportStatuses' => ['submitted', 'in_progress', 'resolved', 'rejected', 'closed'],
            'bailiffUsers' => $this->resolveAssignableUsersByRole(['HUISSIER', 'BAILIFF']),
            'lawyerUsers' => $this->resolveAssignableUsersByRole(['AVOCAT', 'LAWYER']),
        ]);
    }

    public function update(Request $request, PublicUser $publicUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request, $publicUser);
        $before = [
            'public_user_type_id' => $publicUser->public_user_type_id,
            'first_name' => $publicUser->first_name,
            'last_name' => $publicUser->last_name,
            'phone' => $publicUser->phone,
            'email' => $publicUser->email,
            'business_sector' => $publicUser->business_sector,
            'commune' => $publicUser->commune,
        ];

        $payload = [
            'public_user_type_id' => $attributes['public_user_type_id'],
            'first_name' => $attributes['first_name'],
            'last_name' => $attributes['last_name'],
            'phone' => $attributes['phone'],
            'is_whatsapp_number' => (bool) ($attributes['is_whatsapp_number'] ?? false),
            'email' => $attributes['email'] ?? null,
            'company_name' => $attributes['company_name'] ?? null,
            'company_registration_number' => $attributes['company_registration_number'] ?? null,
            'tax_identifier' => $attributes['tax_identifier'] ?? null,
            'business_sector' => $attributes['business_sector'] ?? null,
            'company_address' => $attributes['company_address'] ?? null,
            'commune' => $attributes['commune'],
            'address' => $attributes['address'] ?? null,
        ];

        if (filled($attributes['password'] ?? null)) {
            $payload['password'] = Hash::make($attributes['password']);
        }

        $publicUser->update($payload);

        $activityLogger->log(
            'public_user.updated',
            'Mise à jour d’un usager public.',
            $publicUser,
            [
                'before' => $before,
                'after' => [
                    'public_user_type_id' => $publicUser->public_user_type_id,
                    'first_name' => $publicUser->first_name,
                    'last_name' => $publicUser->last_name,
                    'phone' => $publicUser->phone,
                    'email' => $publicUser->email,
                    'business_sector' => $publicUser->business_sector,
                    'commune' => $publicUser->commune,
                ],
            ],
            $request,
            $request->user(),
        );

        return redirect()->route('super-admin.public-users.index')
            ->with('success', 'L’usager public a été mis à jour.');
    }

    public function destroy(Request $request, PublicUser $publicUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $activityLogger->log(
            'public_user.deleted',
            'Suppression d’un usager public.',
            $publicUser,
            [
                'phone' => $publicUser->phone,
                'email' => $publicUser->email,
            ],
            $request,
            $request->user(),
        );
        $publicUser->delete();

        return redirect()->route('super-admin.public-users.index')
            ->with('success', 'L’usager public a été supprimé.');
    }

    public function toggleStatus(Request $request, PublicUser $publicUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $previousStatus = $publicUser->status;

        $publicUser->update([
            'status' => $publicUser->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log(
            'public_user.status_toggled',
            'Changement de statut d’un usager public.',
            $publicUser,
            [
                'before' => $previousStatus,
                'after' => $publicUser->status,
            ],
            $request,
            $request->user(),
        );

        return back()->with('success', 'Le statut de l’usager public a été mis à jour.');
    }

    private function validatedAttributes(Request $request, ?PublicUser $publicUser = null): array
    {
        $attributes = $request->validate([
            'public_user_type_id' => ['required', 'integer', 'exists:public_user_types,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{8,15}$/', Rule::unique('public_users', 'phone')->ignore($publicUser?->id)],
            'is_whatsapp_number' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('public_users', 'email')->ignore($publicUser?->id)],
            'company_name' => ['nullable', 'string', 'max:180'],
            'company_registration_number' => ['nullable', 'string', 'max:120'],
            'tax_identifier' => ['nullable', 'string', 'max:120'],
            'business_sector' => ['nullable', 'string', 'max:120', 'exists:business_sectors,name'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'commune' => ['required', 'string', 'max:120', 'exists:communes,name'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => [$publicUser ? 'nullable' : 'required', 'string', 'min:8'],
        ]);

        $publicUserType = PublicUserType::query()->findOrFail($attributes['public_user_type_id']);

        $typeCode = strtoupper((string) $publicUserType->code);

        if ($typeCode === 'UPE') {
            foreach ([
                'company_name' => 'La raison sociale est obligatoire.',
                'company_registration_number' => 'Le RCCM ou numéro d’immatriculation est obligatoire.',
            ] as $field => $message) {
                if (! filled($attributes[$field] ?? null)) {
                    throw ValidationException::withMessages([$field => [$message]]);
                }
            }
        }

        return $attributes;
    }

    private function resolveAssignableUsersByRole(array $codes): \Illuminate\Support\Collection
    {
        $roles = Role::query()
            ->whereIn('code', $codes)
            ->orWhere(function ($query) use ($codes): void {
                foreach ($codes as $code) {
                    $query->orWhere('name', 'like', '%'.$code.'%');
                }
            })
            ->pluck('id');

        if ($roles->isNotEmpty()) {
            return User::query()
                ->where('status', 'active')
                ->where(function ($query) use ($roles, $codes): void {
                    $query
                        ->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('roles.id', $roles))
                        ->orWhereHas('accesses', function ($accessQuery) use ($codes): void {
                            $accessQuery
                                ->where('status', 'active')
                                ->whereIn('portal', collect($codes)->map(fn ($code) => strtolower($code))->all());
                        });
                })
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return User::query()
            ->where('status', 'active')
            ->where('is_super_admin', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
