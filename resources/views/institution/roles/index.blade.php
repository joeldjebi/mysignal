@extends('institution.layouts.app')

@section('title', config('app.name').' | Roles')
@section('page-title', 'Roles')
@section('page-description', 'Creer des roles locaux et leur affecter les permissions autorisees pour l institution.')

@section('header-badges')
    <span class="badge-soft">{{ $roles->total() }} roles</span>
    @if ($authorization['canManageInstitutionPermissions'])
        <span class="badge-soft">{{ $permissions->count() }} permissions</span>
    @endif
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card">
                <div class="fw-bold mb-3">Nouveau role</div>
                <form method="POST" action="{{ route('institution.roles.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control" placeholder="SUPERVISEUR" required>
                    </div>
                    <div>
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Superviseur institutionnel" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    @if ($authorization['canManageInstitutionPermissions'])
                        <div>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <label class="form-label mb-0">Permissions</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-dark" data-check-all-permissions>Tout cocher</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-uncheck-all-permissions>Tout decocher</button>
                                </div>
                            </div>
                            <div class="small text-secondary mb-2">Choisis les permissions qui seront heritees automatiquement par les utilisateurs de ce role local.</div>
                            <div class="border rounded-3 p-3" style="max-height: 420px; overflow:auto;">
                                @forelse ($groupedPermissions as $groupLabel => $groupPermissions)
                                    <div class="mb-3" data-permission-group>
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                            <div class="small text-uppercase fw-bold text-secondary">{{ $groupLabel }}</div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-check-group-permissions>Cocher le groupe</button>
                                        </div>
                                        <div class="vstack gap-2">
                                            @foreach ($groupPermissions as $permission)
                                                <div class="border rounded-3 p-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="role-permission-create-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', [])))>
                                                        <label class="form-check-label w-100" for="role-permission-create-{{ $permission->id }}">
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
                                    <div class="text-secondary small">Aucune permission disponible.</div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-dark">Creer</button>
                </form>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Liste des roles</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, nom, description">
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
                            <a href="{{ route('institution.roles.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>
                <div class="table-toolbar">
                    <div class="table-meta">{{ $roles->total() }} resultat{{ $roles->total() > 1 ? 's' : '' }}</div>
                </div>
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    @if ($authorization['canManageInstitutionPermissions'])
                                        <th>Permissions</th>
                                    @endif
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>{{ $role->code }}</td>
                                        <td>{{ $role->name }}</td>
                                        @if ($authorization['canManageInstitutionPermissions'])
                                            <td><span class="small">{{ $role->permissions->pluck('name')->join(', ') ?: '-' }}</span></td>
                                        @endif
                                        <td><span class="status-chip">{{ $role->status }}</span></td>
                                        <td class="text-end">
                                            <div class="report-actions">
                                                <a href="{{ route('institution.roles.edit', $role) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                                <form method="POST" action="{{ route('institution.roles.toggle-status', $role) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-outline-warning">{{ $role->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('institution.roles.destroy', $role) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ 4 + ($authorization['canManageInstitutionPermissions'] ? 1 : 0) }}" class="text-center text-secondary">Aucun role local enregistre.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $roles->currentPage() }} sur {{ $roles->lastPage() }}</div>
                    {{ $roles->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            document.querySelectorAll('form').forEach((form) => {
                if (!form.querySelector('input[name="permission_ids[]"]')) {
                    return;
                }

                const permissionCheckboxes = () => Array.from(form.querySelectorAll('input[name="permission_ids[]"]'));

                form.querySelector('[data-check-all-permissions]')?.addEventListener('click', () => {
                    permissionCheckboxes().forEach((checkbox) => {
                        checkbox.checked = true;
                    });
                });

                form.querySelector('[data-uncheck-all-permissions]')?.addEventListener('click', () => {
                    permissionCheckboxes().forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                });

                form.querySelectorAll('[data-permission-group]').forEach((group) => {
                    group.querySelector('[data-check-group-permissions]')?.addEventListener('click', () => {
                        group.querySelectorAll('input[name="permission_ids[]"]').forEach((checkbox) => {
                            checkbox.checked = true;
                        });
                    });
                });
            });
        })();
    </script>
@endsection
