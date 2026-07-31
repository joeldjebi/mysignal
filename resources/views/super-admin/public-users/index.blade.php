@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Usagers publics')
@section('page-title', 'Usagers publics')
@section('page-description', 'Créer et piloter les comptes publics particuliers et entreprises.')

@section('header-badges')
    @php
        $authUser = auth()->user();
        $canManagePublicUsers = $authUser?->hasEffectivePermissionCode('SA_PUBLIC_USERS_MANAGE') ?? false;
        $canCreatePublicUsers = ($authUser?->hasEffectivePermissionCode('SA_PUBLIC_USERS_CREATE') ?? false) || $canManagePublicUsers;
    @endphp
    <span class="badge-soft">{{ $publicUsers->total() }} usagers</span>
    <span class="badge-soft">{{ number_format($publicUserStats['with_push'] ?? 0, 0, ',', ' ') }} avec notifications</span>
    @if ($canManagePublicUsers)
        <a href="{{ route('super-admin.public-users.push-notifications.index') }}" class="btn btn-outline-dark">
            Notifications UP
        </a>
    @endif
    @if ($canCreatePublicUsers)
        <a href="{{ route('super-admin.public-users.create') }}" class="btn btn-dark">
            Nouvel usager
        </a>
    @endif
@endsection

@section('content')
    @php
        $authUser = auth()->user();
        $canManagePublicUsers = $authUser?->hasEffectivePermissionCode('SA_PUBLIC_USERS_MANAGE') ?? false;
        $canUpdatePublicUsers = ($authUser?->hasEffectivePermissionCode('SA_PUBLIC_USERS_UPDATE') ?? false) || $canManagePublicUsers;
        $canDeletePublicUsers = ($authUser?->hasEffectivePermissionCode('SA_PUBLIC_USERS_DELETE') ?? false) || $canManagePublicUsers;
        $canTogglePublicUsers = ($authUser?->hasEffectivePermissionCode('SA_PUBLIC_USERS_TOGGLE_STATUS') ?? false) || $canManagePublicUsers;
    @endphp
    @include('partials.page-loader', [
        'title' => 'Chargement des usagers',
        'message' => 'Nous préparons les données demandées.',
    ])

    <style>
        .sa-period-dashboard .metric-card,
        .sa-period-dashboard .chart-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            height: 100%;
            box-shadow: 0 18px 44px rgba(16,42,67,.06);
        }
        .sa-period-dashboard .metric-card { border-top: 4px solid #6791ff; }
        .sa-period-dashboard .row.g-2 > div:nth-child(4n+2) .metric-card { border-top-color: #ff0068; }
        .sa-period-dashboard .row.g-2 > div:nth-child(4n+3) .metric-card { border-top-color: #ffa117; }
        .sa-period-dashboard .row.g-2 > div:nth-child(4n+4) .metric-card { border-top-color: #5bebaf; }
        .sa-period-dashboard .metric-label {
            color: #6b7c93;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .sa-period-dashboard .metric-value {
            color: #183447;
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1.1;
            margin-top: .35rem;
        }
        .sa-period-dashboard .chart-frame { min-height: 280px; }
    </style>

    <div class="sa-period-dashboard">
        <section class="row g-2 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Usagers</div>
                    <div class="metric-value">{{ number_format($publicUserStats['total'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Comptes créés sur la période.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Actifs</div>
                    <div class="metric-value">{{ number_format($publicUserStats['active'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Usagers publics actifs.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Inactifs</div>
                    <div class="metric-value">{{ number_format($publicUserStats['inactive'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Comptes désactivés.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Notifications</div>
                    <div class="metric-value">{{ number_format($publicUserStats['with_push'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Usagers avec token actif.</div>
                </div>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Statut des usagers</div>
                    <div class="small text-secondary mb-3">Répartition sur la période sélectionnée.</div>
                    <div id="publicUserStatusChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Types d’usagers</div>
                    <div class="small text-secondary mb-3">Principaux profils enregistrés.</div>
                    <div id="publicUserTypeChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Nouveaux usagers</div>
                    <div class="small text-secondary mb-3">Évolution journalière.</div>
                    <div id="publicUserTrendChart" class="chart-frame"></div>
                </div>
            </div>
        </section>
    </div>

    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des usagers publics</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, téléphone, email, entreprise">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Période</label>
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="today" @selected(request('period') === 'today')>Aujourd’hui</option>
                        <option value="7d" @selected(request('period') === '7d')>7 jours</option>
                        <option value="30d" @selected(blank(request('period')) || request('period') === '30d')>30 jours</option>
                        <option value="month" @selected(request('period') === 'month')>Mois en cours</option>
                        <option value="year" @selected(request('period') === 'year')>Année en cours</option>
                        <option value="custom" @selected(request('period') === 'custom')>Personnalisée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Type</label>
                    <select name="public_user_type_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($publicUserTypes as $publicUserType)
                            <option value="{{ $publicUserType->id }}" @selected((string) request('public_user_type_id') === (string) $publicUserType->id)>{{ $publicUserType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Notifications</label>
                    <select name="push_token" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('push_token') === 'active')>Token actif</option>
                        <option value="none" @selected(request('push_token') === 'none')>Aucun token actif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Par page</label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach ([12, 25, 50, 100] as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected((int) ($perPage ?? request('per_page', 12)) === $perPageOption)>{{ $perPageOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>
        <div class="table-responsive mt-3">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Usager</th>
                        <th>Type</th>
                        <th>Commune</th>
                        <th>Tarification</th>
                        <th>Abonnement</th>
                        <th>Notifications</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($publicUsers->isNotEmpty())
                        @foreach ($publicUsers as $publicUser)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $publicUser->first_name }} {{ $publicUser->last_name }}</div>
                                <div class="small text-secondary">{{ $publicUser->phone }}</div>
                                <div class="small text-secondary">{{ $publicUser->email ?: '-' }}</div>
                                @if ($publicUser->company_name)
                                    <div class="small text-secondary">{{ $publicUser->company_name }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $publicUser->publicUserType?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $publicUser->publicUserType?->profile_kind === 'business' ? 'Entreprise' : 'Particulier' }}</div>
                            </td>
                            <td>{{ $publicUser->commune ?: '-' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $publicUser->publicUserType?->pricingRule?->label ?: '-' }}</div>
                                <div class="small text-secondary">{{ $publicUser->publicUserType?->pricingRule ? number_format($publicUser->publicUserType->pricingRule->amount, 0, ',', ' ') . ' ' . $publicUser->publicUserType->pricingRule->currency : '-' }}</div>
                            </td>
                            <td>
                                @php
                                    $subscription = $publicUser->latestSubscription;
                                    $subscriptionIsUsable = $subscription
                                        && $subscription->status === 'active'
                                        && ($subscription->end_date === null || $subscription->end_date->copy()->addDays((int) $subscription->grace_period_days)->isFuture());
                                    $subscriptionLabels = [
                                        'pending' => 'Paiement en attente',
                                        'active' => 'Actif',
                                        'expired' => 'Expiré',
                                        'cancelled' => 'Annulé',
                                        'suspended' => 'Suspendu',
                                        'payment_failed' => 'Paiement échoué',
                                    ];
                                @endphp
                                @if ($subscription)
                                    <div class="fw-semibold">
                                        {{ $subscriptionIsUsable ? 'Actif' : ($subscriptionLabels[$subscription->status] ?? $subscription->status) }}
                                    </div>
                                    <div class="small text-secondary">{{ $subscription->plan?->name ?: 'Plan non renseigné' }}</div>
                                    <div class="small text-secondary">
                                        {{ $subscription->end_date ? 'Fin '.$subscription->end_date->format('d/m/Y') : 'En attente d’activation' }}
                                    </div>
                                @else
                                    <span class="status-chip">Aucun</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $activeTokens = $publicUser->activeDeviceTokens;
                                    $latestToken = $publicUser->latestDeviceToken;
                                    $platforms = $activeTokens
                                        ->pluck('platform')
                                        ->filter()
                                        ->map(fn ($platform) => strtoupper($platform))
                                        ->unique()
                                        ->values();
                                @endphp
                                @if ($activeTokens->isNotEmpty())
                                    <div class="fw-semibold">{{ $activeTokens->count() }} token{{ $activeTokens->count() > 1 ? 's' : '' }} actif{{ $activeTokens->count() > 1 ? 's' : '' }}</div>
                                    <div class="small text-secondary">{{ $platforms->isNotEmpty() ? $platforms->join(', ') : 'Plateforme non renseignée' }}</div>
                                    <div class="small text-secondary">
                                        {{ $latestToken?->last_seen_at ? 'Dernier : '.$latestToken->last_seen_at->format('d/m/Y H:i') : 'Dernier passage non renseigné' }}
                                    </div>
                                @else
                                    <span class="status-chip">Aucun token actif</span>
                                    @if ((int) $publicUser->device_tokens_count > 0)
                                        <div class="small text-secondary">{{ $publicUser->device_tokens_count }} ancien{{ $publicUser->device_tokens_count > 1 ? 's' : '' }} token{{ $publicUser->device_tokens_count > 1 ? 's' : '' }} révoqué{{ $publicUser->device_tokens_count > 1 ? 's' : '' }}</div>
                                    @endif
                                @endif
                            </td>
                            <td><span class="status-chip">{{ $publicUser->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.public-users.show', $publicUser) }}" class="btn btn-sm btn-dark">Détails</a>
                                    @if ($canUpdatePublicUsers)
                                        <a href="{{ route('super-admin.public-users.edit', $publicUser) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    @endif
                                    @if ($canTogglePublicUsers)
                                        <form method="POST" action="{{ route('super-admin.public-users.toggle-status', $publicUser) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $publicUser->status === 'active' ? 'Désactiver' : 'Activer' }}</button>
                                        </form>
                                    @endif
                                    @if ($canDeletePublicUsers)
                                        <form method="POST" action="{{ route('super-admin.public-users.destroy', $publicUser) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="8" class="text-center text-secondary">Aucun usager public enregistré.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $publicUsers->currentPage() }} sur {{ $publicUsers->lastPage() }}</div>
            {{ $publicUsers->links() }}
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusBreakdown = @json($statusBreakdown);
            const typeBreakdown = @json($typeBreakdown);
            const trend = @json($trend);

            new ApexCharts(document.querySelector('#publicUserStatusChart'), {
                chart: { type: 'donut', height: 280 },
                labels: statusBreakdown.map((item) => item.label),
                series: statusBreakdown.map((item) => Number(item.value || 0)),
                colors: ['#5bebaf', '#ff0068'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#publicUserTypeChart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false } },
                series: [{ name: 'Usagers', data: typeBreakdown.map((item) => Number(item.value || 0)) }],
                xaxis: { categories: typeBreakdown.map((item) => item.label) },
                colors: ['#6791ff'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '44%' } },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#publicUserTrendChart'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Nouveaux usagers', data: trend.map((item) => Number(item.value || 0)) }],
                xaxis: { categories: trend.map((item) => item.label) },
                colors: ['#ffa117'],
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
            }).render();
        });
    </script>
@endpush
