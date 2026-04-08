@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Communes')
@section('page-title', 'Communes')
@section('page-description', 'Gerer les communes et leur rattachement aux villes.')

@section('header-badges')
    <span class="badge-soft">{{ $communes->total() }} communes</span>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Nouvelle commune</div>
                <form method="POST" action="{{ route('super-admin.communes.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Ville</label>
                        <select name="city_id" class="form-select" required>
                            <option value="">Selectionner</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }} · {{ $city->country?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" placeholder="Cocody" required>
                    </div>
                    <div>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="ABJ-COCODY" required>
                    </div>
                    <button type="submit" class="btn btn-dark">Creer</button>
                </form>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Liste des communes</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou code">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Ville</label>
                            <select name="city_id" class="form-select">
                                <option value="">Toutes</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Statut</label>
                            <select name="status" class="form-select">
                                <option value="">Tous</option>
                                <option value="active" @selected(request('status') === 'active')>Actif</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-dark w-100">Filtrer</button>
                            <a href="{{ route('super-admin.communes.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>
                <div class="table-toolbar">
                    <div class="table-meta">{{ $communes->total() }} resultat{{ $communes->total() > 1 ? 's' : '' }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Ville</th>
                                <th>Code</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($communes as $commune)
                                <tr>
                                    <td>{{ $commune->name }}</td>
                                    <td>{{ $commune->city?->name }}</td>
                                    <td>{{ $commune->code }}</td>
                                    <td><span class="status-chip">{{ $commune->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.communes.edit', $commune) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.communes.toggle-status', $commune) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $commune->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.communes.destroy', $commune) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Aucune commune enregistree.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $communes->currentPage() }} sur {{ $communes->lastPage() }}</div>
                    {{ $communes->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
