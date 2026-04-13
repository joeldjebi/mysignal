@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un role')
@section('page-title', 'Modifier un role')
@section('page-description', 'Mettre a jour un role et ses permissions associees.')

@section('content')
    @php
        $selectedPermissionIds = collect(old('permission_ids', $assignedPermissionIds ?? []))
            ->all();
        $groupedPermissions = $permissions
            ->groupBy(function ($permission) {
                $segments = explode('_', (string) $permission->code);

                if (($segments[0] ?? null) === 'SA') {
                    return match ($segments[1] ?? null) {
                        'ACCESS' => 'Acces au portail',
                        'DASHBOARD' => 'Dashboard',
                        'PUBLIC' => 'Usagers publics et signalements',
                        'PAYMENTS' => 'Paiements',
                        'ACTIVITY' => 'Journaux d activite',
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
        <div class="fw-bold mb-3">Edition de {{ $role->name }}</div>
        <form
            method="POST"
            action="{{ route('super-admin.roles.update', $role) }}"
            class="row g-3"
            data-role-permission-form
            data-selected-permission-ids='@json($selectedPermissionIds)'
        >
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $role->code) }}" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <label class="form-label mb-0">Permissions</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark" data-check-all-permissions>Tout cocher</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-uncheck-all-permissions>Tout decocher</button>
                    </div>
                </div>
                <div class="small text-secondary mb-2">Ces permissions seront heritees automatiquement par chaque utilisateur auquel ce role est attribue.</div>
                @if (empty($assignedPermissionIds))
                    <div class="alert alert-warning border rounded-4 py-2 px-3 mb-3">
                        <div class="small mb-0">Ce role n a actuellement aucune permission liee. Les utilisateurs qui portent seulement ce role n heritent donc d aucun droit tant que tu ne coches pas des permissions ici.</div>
                    </div>
                @endif
                <div class="border rounded-3 p-3" style="max-height: 420px; overflow:auto;">
                    @foreach ($groupedPermissions as $groupLabel => $groupPermissions)
                        <div class="mb-3" data-permission-group>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <div class="small text-uppercase fw-bold text-secondary">{{ $groupLabel }}</div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-check-group-permissions>Cocher le groupe</button>
                            </div>
                            <div class="vstack gap-2">
                                @foreach ($groupPermissions as $permission)
                                    <div class="border rounded-3 p-2">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                value="{{ $permission->id }}"
                                                name="permission_ids[]"
                                                id="permission-edit-{{ $permission->id }}"
                                                @checked(in_array($permission->id, $selectedPermissionIds))
                                            >
                                            <label class="form-check-label w-100" for="permission-edit-{{ $permission->id }}">
                                                <div class="fw-semibold">{{ $permission->name }}</div>
                                                <div class="small text-secondary">{{ $permission->code }}</div>
                                                <div class="small text-secondary">{{ $permission->description ?: 'Aucune description renseignee.' }}</div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.roles.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection

@section('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-role-permission-form]');

            if (!form) {
                return;
            }

            const selectedPermissionIds = JSON.parse(form.dataset.selectedPermissionIds || '[]')
                .map((permissionId) => String(permissionId));

            form.querySelectorAll('input[name="permission_ids[]"]').forEach((checkbox) => {
                checkbox.checked = selectedPermissionIds.includes(String(checkbox.value));
            });

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
        })();
    </script>
@endsection
