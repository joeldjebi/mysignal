@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Details '.$systemUser->name)
@section('page-title', 'Details utilisateur interne')
@section('page-description', 'Consulter le profil, les roles et les permissions heritees de cet utilisateur interne.')

@section('header-badges')
    <span class="badge-soft">{{ $systemUser->status === 'active' ? 'Compte actif' : 'Compte inactif' }}</span>
    <span class="badge-soft">{{ $systemUser->roles->count() }} role{{ $systemUser->roles->count() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    @php
        $portalLabels = [
            'backoffice' => 'Backoffice',
            'super_admin' => 'Super admin',
            'institution' => 'Institution',
            'partner' => 'Partenaire',
            'huissier' => 'Huissier',
            'avocat' => 'Avocat',
        ];
        $portalOrganizationHelp = 'Institution et partenaire exigent une organisation. Backoffice, SA, huissier et avocat restent sans organisation.';
        $filterableProfileScopes = collect($permissionProfileScopes)->only(['all', 'super_admin', 'backoffice', 'institution', 'partner', 'huissier', 'avocat']);
    @endphp

    <section class="panel-card mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3">
            <div>
                <div class="fw-bold mb-2">{{ $systemUser->name }}</div>
                <div class="text-secondary">{{ $systemUser->email }}</div>
                <div class="text-secondary">{{ $systemUser->phone ?: 'Telephone non renseigne' }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if ($systemUser->organization_id === null)
                    <a href="{{ route('super-admin.system-users.edit', $systemUser) }}" class="btn btn-dark">Modifier</a>
                    <a href="{{ route('super-admin.system-users.index') }}" class="btn btn-outline-secondary">Retour</a>
                @else
                    <a href="{{ route('super-admin.institution-admins.edit', $systemUser) }}" class="btn btn-dark">Modifier le compte</a>
                    <a href="{{ route('super-admin.institution-admins.index') }}" class="btn btn-outline-secondary">Retour</a>
                @endif
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-12">
            <section class="panel-card">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                    <div>
                        <div class="fw-bold">Profils d acces</div>
                        <div class="small text-secondary">Chaque profil donne acces a un portail precis avec ses propres roles et permissions.</div>
                    </div>
                    <span class="badge-soft">{{ $systemUser->accesses->count() }} profil{{ $systemUser->accesses->count() > 1 ? 's' : '' }}</span>
                </div>

                <form method="POST" action="{{ route('super-admin.system-users.accesses.store', $systemUser) }}" class="border rounded-3 p-3 mb-4">
                    @csrf
                    <div class="fw-semibold mb-3">Ajouter un profil</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Portail</label>
                            <select name="portal" class="form-select" required>
                                @foreach ($portalLabels as $portal => $label)
                                    <option value="{{ $portal }}" @selected(old('portal') === $portal)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Organisation</label>
                            <select name="organization_id" class="form-select">
                                <option value="">Aucune organisation</option>
                                @foreach ($accessOrganizations as $organization)
                                    <option value="{{ $organization->id }}" @selected((string) old('organization_id') === (string) $organization->id)>
                                        {{ $organization->name }} - {{ $organization->organizationType?->name ?: 'Type non renseigne' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="small text-secondary mt-1">{{ $portalOrganizationHelp }}</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select" required>
                                <option value="active" @selected(old('status', 'active') === 'active')>Actif</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-dark w-100">Ajouter</button>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Roles du profil</label>
                            <div class="border rounded-3 p-3" style="max-height: 220px; overflow:auto;">
                                @foreach ($accessRoles as $role)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}" id="new-access-role-{{ $role->id }}" @checked(in_array($role->id, old('role_ids', [])))>
                                        <label class="form-check-label" for="new-access-role-{{ $role->id }}">
                                            <span class="fw-semibold">{{ $role->name }}</span>
                                            <span class="small text-secondary d-block">{{ $role->code }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Permissions directes du profil</label>
                            <div class="row g-2 mb-2" data-permission-filters>
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" data-permission-profile-filter>
                                        @foreach ($filterableProfileScopes as $scope => $label)
                                            <option value="{{ $scope }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" data-permission-category-filter>
                                        <option value="">Toutes les categories</option>
                                        @foreach ($permissionCategories as $category => $label)
                                            <option value="{{ $category }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="search" class="form-control form-control-sm" placeholder="Rechercher" data-permission-search-filter>
                                </div>
                            </div>
                            <div class="border rounded-3 p-3" style="max-height: 220px; overflow:auto;" data-permission-list>
                                @foreach ($accessPermissions as $permission)
                                    <div class="form-check mb-2" data-permission-row data-profile="{{ $permission->profile_scope ?: 'all' }}" data-category="{{ $permission->category ?: 'other' }}" data-search="{{ Str::lower($permission->name.' '.$permission->code.' '.$permission->description) }}">
                                        <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" id="new-access-permission-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', [])))>
                                        <label class="form-check-label" for="new-access-permission-{{ $permission->id }}">
                                            <span class="fw-semibold">{{ $permission->name }}</span>
                                            <span class="small text-secondary d-block">{{ $permission->code }} - {{ $permission->profileScopeLabel() }} - {{ $permission->categoryLabel() }}</span>
                                        </label>
                                    </div>
                                @endforeach
                                <div class="text-secondary small d-none" data-permission-empty>Aucune permission ne correspond aux filtres.</div>
                            </div>
                        </div>
                    </div>
                </form>

                @forelse ($systemUser->accesses->sortBy('portal') as $access)
                    @php
                        $accessRoleIds = $access->roles->pluck('id')->all();
                        $accessPermissionIds = $access->permissions->pluck('id')->all();
                        $effectivePermissionCodes = $access->permissionCodes();
                    @endphp
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                            <div>
                                <div class="fw-semibold">{{ $portalLabels[$access->portal] ?? $access->portal }}</div>
                                <div class="small text-secondary">
                                    {{ $access->organization?->name ?: 'Sans organisation' }}
                                    @if ($access->organization?->organizationType)
                                        - {{ $access->organization->organizationType->name }}
                                    @endif
                                </div>
                                <div class="small text-secondary">{{ $effectivePermissionCodes->count() }} permission{{ $effectivePermissionCodes->count() > 1 ? 's' : '' }} effective{{ $effectivePermissionCodes->count() > 1 ? 's' : '' }}</div>
                            </div>
                            <div class="d-flex gap-2 align-items-start flex-wrap">
                                <span class="status-chip">{{ $access->status }}</span>
                                <form method="POST" action="{{ route('super-admin.system-users.accesses.destroy', [$systemUser, $access]) }}" onsubmit="return confirm('Supprimer ce profil d acces ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('super-admin.system-users.accesses.update', [$systemUser, $access]) }}" class="row g-3">
                            @csrf
                            @method('PUT')
                            <div class="col-md-3">
                                <label class="form-label">Portail</label>
                                <select name="portal" class="form-select" required>
                                    @foreach ($portalLabels as $portal => $label)
                                        <option value="{{ $portal }}" @selected($access->portal === $portal)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Organisation</label>
                                <select name="organization_id" class="form-select">
                                    <option value="">Aucune organisation</option>
                                    @foreach ($accessOrganizations as $organization)
                                        <option value="{{ $organization->id }}" @selected((int) $access->organization_id === (int) $organization->id)>
                                            {{ $organization->name }} - {{ $organization->organizationType?->name ?: 'Type non renseigne' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select" required>
                                    <option value="active" @selected($access->status === 'active')>Actif</option>
                                    <option value="inactive" @selected($access->status === 'inactive')>Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-outline-dark w-100">Enregistrer</button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Roles du profil</label>
                                <div class="border rounded-3 p-3" style="max-height: 220px; overflow:auto;">
                                    @foreach ($accessRoles as $role)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}" id="access-{{ $access->id }}-role-{{ $role->id }}" @checked(in_array($role->id, $accessRoleIds))>
                                            <label class="form-check-label" for="access-{{ $access->id }}-role-{{ $role->id }}">
                                                <span class="fw-semibold">{{ $role->name }}</span>
                                                <span class="small text-secondary d-block">{{ $role->code }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Permissions directes du profil</label>
                                <div class="row g-2 mb-2" data-permission-filters>
                                    <div class="col-md-4">
                                        <select class="form-select form-select-sm" data-permission-profile-filter>
                                            @foreach ($filterableProfileScopes as $scope => $label)
                                                <option value="{{ $scope }}" @selected($access->portal === $scope)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select form-select-sm" data-permission-category-filter>
                                            <option value="">Toutes les categories</option>
                                            @foreach ($permissionCategories as $category => $label)
                                                <option value="{{ $category }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="search" class="form-control form-control-sm" placeholder="Rechercher" data-permission-search-filter>
                                    </div>
                                </div>
                                <div class="border rounded-3 p-3" style="max-height: 220px; overflow:auto;" data-permission-list>
                                    @foreach ($accessPermissions as $permission)
                                        <div class="form-check mb-2" data-permission-row data-profile="{{ $permission->profile_scope ?: 'all' }}" data-category="{{ $permission->category ?: 'other' }}" data-search="{{ Str::lower($permission->name.' '.$permission->code.' '.$permission->description) }}">
                                            <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" id="access-{{ $access->id }}-permission-{{ $permission->id }}" @checked(in_array($permission->id, $accessPermissionIds))>
                                            <label class="form-check-label" for="access-{{ $access->id }}-permission-{{ $permission->id }}">
                                                <span class="fw-semibold">{{ $permission->name }}</span>
                                                <span class="small text-secondary d-block">{{ $permission->code }} - {{ $permission->profileScopeLabel() }} - {{ $permission->categoryLabel() }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                    <div class="text-secondary small d-none" data-permission-empty>Aucune permission ne correspond aux filtres.</div>
                                </div>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="text-secondary">Aucun profil d acces specifique n est encore configure pour cet utilisateur.</div>
                @endforelse
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Informations du compte</div>
                <div class="small text-secondary text-uppercase fw-semibold mb-1">Statut</div>
                <div class="mb-3"><span class="status-chip">{{ $systemUser->status }}</span></div>

                <div class="small text-secondary text-uppercase fw-semibold mb-1">Cree par</div>
                <div class="mb-3">{{ $systemUser->creator?->name ?: 'Non renseigne' }}</div>

                <div class="small text-secondary text-uppercase fw-semibold mb-1">Date de creation</div>
                <div class="mb-3">{{ $systemUser->created_at?->format('d/m/Y H:i') ?: '-' }}</div>

                <div class="small text-secondary text-uppercase fw-semibold mb-1">Derniere mise a jour</div>
                <div>{{ $systemUser->updated_at?->format('d/m/Y H:i') ?: '-' }}</div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Roles attribues</div>
                @forelse ($systemUser->roles as $role)
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="fw-semibold">{{ $role->name }}</div>
                        <div class="small text-secondary">{{ $role->code }}</div>
                        <div class="small text-secondary mt-1">{{ $role->description ?: 'Aucune description renseignee.' }}</div>
                        <div class="small text-secondary mt-2">{{ $role->permissions->count() }} permission{{ $role->permissions->count() > 1 ? 's' : '' }} via ce role</div>
                    </div>
                @empty
                    <div class="text-secondary">Aucun role attribue.</div>
                @endforelse
            </section>
        </div>

        <div class="col-lg-12">
            <section class="panel-card">
                <div class="fw-bold mb-3">Permissions heritees des roles</div>
                @php
                    $inheritedPermissions = $systemUser->roles
                        ->flatMap(fn ($role) => $role->permissions)
                        ->unique('id')
                        ->sortBy('name')
                        ->values();
                @endphp
                @if ($inheritedPermissions->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($inheritedPermissions as $permission)
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-semibold">{{ $permission->name }}</div>
                                    <div class="small text-secondary">{{ $permission->code }}</div>
                                    <div class="small text-secondary mt-1">{{ $permission->description ?: 'Aucune description renseignee.' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-secondary">Aucune permission heritee. Il faut attribuer au moins un role portant des permissions.</div>
                @endif
            </section>
        </div>
        <div class="col-lg-12">
            <section class="panel-card">
                <div class="fw-bold mb-3">Visibilite sur les activites des utilisateurs internes</div>
                @if ($systemUser->activityLogVisibleUsers->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($systemUser->activityLogVisibleUsers as $visibleUser)
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-semibold">{{ $visibleUser->name }}</div>
                                    <div class="small text-secondary">{{ $visibleUser->email }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-secondary">Aucun utilisateur interne specifique n est autorise pour ce compte.</div>
                @endif
            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const compatibleProfiles = {
                all: ['all'],
                super_admin: ['all', 'super_admin', 'backoffice'],
                backoffice: ['all', 'backoffice', 'huissier', 'avocat'],
                institution: ['all', 'institution'],
                partner: ['all', 'partner'],
                huissier: ['all', 'huissier', 'backoffice'],
                avocat: ['all', 'avocat', 'backoffice'],
            };

            function normalize(value) {
                return String(value || '').trim().toLowerCase();
            }

            function syncPermissionFilters(form) {
                const profileFilter = form.querySelector('[data-permission-profile-filter]');
                const categoryFilter = form.querySelector('[data-permission-category-filter]');
                const searchFilter = form.querySelector('[data-permission-search-filter]');
                const portalSelect = form.querySelector('select[name="portal"]');
                const rows = Array.from(form.querySelectorAll('[data-permission-row]'));
                const emptyState = form.querySelector('[data-permission-empty]');

                if (!profileFilter || !categoryFilter || !searchFilter || !rows.length) {
                    return;
                }

                const syncProfileFromPortal = () => {
                    const portal = normalize(portalSelect?.value);

                    if (portal && Array.from(profileFilter.options).some((option) => option.value === portal)) {
                        profileFilter.value = portal;
                    }
                };

                const applyFilters = () => {
                    const selectedProfile = normalize(profileFilter.value);
                    const selectedCategory = normalize(categoryFilter.value);
                    const search = normalize(searchFilter.value);
                    const acceptedProfiles = compatibleProfiles[selectedProfile] || ['all', selectedProfile];
                    let visibleCount = 0;

                    rows.forEach((row) => {
                        const rowProfile = normalize(row.dataset.profile);
                        const rowCategory = normalize(row.dataset.category);
                        const rowSearch = normalize(row.dataset.search);
                        const isProfileMatch = acceptedProfiles.includes(rowProfile);
                        const isCategoryMatch = !selectedCategory || rowCategory === selectedCategory;
                        const isSearchMatch = !search || rowSearch.includes(search);
                        const isVisible = isProfileMatch && isCategoryMatch && isSearchMatch;

                        row.classList.toggle('d-none', !isVisible);
                        visibleCount += isVisible ? 1 : 0;
                    });

                    emptyState?.classList.toggle('d-none', visibleCount > 0);
                };

                syncProfileFromPortal();
                applyFilters();

                portalSelect?.addEventListener('change', () => {
                    syncProfileFromPortal();
                    applyFilters();
                });
                profileFilter.addEventListener('change', applyFilters);
                categoryFilter.addEventListener('change', applyFilters);
                searchFilter.addEventListener('input', applyFilters);
            }

            document.querySelectorAll('form').forEach(syncPermissionFilters);
        })();
    </script>
@endsection
