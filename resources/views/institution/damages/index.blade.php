@extends('institution.layouts.app')

@section('title', config('app.name').' | Dommages')
@section('page-title', 'Dommages')
@section('page-description', 'Dommages déclarés par les usagers publics.')

@section('content')
    @include('partials.page-loader', [
        'title' => 'Chargement des dommages',
        'message' => 'Nous préparons la liste selon vos filtres.',
    ])

    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
        $statusClass = fn (?string $status) => match ($status) {
            'resolved' => 'chip-success',
            'in_progress' => 'chip-warning',
            'rejected' => 'chip-danger',
            default => 'chip-neutral',
        };
    @endphp

    @include('institution.partials.stats-cards', [
        'cards' => [
            [
                'label' => 'Dommages',
                'value' => number_format($damagesStats['total'] ?? 0, 0, ',', ' '),
                'help' => 'Total affiché avec les filtres actifs.',
                'tone' => 'blue',
            ],
            [
                'label' => 'En cours',
                'value' => number_format($damagesStats['in_progress'] ?? 0, 0, ',', ' '),
                'help' => 'Dossiers de dommages en traitement.',
                'tone' => 'orange',
            ],
            [
                'label' => 'Justificatifs',
                'value' => number_format($damagesStats['with_attachment'] ?? 0, 0, ',', ' '),
                'help' => 'Déclarations avec fichier joint.',
                'tone' => 'pink',
            ],
            [
                'label' => 'Montant estimé',
                'value' => number_format($damagesStats['estimated_amount'] ?? 0, 0, ',', ' '),
                'help' => 'Total estimé en FCFA.',
                'tone' => 'green',
            ],
        ],
    ])

    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Dommages déclarés</div>
            </div>
            <span class="status-chip">{{ $damages->total() }} élément(s)</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Référence, signal, résumé du dommage">
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
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="damage_resolution_status" class="form-select">
                        <option value="">Tous</option>
                        <option value="submitted" @selected(request('damage_resolution_status') === 'submitted')>Soumis</option>
                        <option value="in_progress" @selected(request('damage_resolution_status') === 'in_progress')>En cours</option>
                        <option value="resolved" @selected(request('damage_resolution_status') === 'resolved')>Résolus</option>
                        <option value="rejected" @selected(request('damage_resolution_status') === 'rejected')>Rejetés</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Justificatif</label>
                    <select name="attachment" class="form-select">
                        <option value="">Tous</option>
                        <option value="with" @selected(request('attachment') === 'with')>Avec fichier</option>
                        <option value="without" @selected(request('attachment') === 'without')>Sans fichier</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('institution.damages.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $damages->total() }} dommage(s)</div>
            <div class="table-meta">Page {{ $damages->currentPage() }} / {{ $damages->lastPage() }}</div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Signal</th>
                            <th>Résumé</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Justificatif</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($damages as $report)
                            <tr>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->reference }}</span>
                                        <span class="meta-subtitle">{{ $report->damage_declared_at?->format('d/m/Y H:i') ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->signal_label ?? $report->incident_type }}</span>
                                        <span class="meta-subtitle">{{ $report->commune?->name ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->damage_summary ?: 'Déclaration de dommage enregistrée' }}</span>
                                        <span class="meta-subtitle">{{ \Illuminate\Support\Str::limit($report->damage_notes ?: 'Aucun détail complémentaire fourni.', 90) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="meta-title">
                                        {{ $report->damage_amount_estimated !== null
                                            ? number_format((float) $report->damage_amount_estimated, 0, ',', ' ').' FCFA'
                                            : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-chip {{ $statusClass($report->damage_resolution_status ?? 'submitted') }}">
                                        {{ $label::damage($report->damage_resolution_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-chip {{ !empty($report->damage_attachment) ? 'chip-success' : 'chip-neutral' }}">
                                        {{ !empty($report->damage_attachment) ? 'Disponible' : 'Aucun' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('institution.reports.show', $report) }}" class="btn btn-sm btn-outline-dark">Voir le détail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-secondary py-4">Aucun dommage disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $damages->currentPage() }} sur {{ $damages->lastPage() }}</div>
            {{ $damages->links() }}
        </div>
    </section>
@endsection
