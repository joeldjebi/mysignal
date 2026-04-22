@extends('partner.layouts.app')

@section('title', config('app.name').' | Users partenaire')
@section('page-title', 'Users partenaire')
@section('page-description', 'Creez les admins, managers et agents mobiles qui accedent a l application de reduction.')

@section('header-badges')
    <span class="badge-soft">{{ $users->total() }} users</span>
    <span class="badge-soft">{{ $roles->count() }} roles</span>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createUserModal">
        Nouveau user
    </button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des users</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, telephone">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Role</label>
                    <select name="role_code" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->code }}" @selected(request('role_code') === $role->code)>{{ $role->name }}</option>
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
            </div>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-dark">Filtrer</button>
                <a href="{{ route('partner.users.index') }}" class="btn btn-outline-secondary">RAZ</a>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $users->total() }} resultat{{ $users->total() > 1 ? 's' : '' }}</div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $user->name }}</span>
                                        <span class="meta-subtitle">{{ $user->email }}</span>
                                        <span class="meta-subtitle">{{ $user->phone ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                                <td><span class="small">{{ $user->permissionCodes()->join(', ') ?: '-' }}</span></td>
                                <td><span class="status-chip">{{ $user->status }}</span></td>
                                <td class="text-end">
                                    <div class="report-actions">
                                        <a href="{{ route('partner.users.edit', $user) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('partner.users.toggle-status', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $user->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary">Aucun user partenaire enregistre.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $users->currentPage() }} sur {{ $users->lastPage() }}</div>
            {{ $users->links() }}
        </div>
    </section>

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="createUserModalLabel">Nouveau user partenaire</h5>
                        <div class="text-secondary small">Créez un admin, manager ou agent mobile pour votre établissement.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('partner.users.store') }}">
                    @csrf
                    <div class="modal-body pt-3">
                        <div class="vstack gap-3">
                            <div>
                                <label class="form-label">Nom complet</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                                </div>
                                <div class="col-md-5">
                                    @include('partials.phone-field', ['value' => old('phone'), 'placeholder' => '0700000000'])
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Role</label>
                                <select name="role_code" class="form-select" required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->code }}" @selected(old('role_code') === $role->code)>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElement = document.getElementById('createUserModal');

                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            });
        </script>
    @endif
@endsection
