@extends('super-admin.layouts.app')

@section('title', config('app.name').' | AI sans institution')
@section('page-title', 'AI sans institution')
@section('page-description', 'Comptes admins institutionnels conservés après suppression de leur institution.')

@section('header-badges')
    <span class="badge-soft">{{ $admins->total() }} compte{{ $admins->total() > 1 ? 's' : '' }}</span>
    <a href="{{ route('super-admin.institution-admins.index') }}" class="btn btn-outline-secondary">Retour aux AI</a>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des AI sans institution</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, téléphone">
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
                </div>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $admins->total() }} résultat{{ $admins->total() > 1 ? 's' : '' }}</div>
            <a href="{{ route('super-admin.institution-admins.orphaned') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Créé par</th>
                        <th>Statut</th>
                        <th>Date de création</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admins as $admin)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $admin->name }}</div>
                                <div class="small text-secondary">{{ $admin->email }}</div>
                                <div class="small text-secondary">{{ $admin->phone ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $admin->creator?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $admin->creator?->email ?: '' }}</div>
                            </td>
                            <td><span class="status-chip">{{ $admin->status }}</span></td>
                            <td>{{ $admin->created_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary">Aucun AI sans institution.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $admins->currentPage() }} sur {{ $admins->lastPage() }}</div>
            {{ $admins->links() }}
        </div>
    </section>
@endsection
