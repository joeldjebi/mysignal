@extends('institution.layouts.app')

@section('title', config('app.name').' | Usagers publics')
@section('page-title', 'Usagers publics')
@section('page-description', 'Usagers publics liés aux signalements de votre institution.')

@section('content')
    @include('partials.page-loader', [
        'title' => 'Chargement des usagers',
        'message' => 'Nous préparons la liste selon vos filtres.',
    ])

    @php
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $features ?? [], true);
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp

    @include('institution.partials.stats-cards', [
        'cards' => [
            [
                'label' => 'Usagers',
                'value' => number_format($reportUsersStats['total'] ?? 0, 0, ',', ' '),
                'help' => 'Total affiché avec les filtres actifs.',
                'tone' => 'blue',
            ],
            [
                'label' => 'Actifs',
                'value' => number_format($reportUsersStats['active'] ?? 0, 0, ',', ' '),
                'help' => 'Comptes usagers actifs.',
                'tone' => 'orange',
            ],
            [
                'label' => 'Avec signalement',
                'value' => number_format($reportUsersStats['with_reports'] ?? 0, 0, ',', ' '),
                'help' => 'Usagers ayant déjà signalé.',
                'tone' => 'pink',
            ],
            [
                'label' => 'Inactifs',
                'value' => number_format($reportUsersStats['inactive'] ?? 0, 0, ',', ' '),
                'help' => 'Comptes non actifs.',
                'tone' => 'green',
            ],
        ],
    ])

    <section class="panel-card">
        <div class="fw-bold mb-3">Usagers publics</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, téléphone, email">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Commune</label>
                    <select name="commune" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($communes as $commune)
                            <option value="{{ $commune }}" @selected(request('commune') === $commune)>{{ $commune }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Signalement</label>
                    <select name="has_reports" class="form-select">
                        <option value="">Tous</option>
                        <option value="yes" @selected(request('has_reports') === 'yes')>A déjà signalé</option>
                        <option value="no" @selected(request('has_reports') === 'no')>N’a pas signalé</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('institution.report-users.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $users->total() }} usager(s)</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Usager</th>
                        <th>Téléphone</th>
                        <th>Commune</th>
                        <th>Identifiants</th>
                        <th>Signalements</th>
                        @if ($canViewPaymentInfo)
                            <th>Payés</th>
                        @endif
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div>{{ $user->first_name }} {{ $user->last_name }}</div>
                                <div class="small text-secondary">{{ $user->email ?: '-' }}</div>
                            </td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->commune ?: '-' }}</td>
                            <td><span class="status-chip">{{ $user->identifiers_count }}</span></td>
                            <td><span class="status-chip">{{ $user->reports_count }}</span></td>
                            @if ($canViewPaymentInfo)
                                <td><span class="status-chip">{{ $user->paid_reports_count }}</span></td>
                            @endif
                            <td><span class="status-chip">{{ $label::status($user->status) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('institution.report-users.show', $user) }}" class="btn btn-sm btn-outline-dark">Détails</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canViewPaymentInfo ? 8 : 7 }}" class="text-center text-secondary">Aucun usager disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $users->currentPage() }} sur {{ $users->lastPage() }}</div>
            {{ $users->links() }}
        </div>
    </section>
@endsection
