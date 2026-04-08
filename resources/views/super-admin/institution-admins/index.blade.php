@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Admins institutionnels')
@section('page-title', 'Admins institutionnels')
@section('page-description', 'Creer les comptes admins racine qui gereront ensuite leurs propres users, roles et permissions dans leur portail.')

@section('header-badges')
    <span class="badge-soft">{{ $admins->total() }} admins</span>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createInstitutionAdminModal">
        Creer un nouvel institution
    </button>
@endsection

@section('content')
    <style>
        .feature-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }
        .feature-inline-list {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
        }
        .feature-inline-chip {
            display: inline-flex;
            align-items: center;
            padding: .25rem .55rem;
            border-radius: 999px;
            background: rgba(16, 24, 40, 0.06);
            color: #344054;
            font-size: .78rem;
            line-height: 1.2;
        }
        .feature-more-toggle {
            border: 0;
            background: transparent;
            color: #0f5b8d;
            font-size: .78rem;
            font-weight: 600;
            padding: 0;
        }
        .feature-more-toggle:hover {
            text-decoration: underline;
        }
        @media (max-width: 1199.98px) {
            .feature-grid-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767.98px) {
            .feature-grid-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des admins institutionnels</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, telephone">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Organisation</label>
                    <select name="organization_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
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
            </div>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-dark">Filtrer</button>
                <a href="{{ route('super-admin.institution-admins.index') }}" class="btn btn-outline-secondary">RAZ</a>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $admins->total() }} resultat{{ $admins->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Organisation</th>
                        <th>Fonctionnalites</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
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
                            <td>{{ $admin->organization?->name ?: '-' }}</td>
                            <td>
                                @php
                                    $effectiveFeatures = $admin->features->isNotEmpty()
                                        ? $admin->features->sortBy('name')->values()
                                        : collect($admin->organization?->resolvedFeatures() ?? [])->sortBy('name')->values();
                                    $visibleFeatureNames = $effectiveFeatures
                                        ->pluck('name')
                                        ->values();
                                    $previewFeatureNames = $visibleFeatureNames->take(3);
                                    $remainingFeatureNames = $visibleFeatureNames->slice(3)->values();
                                @endphp
                                @if ($visibleFeatureNames->isEmpty())
                                    <span class="small text-secondary">-</span>
                                @else
                                    <div class="feature-inline-list">
                                        @foreach ($previewFeatureNames as $featureName)
                                            <span class="feature-inline-chip">{{ $featureName }}</span>
                                        @endforeach
                                        @if ($remainingFeatureNames->isNotEmpty())
                                            <details class="d-inline-block">
                                                <summary class="feature-more-toggle">Voir plus ({{ $remainingFeatureNames->count() }})</summary>
                                                <div class="feature-inline-list mt-2">
                                                    @foreach ($remainingFeatureNames as $featureName)
                                                        <span class="feature-inline-chip">{{ $featureName }}</span>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td><span class="status-chip">{{ $admin->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.institution-admins.edit', $admin) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.institution-admins.toggle-status', $admin) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ $admin->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.institution-admins.destroy', $admin) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary">Aucun admin institutionnel enregistre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $admins->currentPage() }} sur {{ $admins->lastPage() }}</div>
            {{ $admins->links() }}
        </div>
    </section>

    <div class="modal fade" id="createInstitutionAdminModal" tabindex="-1" aria-labelledby="createInstitutionAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="createInstitutionAdminModalLabel">Nouvel admin institutionnel</h5>
                        <div class="small text-secondary">Creer le compte admin racine d'un portail institutionnel.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.institution-admins.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Organisation</label>
                                <select name="organization_id" class="form-select" id="institutionAdminOrganizationSelect" required>
                                    <option value="">Selectionner</option>
                                    @foreach ($organizations as $organization)
                                        <option value="{{ $organization->id }}" data-feature-ids="{{ implode(',', $organization->resolvedFeatureIds()) }}" @selected(old('organization_id') == $organization->id)>{{ $organization->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom complet</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                @include('partials.phone-field', ['value' => old('phone'), 'placeholder' => '0700000000'])
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Fonctionnalites attribuees</label>
                                <div class="small text-secondary mb-2">Les fonctionnalites ouvertes pour l'organisation sont preselectionnees automatiquement. Vous pouvez ensuite limiter l'AI a un sous-ensemble.</div>
                                <div class="border rounded-3 p-3" style="max-height: 260px; overflow:auto;">
                                    <div class="feature-grid-3">
                                        @forelse ($features as $feature)
                                            <div>
                                                <div class="form-check">
                                                    @php
                                                        $oldOrganization = $organizations->firstWhere('id', (int) old('organization_id'));
                                                        $defaultFeatureIds = $oldOrganization?->resolvedFeatureIds() ?? [];
                                                    @endphp
                                                    <input class="form-check-input" type="checkbox" value="{{ $feature->id }}" name="feature_ids[]" id="feature-create-{{ $feature->id }}" @checked(in_array($feature->id, old('feature_ids', $defaultFeatureIds)))>
                                                    <label class="form-check-label" for="feature-create-{{ $feature->id }}">
                                                        <span class="d-block">{{ $feature->name }}</span>
                                                        <span class="small text-muted d-block">{{ $feature->code }}</span>
                                                        @if ($feature->description)
                                                            <span class="small text-secondary">{{ $feature->description }}</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-secondary small">Aucune fonctionnalite active.</div>
                                        @endforelse
                                    </div>
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
    @if ($errors->any() && old('name'))
        <script>
            const createInstitutionAdminModal = document.getElementById('createInstitutionAdminModal');

            if (createInstitutionAdminModal) {
                bootstrap.Modal.getOrCreateInstance(createInstitutionAdminModal).show();
            }
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const organizationSelect = document.getElementById('institutionAdminOrganizationSelect');

            if (!organizationSelect) {
                return;
            }

                const applyOrganizationDefaults = () => {
                    const selectedOption = organizationSelect.options[organizationSelect.selectedIndex];
                    const featureIds = (selectedOption?.dataset.featureIds || '')
                    .split(',')
                    .map((value) => value.trim())
                    .filter(Boolean);
                const featureInputs = document.querySelectorAll('#createInstitutionAdminModal input[name="feature_ids[]"]');

                featureInputs.forEach((input) => {
                    input.checked = featureIds.includes(input.value);
                });
            };

            organizationSelect.addEventListener('change', applyOrganizationDefaults);
        });
    </script>
@endsection
