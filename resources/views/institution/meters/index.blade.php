@extends('institution.layouts.app')

@section('title', config('app.name').' | Identifiants')
@section('page-title', 'Identifiants')
@section('page-description', 'Liste des références rattachées aux signalements.')

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp
    <section class="panel-card">
        <div class="fw-bold mb-3">Identifiants</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Identifiant, libellé, commune, adresse">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('institution.meters.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $meters->total() }} identifiant(s)</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Identifiant</th>
                        <th>Catégorie</th>
                        <th>Usage</th>
                        <th>Catégorie métier / Institution</th>
                        <th>Libellé</th>
                        <th>Commune</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($meters as $meter)
                        <tr>
                            <td class="fw-semibold">{{ $meter->meter_number }}</td>
                            <td>{{ $label::humanize($meter->network_type) }}</td>
                            <td>
                                <span class="status-chip">
                                    {{ ($meter->gbonhi_assignments_count ?? 0) > 0 ? 'Gbonhi' : 'Personnel' }}
                                </span>
                            </td>
                            <td>
                                <div class="meta-stack">
                                    <span class="meta-title">{{ $meter->application?->name ?: '-' }}</span>
                                    <span class="meta-subtitle">{{ $meter->organization?->name ?: '-' }}</span>
                                </div>
                            </td>
                            <td>{{ $meter->label ?: '-' }}</td>
                            <td>{{ $meter->commune ?: '-' }}</td>
                            <td><span class="status-chip">{{ $label::status($meter->status) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('institution.meters.show', $meter) }}" class="btn btn-sm btn-outline-dark">Détails</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary">Aucun identifiant disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $meters->currentPage() }} sur {{ $meters->lastPage() }}</div>
            {{ $meters->links() }}
        </div>
    </section>
@endsection
