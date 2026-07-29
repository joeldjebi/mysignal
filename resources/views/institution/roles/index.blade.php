@extends('institution.layouts.app')

@section('title', config('app.name').' | Rôles')
@section('page-title', 'Rôles')
@section('page-description', 'Créer des rôles locaux et leur affecter les droits autorisés pour l’institution.')

@section('header-badges')
    <span class="badge-soft">{{ $roles->total() }} rôle{{ $roles->total() > 1 ? 's' : '' }}</span>
    @if ($authorization['canManageInstitutionPermissions'])
        <span class="badge-soft">{{ $permissions->count() }} permissions</span>
    @endif
@endsection

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
    @endphp

    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Liste des rôles</div>
                <div class="text-secondary small">Rôles utilisés pour regrouper les droits des collaborateurs.</div>
            </div>
            <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#createInstitutionRoleModal">
                Ajouter
            </button>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom ou description">
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
                    <a href="{{ route('institution.roles.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $roles->total() }} résultat{{ $roles->total() > 1 ? 's' : '' }}</div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
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
                                <td>{{ $role->name }}</td>
                                @if ($authorization['canManageInstitutionPermissions'])
                                    <td><span class="small">{{ $role->permissions->pluck('name')->join(', ') ?: '-' }}</span></td>
                                @endif
                                <td><span class="status-chip">{{ $label::status($role->status) }}</span></td>
                                <td class="text-end">
                                    <div class="report-actions">
                                        <a href="{{ route('institution.roles.edit', $role) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                        <form method="POST" action="{{ route('institution.roles.toggle-status', $role) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $role->status === 'active' ? 'Désactiver' : 'Activer' }}</button>
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
                            <tr><td colspan="{{ 3 + ($authorization['canManageInstitutionPermissions'] ? 1 : 0) }}" class="text-center text-secondary">Aucun rôle local enregistré.</td></tr>
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

    <div class="modal fade" id="createInstitutionRoleModal" tabindex="-1" aria-labelledby="createInstitutionRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('institution.roles.store') }}">
                    @csrf
                    <input type="hidden" name="_form" value="create_role">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createInstitutionRoleModalLabel">Nouveau rôle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Superviseur institutionnel" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>
                            @if ($authorization['canManageInstitutionPermissions'])
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                        <label class="form-label mb-0">Permissions</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-dark" data-check-all-permissions>Tout cocher</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-uncheck-all-permissions>Tout décocher</button>
                                        </div>
                                    </div>
                                    <div class="border rounded-3 p-3" style="max-height: 460px; overflow:auto;">
                                        @forelse ($groupedPermissions as $groupLabel => $groupPermissions)
                                            <div class="mb-3" data-permission-group>
                                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                    <div class="small text-uppercase fw-bold text-secondary">{{ $groupLabel }}</div>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-check-group-permissions>Cocher le groupe</button>
                                                </div>
                                                <div class="row g-2">
                                                    @foreach ($groupPermissions as $permission)
                                                        <div class="col-md-6">
                                                            <div class="border rounded-3 p-2 h-100">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="role-permission-create-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', [])))>
                                                                    <label class="form-check-label w-100" for="role-permission-create-{{ $permission->id }}">
                                                                        <div class="fw-semibold">{{ $permission->name }}</div>
                                                                        <div class="small text-secondary">{{ $permission->description ?: 'Droit disponible pour l’institution.' }}</div>
                                                                    </label>
                                                                </div>
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

            @if ($errors->any() && old('_form') === 'create_role')
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createInstitutionRoleModal')).show();
            @endif
        })();
    </script>
@endsection
