@extends('institution.layouts.app')

@section('title', config('app.name').' | Signalements')
@section('page-title', 'File des signaux')
@section('page-description', 'Signalements reçus par votre institution.')

@section('content')
    @include('partials.page-loader', [
        'title' => 'Chargement des signalements',
        'message' => 'Nous préparons la liste selon vos filtres.',
    ])

    @php
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $features ?? [], true);
        $canViewDamageInfo = in_array('INSTITUTION_REPORT_DAMAGE_ACCESS', $features ?? [], true);
        $label = \App\Support\Ui\InstitutionLabel::class;
        $statusClass = fn ($status) => match ($status) {
            'resolved', 'paid' => 'chip-success',
            'in_progress' => 'chip-warning',
            'rejected', 'failed' => 'chip-danger',
            default => 'chip-neutral',
        };
        $filterQuery = request()->except(['page', 'identifier_group']);
    @endphp

    @include('institution.partials.stats-cards', [
        'cards' => [
            [
                'label' => 'Signalements',
                'value' => number_format($reportsStats['total'] ?? 0, 0, ',', ' '),
                'help' => 'Total affiché avec les filtres actifs.',
                'tone' => 'blue',
            ],
            [
                'label' => 'À traiter',
                'value' => number_format($reportsStats['submitted'] ?? 0, 0, ',', ' '),
                'help' => 'Signalements encore non pris en charge.',
                'tone' => 'orange',
            ],
            [
                'label' => 'En cours',
                'value' => number_format($reportsStats['in_progress'] ?? 0, 0, ',', ' '),
                'help' => 'Signalements déjà en traitement.',
                'tone' => 'pink',
            ],
            [
                'label' => $canViewPaymentInfo ? 'Payés' : 'Résolus',
                'value' => number_format($canViewPaymentInfo ? ($reportsStats['paid'] ?? 0) : ($reportsStats['resolved'] ?? 0), 0, ',', ' '),
                'help' => $canViewPaymentInfo ? 'Signalements avec paiement validé.' : 'Signalements clôturés par l’institution.',
                'tone' => 'green',
            ],
        ],
    ])

    <section class="panel-card mb-4">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Regroupements par identifiant</div>
                <div class="small text-secondary">
                    Les zones ci-dessous regroupent les signalements liés aux identifiants géolocalisés sur une surface de {{ number_format($groupingSurfaceSquareMeters, 0, ',', ' ') }} m².
                </div>
            </div>
            @if ($selectedIdentifierGroup)
                <a href="{{ route('institution.reports.index', $filterQuery) }}" class="btn btn-sm btn-outline-secondary">Afficher tous les signalements</a>
            @endif
        </div>

        @if ($selectedIdentifierGroup)
            <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
                <div>
                    <div class="fw-semibold">{{ $selectedIdentifierGroup['label'] }} sélectionnée</div>
                    <div class="small">La liste affiche uniquement les signalements de ce regroupement.</div>
                </div>
                @if (($selectedIdentifierGroup['open_reports_count'] ?? 1) > 0)
                    <form method="POST" action="{{ route('institution.reports.identifier-groups.resolve') }}" class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
                        @csrf
                        @method('PATCH')
                        @foreach (request()->except(['page']) as $key => $value)
                            @if (is_scalar($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <input type="hidden" name="identifier_group" value="{{ $selectedIdentifierGroup['key'] }}">
                        <input type="hidden" name="official_response" value="Signalements résolus par l’institution pour cette zone.">
                        <button class="btn btn-success">Résoudre ce regroupement</button>
                    </form>
                @endif
            </div>
        @endif

        <div class="row g-3">
            @forelse ($identifierGroups as $group)
                <div class="col-md-6 col-xl-4">
                    <div class="surface-soft h-100 p-3 border rounded-3 {{ ($selectedIdentifierGroup['key'] ?? null) === $group['key'] ? 'border-primary' : '' }}">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <div>
                                <div class="fw-semibold">{{ $group['label'] }}</div>
                                <div class="small text-secondary">{{ $group['area_label'] }}</div>
                            </div>
                            <span class="status-chip">{{ number_format($group['reports_count'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="status-chip chip-warning">{{ number_format($group['open_reports_count'], 0, ',', ' ') }} à traiter</span>
                            <span class="status-chip chip-neutral">{{ number_format($groupingSideMeters, 0, ',', ' ') }} m de côté</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('institution.reports.index', array_merge($filterQuery, ['identifier_group' => $group['key']])) }}" class="btn btn-sm btn-dark">Voir la liste</a>
                            @if ($group['open_reports_count'] > 0)
                                <form method="POST" action="{{ route('institution.reports.identifier-groups.resolve') }}">
                                    @csrf
                                    @method('PATCH')
                                    @foreach ($filterQuery as $key => $value)
                                        @if (is_scalar($value))
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endif
                                    @endforeach
                                    <input type="hidden" name="identifier_group" value="{{ $group['key'] }}">
                                    <input type="hidden" name="official_response" value="Signalements résolus par l’institution pour cette zone.">
                                    <button class="btn btn-sm btn-outline-success">Résoudre</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-secondary py-4">Aucun regroupement disponible avec les filtres actifs.</div>
                </div>
            @endforelse
        </div>
    </section>

    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Signalements</div>
            </div>
            <span class="status-chip">{{ $reports->total() }} élément(s)</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Référence, signal, description">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Commune</label>
                    <select name="commune_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($communes as $commune)
                            <option value="{{ $commune->id }}" @selected((string) request('commune_id') === (string) $commune->id)>{{ $commune->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Identifiant</label>
                    <select name="meter_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($meters as $meter)
                            <option value="{{ $meter->id }}" @selected((string) request('meter_id') === (string) $meter->id)>
                                {{ $meter->meter_number ?: 'Sans numéro' }}@if($meter->label) · {{ $meter->label }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($canViewPaymentInfo)
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Paiement</label>
                        <select name="payment_status" class="form-select">
                            <option value="">Tous</option>
                            <option value="pending" @selected(request('payment_status') === 'pending')>En attente</option>
                            <option value="paid" @selected(request('payment_status') === 'paid')>Payé</option>
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Traitement</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>Soumis</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>En cours</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Résolus</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejetés</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('institution.reports.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $reports->total() }} signalement(s)</div>
            <div class="table-meta">Page {{ $reports->currentPage() }} / {{ $reports->lastPage() }}</div>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Signal</th>
                            <th>Identifiant</th>
                            <th>Traitement</th>
                            @if ($canViewPaymentInfo)
                                <th>Paiement</th>
                            @endif
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->reference }}</span>
                                        <span class="meta-subtitle">{{ $report->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->signal_label ?? $report->incident_type }}</span>
                                        <span class="meta-subtitle">
                                            {{ $report->commune?->name ?: '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->meter?->meter_number ?: '-' }}</span>
                                        <span class="meta-subtitle">{{ $report->meter?->label ?: 'Sans libellé' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="surface-soft">
                                        <div class="d-flex flex-column gap-2">
                                            <span class="status-chip {{ $statusClass($report->status) }}">{{ $label::status($report->status) }}</span>
                                            <span class="meta-subtitle">{{ $report->assignedTo?->name ?: 'Non assigné' }}</span>
                                        </div>
                                    </div>
                                </td>
                                @if ($canViewPaymentInfo)
                                    <td>
                                        <span class="status-chip {{ $statusClass($report->payment_status) }}">{{ $label::payment($report->payment_status) }}</span>
                                    </td>
                                @endif
                                <td class="text-end">
                                    <div class="report-actions">
                                        <a href="{{ route('institution.reports.show', $report) }}" class="btn btn-sm btn-outline-dark">Détails</a>

                                        @if ($report->status === 'submitted')
                                            <form method="POST" action="{{ route('institution.reports.take-over', $report) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-dark">Prendre en charge</button>
                                            </form>
                                        @endif

                                        @if (in_array($report->status, ['submitted', 'in_progress'], true))
                                            <form method="POST" action="{{ route('institution.reports.resolve', $report) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="official_response" value="Signalement résolu par l’institution.">
                                                <button class="btn btn-sm btn-outline-success">Résoudre</button>
                                            </form>
                                            <form method="POST" action="{{ route('institution.reports.reject', $report) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="official_response" value="Signalement rejeté après analyse institutionnelle.">
                                                <button class="btn btn-sm btn-outline-danger">Rejeter</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canViewPaymentInfo ? 6 : 5 }}" class="text-center text-secondary py-4">Aucun signalement disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $reports->currentPage() }} sur {{ $reports->lastPage() }}</div>
            {{ $reports->links() }}
        </div>
    </section>
@endsection
