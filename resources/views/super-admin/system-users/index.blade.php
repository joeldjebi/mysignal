@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Utilisateurs internes')
@section('page-title', 'Utilisateurs internes')
@section('page-description', 'Creer les comptes internes, puis leur attribuer roles et permissions pour le pilotage des dossiers et operations.')

@section('header-badges')
    <span class="badge-soft">{{ $systemUsers->total() }} utilisateurs</span>
    <span class="badge-soft">{{ $roles->count() }} roles actifs</span>
    <span class="badge-soft">{{ $permissions->count() }} permissions actives</span>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createSystemUserModal">Nouvel utilisateur</button>
@endsection

@section('content')
    @php
        $permissionGroups = $permissions
            ->groupBy(function ($permission) {
                $segments = explode('_', (string) $permission->code);

                if (($segments[0] ?? null) === 'SA') {
                    return match ($segments[1] ?? null) {
                        'ACCESS' => 'Acces au portail',
                        'DASHBOARD' => 'Dashboard',
                        'PUBLIC' => 'Usagers publics et signalements',
                        'PAYMENTS' => 'Paiements',
                        'INSTITUTION' => 'Admins institutionnels',
                        'SYSTEM' => 'Utilisateurs internes',
                        'ROLES' => 'Roles',
                        'PERMISSIONS' => 'Permissions',
                        'REPARATION' => 'Dossiers contentieux',
                        'APPLICATIONS' => 'Applications',
                        'FEATURES' => 'Fonctionnalites',
                        'SLA' => 'SLA',
                        'ORGANIZATIONS' => 'Organisations',
                        'ORGANIZATION' => 'Types d organisation',
                        'PRICING' => 'Tarification',
                        'COUNTRIES', 'CITIES', 'COMMUNES' => 'Geographie',
                        'BUSINESS' => 'Secteurs',
                        default => 'Autres permissions SA',
                    };
                }

                return 'Autres permissions';
            })
            ->sortKeys();
    @endphp

    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des utilisateurs internes</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, telephone">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Role</label>
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
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.system-users.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $systemUsers->total() }} resultat{{ $systemUsers->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Roles</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($systemUsers as $systemUser)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $systemUser->name }}</div>
                                <div class="small text-secondary">{{ $systemUser->email }}</div>
                                <div class="small text-secondary">{{ $systemUser->phone ?: '-' }}</div>
                            </td>
                            <td><span class="small">{{ $systemUser->roles->pluck('name')->join(', ') ?: '-' }}</span></td>
                            <td><span class="status-chip">{{ $systemUser->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.system-users.show', $systemUser) }}" class="btn btn-sm btn-outline-secondary">Details</a>
                                    <a href="{{ route('super-admin.system-users.edit', $systemUser) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    @if (auth()->user()?->hasPermissionCode('SA_SYSTEM_USERS_TOGGLE_STATUS'))
                                        <form method="POST" action="{{ route('super-admin.system-users.toggle-status', $systemUser) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $systemUser->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('super-admin.system-users.destroy', $systemUser) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary">Aucun utilisateur interne enregistre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $systemUsers->currentPage() }} sur {{ $systemUsers->lastPage() }}</div>
            {{ $systemUsers->links() }}
        </div>
    </section>

    <div class="modal fade" id="createSystemUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold">Nouvel utilisateur interne</h5>
                        <div class="small text-secondary">Creer un compte et attribuer ses roles et permissions.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.system-users.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-light border rounded-4 mb-4">
                            <div class="fw-bold mb-2">Comment attribuer les droits</div>
                            <div class="small text-secondary mb-1">Utilise d abord un ou plusieurs roles pour donner un ensemble coherent de droits.</div>
                            <div class="small text-secondary mb-1">Ajoute ensuite des permissions directes seulement pour completer un besoin precis.</div>
                            <div class="small text-secondary mb-0">Exemple : un huissier peut recevoir un role metier, puis une permission supplementaire de consultation si necessaire.</div>
                        </div>

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
                                @include('partials.phone-field', ['value' => old('phone'), 'placeholder' => '0700000000'])
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Roles</label>
                                <div class="small text-secondary mb-2">Les roles regroupent plusieurs permissions. C est la methode recommandee.</div>
                                <div class="border rounded-3 p-3" style="max-height: 280px; overflow:auto;">
                                    @forelse ($roles as $role)
                                        <div class="border rounded-3 p-2 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $role->id }}" name="role_ids[]" id="create-role-{{ $role->id }}" @checked(in_array($role->id, old('role_ids', [])))>
                                                <label class="form-check-label w-100" for="create-role-{{ $role->id }}">
                                                    <div class="fw-semibold">{{ $role->name }}</div>
                                                    <div class="small text-secondary">{{ $role->code }}</div>
                                                    <div class="small text-secondary">{{ $role->description ?: 'Aucune description renseignee.' }}</div>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-secondary small">Aucun role actif disponible.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Permissions directes</label>
                                <div class="small text-secondary mb-2">A utiliser pour un ajustement fin, permission par permission.</div>
                                <div class="border rounded-3 p-3" style="max-height: 280px; overflow:auto;">
                                    @forelse ($permissionGroups as $groupLabel => $groupPermissions)
                                        <div class="mb-3">
                                            <div class="fw-bold small text-uppercase text-secondary mb-2">{{ $groupLabel }}</div>
                                            <div class="vstack gap-2">
                                                @foreach ($groupPermissions as $permission)
                                                    <div class="border rounded-3 p-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="create-permission-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', [])))>
                                                            <label class="form-check-label w-100" for="create-permission-{{ $permission->id }}">
                                                                <div class="fw-semibold">{{ $permission->name }}</div>
                                                                <div class="small text-secondary">{{ $permission->code }}</div>
                                                                <div class="small text-secondary">{{ $permission->description ?: 'Aucune description renseignee.' }}</div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-secondary small">Aucune permission active disponible.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($errors->any() && old('email'))
        <script>
            const createSystemUserModal = document.getElementById('createSystemUserModal');
            if (createSystemUserModal) {
                bootstrap.Modal.getOrCreateInstance(createSystemUserModal).show();
            }
        </script>
    @endif
@endsection
