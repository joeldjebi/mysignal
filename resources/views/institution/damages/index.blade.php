@extends('institution.layouts.app')

@section('title', config('app.name').' | Dommages')
@section('page-title', 'Dommages')
@section('page-description', 'Liste des dommages declares par les usagers publics pour cette institution.')

@section('content')
    @php
        $statusLabel = fn (?string $status) => match ($status) {
            'submitted' => 'Soumis',
            'in_progress' => 'En cours',
            'resolved' => 'Resolu',
            'rejected' => 'Rejete',
            default => 'Soumis',
        };
        $statusClass = fn (?string $status) => match ($status) {
            'resolved' => 'chip-success',
            'in_progress' => 'chip-warning',
            'rejected' => 'chip-danger',
            default => 'chip-neutral',
        };
    @endphp

    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Dommages declares</div>
                <div class="text-secondary small">Une vue dediee pour suivre les dommages soumis apres resolution d un signalement.</div>
            </div>
            <span class="status-chip">{{ $damages->total() }} element(s)</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, signal, resume du dommage">
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
                        <option value="resolved" @selected(request('damage_resolution_status') === 'resolved')>Resolus</option>
                        <option value="rejected" @selected(request('damage_resolution_status') === 'rejected')>Rejetes</option>
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
                    <a href="{{ route('institution.damages.index') }}" class="btn btn-outline-secondary">RAZ</a>
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
                            <th>Reference</th>
                            <th>Signal</th>
                            <th>Resume</th>
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
                                        <span class="meta-title">{{ $report->damage_summary ?: 'Declaration de dommage enregistree' }}</span>
                                        <span class="meta-subtitle">{{ \Illuminate\Support\Str::limit($report->damage_notes ?: 'Aucun detail complementaire fourni.', 90) }}</span>
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
                                        {{ $statusLabel($report->damage_resolution_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-chip {{ !empty($report->damage_attachment) ? 'chip-success' : 'chip-neutral' }}">
                                        {{ !empty($report->damage_attachment) ? 'Disponible' : 'Aucun' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('institution.reports.show', $report) }}" class="btn btn-sm btn-outline-dark">Voir le detail</a>
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
