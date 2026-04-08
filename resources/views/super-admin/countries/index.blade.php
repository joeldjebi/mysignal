@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Pays')
@section('page-title', 'Pays')
@section('page-description', 'Creer, modifier, activer ou desactiver les pays disponibles dans la plateforme.')

@section('header-badges')
    <span class="badge-soft">{{ $countries->total() }} pays</span>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Nouveau pays</div>
                <form method="POST" action="{{ route('super-admin.countries.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" placeholder="Cote d Ivoire" required>
                    </div>
                    <div>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="CI" required>
                    </div>
                    <div>
                        <label class="form-label">Flag</label>
                        <input type="text" name="flag" class="form-control" placeholder="🇨🇮" value="{{ old('flag') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Indicatif</label>
                        <div class="input-group">
                            <span class="input-group-text">+</span>
                            <input type="text" name="dial_code" class="form-control" placeholder="225" value="{{ old('dial_code') }}" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark">Creer</button>
                </form>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Liste des pays</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou code">
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
                            <a href="{{ route('super-admin.countries.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>
                <div class="table-toolbar">
                    <div class="table-meta">{{ $countries->total() }} resultat{{ $countries->total() > 1 ? 's' : '' }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Code</th>
                                <th>Flag</th>
                                <th>Indicatif</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($countries as $country)
                                <tr>
                                    <td>{{ $country->name }}</td>
                                    <td>{{ $country->code }}</td>
                                    <td style="font-size: 1.2rem;">{{ $country->flag ?: '-' }}</td>
                                    <td>{{ $country->dial_code ? '+'.$country->dial_code : '-' }}</td>
                                    <td><span class="status-chip">{{ $country->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.countries.edit', $country) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.countries.toggle-status', $country) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm {{ $country->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $country->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.countries.destroy', $country) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-secondary">Aucun pays enregistre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $countries->currentPage() }} sur {{ $countries->lastPage() }}</div>
                    {{ $countries->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
