@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Mes utilisateurs')
@section('page-title', 'Mes utilisateurs')
@section('page-description', 'Créer vos propres utilisateurs et leur attribuer vos rôles ou permissions directes.')

@section('header-badges')
    <span class="badge-soft">{{ $users->total() }} utilisateurs</span>
    <a href="{{ route('super-admin.login') }}" class="btn btn-outline-dark" target="_blank" rel="noopener">Page login SA</a>
    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createScopedUserModal">Nouvel utilisateur</button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des utilisateurs SA</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, téléphone">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Rôle</label>
                    <select name="role_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->name }}</option>
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
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Par page</label>
                    <select name="per_page" class="form-select">
                        @foreach ([12, 25, 50, 100] as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected((int) request('per_page', 12) === $perPageOption)>{{ $perPageOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button class="btn btn-dark w-100">OK</button>
                </div>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $users->total() }} résultat{{ $users->total() > 1 ? 's' : '' }}</div>
            <a href="{{ route('super-admin.scoped-users.index') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead><tr><th>Utilisateur</th><th>Rôles</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($users as $managedUser)
                        <tr>
                            <td><div class="fw-semibold">{{ $managedUser->name }}</div><div class="small text-secondary">{{ $managedUser->email }}</div></td>
                            <td>{{ $managedUser->roles->pluck('name')->join(', ') ?: '-' }}</td>
                            <td><span class="status-chip">{{ $managedUser->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a class="btn btn-sm btn-outline-dark" href="{{ route('super-admin.scoped-users.edit', $managedUser) }}">Modifier</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('super-admin.login') }}" target="_blank" rel="noopener">Connexion</a>
                                    <form method="POST" action="{{ route('super-admin.scoped-users.send-access', $managedUser) }}" onsubmit="return confirm('Un nouveau mot de passe temporaire sera généré puis envoyé par SMS. L’envoi e-mail sera activé dès que le service sera configuré. Continuer ?')">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">Envoyer accès SMS / e-mail</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.scoped-users.destroy', $managedUser) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary">Aucun utilisateur créé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </section>

    <div class="modal fade" id="createScopedUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('super-admin.scoped-users.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Nouvel utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('super-admin.scoped-users.partials.form', ['managedUser' => null, 'roles' => $roles, 'permissions' => $permissions, 'assignedRoleIds' => old('role_ids', []), 'assignedPermissionIds' => old('permission_ids', [])])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button class="btn btn-dark">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
