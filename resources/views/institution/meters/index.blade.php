@extends('institution.layouts.app')

@section('title', config('app.name').' | Compteurs')
@section('page-title', 'Compteurs')
@section('page-description', 'Liste des compteurs publics visibles pour cette institution.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Compteurs</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Numero, libelle, commune, adresse">
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
                    <a href="{{ route('institution.meters.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $meters->total() }} compteur(s)</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Reseau</th>
                        <th>Libelle</th>
                        <th>Commune</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($meters as $meter)
                        <tr>
                            <td class="fw-semibold">{{ $meter->meter_number }}</td>
                            <td>{{ $meter->network_type }}</td>
                            <td>{{ $meter->label ?: '-' }}</td>
                            <td>{{ $meter->commune ?: '-' }}</td>
                            <td><span class="status-chip">{{ $meter->status }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('institution.meters.show', $meter) }}" class="btn btn-sm btn-outline-dark">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucun compteur disponible.</td></tr>
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
