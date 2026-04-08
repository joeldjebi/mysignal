@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Usagers publics')
@section('page-title', 'Usagers publics')
@section('page-description', 'Creer et piloter les comptes publics particuliers et entreprises.')

@section('header-badges')
    <span class="badge-soft">{{ $publicUsers->total() }} usagers</span>
    <a href="{{ route('super-admin.public-users.create') }}" class="btn btn-dark">
        Nouvel usager
    </a>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des usagers publics</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, telephone, email, entreprise">
                </div>
                <div class="col-md-3">
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
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">RAZ</a>
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
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($publicUsers as $publicUser)
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
                            <td><span class="status-chip">{{ $publicUser->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.public-users.edit', $publicUser) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.public-users.toggle-status', $publicUser) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ $publicUser->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.public-users.destroy', $publicUser) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucun usager public enregistre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $publicUsers->currentPage() }} sur {{ $publicUsers->lastPage() }}</div>
            {{ $publicUsers->links() }}
        </div>
    </section>
@endsection
