@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Sous-quartiers')
@section('page-title', 'Sous-quartiers')
@section('page-description', 'Gerer les sous-quartiers et leur rattachement aux quartiers.')

@section('header-badges')
    <span class="badge-soft">{{ $subNeighborhoods->total() }} sous-quartiers</span>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Nouveau sous-quartier</div>
                <form method="POST" action="{{ route('super-admin.sub-neighborhoods.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Quartier</label>
                        <select name="neighborhood_id" class="form-select" required>
                            <option value="">Selectionner</option>
                            @foreach ($neighborhoods as $neighborhood)
                                <option value="{{ $neighborhood->id }}" @selected(old('neighborhood_id') == $neighborhood->id)>{{ $neighborhood->name }} · {{ $neighborhood->commune?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Riviera Golf" required>
                    </div>
                    <div>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control" placeholder="ABJ-COCODY-RIVIERA-GOLF" required>
                    </div>
                    <button type="submit" class="btn btn-dark">Creer</button>
                </form>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Liste des sous-quartiers</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou code">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Quartier</label>
                            <select name="neighborhood_id" class="form-select">
                                <option value="">Tous</option>
                                @foreach ($neighborhoods as $neighborhood)
                                    <option value="{{ $neighborhood->id }}" @selected((string) request('neighborhood_id') === (string) $neighborhood->id)>{{ $neighborhood->name }}</option>
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
                        <div class="col-md-2">
                            <label class="form-label small text-secondary">Par page</label>
                            <select name="per_page" class="form-select">
                                @foreach ([12, 25, 50, 100] as $perPageOption)
                                    <option value="{{ $perPageOption }}" @selected((int) request('per_page', 12) === $perPageOption)>{{ $perPageOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-dark w-100">Filtrer</button>
                            <a href="{{ route('super-admin.sub-neighborhoods.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>
                <div class="table-toolbar">
                    <div class="table-meta">{{ $subNeighborhoods->total() }} resultat{{ $subNeighborhoods->total() > 1 ? 's' : '' }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Quartier</th>
                                <th>Code</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subNeighborhoods as $subNeighborhood)
                                <tr>
                                    <td>{{ $subNeighborhood->name }}</td>
                                    <td>{{ $subNeighborhood->neighborhood?->name }}</td>
                                    <td>{{ $subNeighborhood->code }}</td>
                                    <td><span class="status-chip">{{ $subNeighborhood->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.sub-neighborhoods.edit', $subNeighborhood) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.sub-neighborhoods.toggle-status', $subNeighborhood) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $subNeighborhood->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.sub-neighborhoods.destroy', $subNeighborhood) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Aucun sous-quartier enregistre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $subNeighborhoods->currentPage() }} sur {{ $subNeighborhoods->lastPage() }}</div>
                    {{ $subNeighborhoods->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
