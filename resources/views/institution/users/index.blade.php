@extends('institution.layouts.app')

@section('title', config('app.name').' | Collaborateurs')
@section('page-title', 'Collaborateurs')
@section('page-description', 'Créer les collaborateurs de l’institution et leur attribuer rôles et permissions.')

@section('header-badges')
    <span class="badge-soft">{{ $users->total() }} collaborateur{{ $users->total() > 1 ? 's' : '' }}</span>
    @if ($authorization['canManageInstitutionRoles'])
        <span class="badge-soft">{{ $roles->count() }} rôle{{ $roles->count() > 1 ? 's' : '' }}</span>
    @endif
    @if ($authorization['canManageInstitutionPermissions'])
        <span class="badge-soft">{{ $permissions->count() }} permissions</span>
    @endif
@endsection

@section('content')
    @php
        $uiLabel = \App\Support\Ui\InstitutionLabel::class;
    @endphp

    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Liste des collaborateurs</div>
                <div class="text-secondary small">Collaborateurs autorisés à accéder au portail institutionnel.</div>
            </div>
            <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#createInstitutionUserModal">
                Ajouter
            </button>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, téléphone">
                </div>
                @if ($authorization['canManageInstitutionRoles'])
                    <div class="col-md-4">
                        <label class="form-label small text-secondary">Rôle</label>
                        <select name="role_id" class="form-select">
                            <option value="">Tous</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
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
                <a href="{{ route('institution.users.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $users->total() }} résultat{{ $users->total() > 1 ? 's' : '' }}</div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Collaborateur</th>
                            @if ($authorization['canManageInstitutionRoles'])
                                <th>Rôles</th>
                            @endif
                            @if ($authorization['canManageInstitutionPermissions'])
                                <th>Droits directs</th>
                            @endif
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
                                @if ($authorization['canManageInstitutionRoles'])
                                    <td><span class="small">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</span></td>
                                @endif
                                @if ($authorization['canManageInstitutionPermissions'])
                                    <td><span class="small">{{ $user->permissions->pluck('name')->join(', ') ?: '-' }}</span></td>
                                @endif
                                <td><span class="status-chip">{{ $uiLabel::status($user->status) }}</span></td>
                                <td class="text-end">
                                    <div class="report-actions">
                                        <a href="{{ route('institution.users.edit', $user) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('institution.users.toggle-status', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $user->status === 'active' ? 'Désactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('institution.users.destroy', $user) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 3 + ($authorization['canManageInstitutionRoles'] ? 1 : 0) + ($authorization['canManageInstitutionPermissions'] ? 1 : 0) }}" class="text-center text-secondary">Aucun collaborateur enregistré.</td></tr>
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

    <div class="modal fade" id="createInstitutionUserModal" tabindex="-1" aria-labelledby="createInstitutionUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('institution.users.store') }}">
                    @csrf
                    <input type="hidden" name="_form" value="create_user">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createInstitutionUserModalLabel">Nouveau collaborateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                @include('partials.phone-field', ['label' => 'Téléphone', 'value' => old('phone'), 'placeholder' => '0700000000'])
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            @if ($authorization['canManageInstitutionRoles'])
                                <div class="col-md-5">
                                    <label class="form-label">Rôles</label>
                                    <div class="border rounded-3 p-2" style="max-height: 240px; overflow:auto;">
                                        @forelse ($roles as $role)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $role->id }}" name="role_ids[]" id="user-role-create-{{ $role->id }}" @checked(in_array($role->id, old('role_ids', [])))>
                                                <label class="form-check-label" for="user-role-create-{{ $role->id }}">{{ $role->name }}</label>
                                            </div>
                                        @empty
                                            <div class="text-secondary small">Aucun rôle local disponible pour le moment.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                            @if ($authorization['canManageInstitutionPermissions'])
                                <div class="{{ $authorization['canManageInstitutionRoles'] ? 'col-md-7' : 'col-12' }}">
                                    <label class="form-label">Droits directs</label>
                                    <div class="border rounded-3 p-3" style="max-height: 320px; overflow:auto;">
                                        @forelse ($groupedPermissions as $groupLabel => $groupPermissions)
                                            <div class="mb-3">
                                                <div class="small text-uppercase fw-bold text-secondary mb-2">{{ $groupLabel }}</div>
                                                @foreach ($groupPermissions as $permission)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="user-permission-create-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', [])))>
                                                        <label class="form-check-label" for="user-permission-create-{{ $permission->id }}">
                                                            <span class="d-block">{{ $permission->name }}</span>
                                                            <span class="small text-secondary">{{ $permission->description ?: 'Droit disponible pour l’institution.' }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @empty
                                            <div class="text-secondary small">Aucun droit disponible. Vérifiez les fonctions attribuées par le super administrateur.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($errors->any() && old('_form') === 'create_user')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createInstitutionUserModal')).show();
            });
        </script>
    @endif
@endsection
