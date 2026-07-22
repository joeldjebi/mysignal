@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Institutions')
@section('page-title', 'Institution')
@section('page-description', 'Créer les institutions et portails qui disposeront de leur propre administration locale.')

@section('header-badges')
    <span class="badge-soft">{{ $organizations->total() }} institutions</span>
    <form method="POST" action="{{ route('super-admin.organizations.clear') }}" onsubmit="return confirm('Vider toutes les institutions ? Cette action est irreversible.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger" @disabled($organizations->total() === 0)>Vider les institutions</button>
    </form>
    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importOrganizationsModal">
        Importer CSV
    </button>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createOrganizationModal">
        Creer une institution
    </button>
@endsection

@section('content')
    <style>
        .org-code {
            display: inline-flex;
            border-radius: 999px;
            background: rgba(25,75,112,.08);
            color: var(--acepen-blue);
            font-weight: 700;
            font-size: .74rem;
            padding: .3rem .6rem;
        }
        .feature-stack {
            display: grid;
            gap: .7rem;
        }
        .feature-group-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 18px;
            padding: .8rem;
            background: rgba(255,255,255,.86);
        }
        .feature-summary-line {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            align-items: center;
        }
        .feature-summary-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .32rem .62rem;
            background: rgba(25,75,112,.08);
            color: var(--acepen-blue);
            font-size: .76rem;
            font-weight: 700;
        }
        .feature-details-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(16,42,67,.12);
            border-radius: 999px;
            padding: .32rem .75rem;
            background: #fff;
            color: #16354a;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
            list-style: none;
        }
        .feature-details-toggle::-webkit-details-marker {
            display: none;
        }
        .feature-details-panel {
            margin-top: .8rem;
        }
        .feature-chip-grid {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: .5rem;
        }
        .feature-mini-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .35rem .65rem;
            background: rgba(196,155,72,.12);
            color: #7a5c1d;
            font-size: .76rem;
            font-weight: 700;
        }
        .org-identity {
            display: grid;
            gap: .2rem;
        }
        .org-meta-line {
            display: grid;
            gap: .15rem;
        }
        .org-meta-line .small {
            line-height: 1.25;
        }
        .feature-picker {
            display: grid;
            gap: 1rem;
            max-height: 52vh;
            overflow: auto;
            padding-right: .25rem;
        }
        .feature-picker-group {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 20px;
            background: rgba(248,250,252,.9);
            padding: .9rem;
        }
        .feature-picker-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }
        .feature-option {
            display: block;
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 18px;
            background: #fff;
            padding: .9rem;
            height: 100%;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }
        .feature-option:hover {
            transform: translateY(-1px);
            border-color: rgba(25,75,112,.22);
            box-shadow: 0 12px 24px rgba(16,42,67,.08);
        }
        .feature-option .form-check {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            margin: 0;
        }
        .feature-option .form-check-input {
            margin-top: .2rem;
        }
        .feature-option-title {
            font-weight: 700;
            line-height: 1.3;
        }
        .feature-option-code {
            color: var(--acepen-blue);
            font-size: .73rem;
            font-weight: 800;
            letter-spacing: .03em;
            margin-top: .15rem;
        }
        .required-star {
            color: #dc3545;
            font-weight: 800;
            margin-left: .2rem;
        }
        @media (max-width: 767.98px) {
            .feature-picker-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 1199.98px) {
            .feature-picker-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <section class="panel-card mb-4">
        <div class="fw-bold mb-3">Liste des institutions</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, code, portail, email">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Catégorie</label>
                    <select name="application_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($applications as $application)
                            <option value="{{ $application->id }}" @selected((string) request('application_id') === (string) $application->id)>{{ $application->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Sous Catégorie</label>
                    <select name="organization_type_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($organizationTypes as $organizationType)
                            <option value="{{ $organizationType->id }}" @selected((string) request('organization_type_id') === (string) $organizationType->id)>{{ $organizationType->name }}</option>
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
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.organizations.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $organizations->total() }} resultat{{ $organizations->total() > 1 ? 's' : '' }}</div>
        </div>

        @if ($organizations->isEmpty())
            <div class="text-center text-secondary py-5">Aucune institution enregistrée.</div>
        @else
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Institution</th>
                                <th>Catégorie</th>
                                <th>Type</th>
                                <th>Portail</th>
                                <th>Fonctionnalites</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($organizations as $organization)
                                @php
                                    $organizationFeatureGroups = $organization->resolvedFeatures()->groupBy(function ($feature) {
                                        return match (true) {
                                            str_starts_with($feature->code, 'INSTITUTION_DASHBOARD_') => 'Dashboard',
                                            str_starts_with($feature->code, 'INSTITUTION_') => 'Acces institutionnels',
                                            str_starts_with($feature->code, 'PUBLIC_') => 'Modules publics',
                                            default => 'Autres',
                                        };
                                    });
                                    $allOrganizationFeatures = $organization->resolvedFeatures()->pluck('name')->values();
                                    $previewOrganizationFeatures = $allOrganizationFeatures->take(3);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="org-identity">
                                            <div class="org-code">{{ $organization->code }}</div>
                                            <div class="fw-bold">{{ $organization->name }}</div>
                                            <div class="small text-secondary">{{ $organization->email ?: 'Aucun email renseigne' }}</div>
                                            <div class="small text-secondary">{{ collect([$organization->commune, $organization->address])->filter()->implode(' - ') ?: 'Localisation non renseignee' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="org-meta-line">
                                            <div class="fw-semibold">{{ $organization->application?->name ?: '-' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="org-meta-line">
                                            <div class="fw-semibold">{{ $organization->organizationType?->name ?: '-' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="org-meta-line">
                                            <div class="fw-semibold">{{ $organization->portal_key ?: '-' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($organizationFeatureGroups->isEmpty())
                                            <span class="small text-secondary">Aucune fonctionnalite attribuee.</span>
                                        @else
                                            <div class="feature-summary-line">
                                                @foreach ($previewOrganizationFeatures as $featureName)
                                                    <span class="feature-summary-chip">{{ $featureName }}</span>
                                                @endforeach
                                                @if ($allOrganizationFeatures->count() > 3)
                                                    <span class="feature-summary-chip">+{{ $allOrganizationFeatures->count() - 3 }}</span>
                                                @endif
                                                <details>
                                                    <summary class="feature-details-toggle">Details des fonctionnalites</summary>
                                                    <div class="feature-details-panel feature-stack">
                                                        @foreach ($organizationFeatureGroups as $groupLabel => $features)
                                                            <div class="feature-group-card">
                                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                                    <div class="fw-semibold">{{ $groupLabel }}</div>
                                                                    <div class="small text-secondary">{{ $features->count() }}</div>
                                                                </div>
                                                                <div class="feature-chip-grid">
                                                                    @foreach ($features as $feature)
                                                                        <span class="feature-mini-chip">{{ $feature->name }}</span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            </div>
                                            <div class="small text-secondary mt-2">{{ $allOrganizationFeatures->count() }} fonctionnalite(s) active(s)</div>
                                        @endif
                                    </td>
                                    <td><span class="status-chip">{{ $organization->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap justify-content-end">
                                            <a href="{{ route('super-admin.organizations.show', $organization) }}" class="btn btn-sm btn-dark">Details</a>
                                            <a href="{{ route('super-admin.organizations.edit', $organization) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.organizations.toggle-status', $organization) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $organization->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.organizations.destroy', $organization) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $organizations->currentPage() }} sur {{ $organizations->lastPage() }}</div>
            {{ $organizations->links() }}
        </div>
    </section>

    <div class="modal fade" id="importOrganizationsModal" tabindex="-1" aria-labelledby="importOrganizationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="importOrganizationsModalLabel">Importer des institutions</h5>
                        <div class="small text-secondary">CSV ou XLSX attendu : Nom, Commune, Adresse, Mobile ou Type_organisation, Nom, Commune, Region_District.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.organizations.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="import_form" value="1">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Catégorie<span class="required-star">*</span></label>
                                <select name="application_id" class="form-select" required>
                                    <option value="">Selectionner</option>
                                    @foreach ($applications as $application)
                                        <option value="{{ $application->id }}" @selected(old('application_id') == $application->id)>{{ $application->name }}</option>
                                    @endforeach
                                </select>
                                <div class="small text-secondary mt-2">Les fonctionnalites de cette catégorie seront liees aux institutions importees.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sous Catégorie</label>
                                <select name="organization_type_id" class="form-select">
                                    <option value="">Selectionner une sous catégorie existante</option>
                                    @foreach ($organizationTypes as $organizationType)
                                        <option value="{{ $organizationType->id }}" @selected(old('organization_type_id') == $organizationType->id)>{{ $organizationType->name }}</option>
                                    @endforeach
                                </select>
                                <div class="small text-secondary mt-2">Facultatif si le fichier contient une colonne Type_organisation.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nouvelle sous catégorie</label>
                                <input type="text" name="organization_type_name" value="{{ old('organization_type_name') }}" class="form-control" placeholder="Ex : Institution publique">
                                <div class="small text-secondary mt-2">Facultatif si vous choisissez une sous catégorie existante ou si le fichier contient Type_organisation.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Modeles de fichier</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('super-admin.organizations.import-template', 'standard') }}" class="btn btn-outline-dark btn-sm">Modele Nom, Commune, Adresse, Mobile</a>
                                    <a href="{{ route('super-admin.organizations.import-template', 'typed') }}" class="btn btn-outline-dark btn-sm">Modele Type_organisation</a>
                                </div>
                                <div class="small text-secondary mt-2">Telechargez un modele, renseignez les lignes, puis importez le fichier complete.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Fichier CSV ou XLSX<span class="required-star">*</span></label>
                                <input type="file" name="csv_file" class="form-control" accept=".csv,.xlsx,text/csv,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                                <div class="small text-secondary mt-2">Mobile peut etre vide ou absent. S il est vide ou trop court, un numero a 10 chiffres sera genere ou complete automatiquement.</div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-light border mb-0">
                                    <div class="fw-semibold mb-1">Regles appliquees</div>
                                    <div class="small text-secondary">Code institution et cle portail generes depuis le nom, sous catégorie creee depuis Type_organisation si present, admin institutionnel cree avec email en @mysignal.pro et mot de passe par defaut 12345678.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Importer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createOrganizationModal" tabindex="-1" aria-labelledby="createOrganizationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="createOrganizationModalLabel">Nouvelle institution</h5>
                        <div class="small text-secondary">Créer une institution et lui attribuer ses modules dès l'ouverture.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.organizations.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Catégorie<span class="required-star">*</span></label>
                                <select name="application_id" class="form-select" id="organizationApplicationSelect" required>
                                    <option value="">Selectionner</option>
                                    @foreach ($applications as $application)
                                        <option value="{{ $application->id }}" data-feature-ids="{{ $application->features->pluck('id')->implode(',') }}" @selected(old('application_id') == $application->id)>{{ $application->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sous Catégorie<span class="required-star">*</span></label>
                                <select name="organization_type_id" class="form-select" required>
                                    <option value="">Selectionner</option>
                                    @foreach ($organizationTypes as $organizationType)
                                        <option value="{{ $organizationType->id }}" @selected(old('organization_type_id') == $organizationType->id)>{{ $organizationType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom<span class="required-star">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Compagnie Ivoirienne d Electricite" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                @include('partials.phone-field', ['value' => old('phone'), 'placeholder' => '0700000000'])
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Commune</label>
                                <input type="text" name="commune" value="{{ old('commune') }}" class="form-control" placeholder="Cocody">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="address" value="{{ old('address') }}" class="form-control" placeholder="Rue 12">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Fonctionnalites de l'institution</label>
                                <div class="small text-secondary mb-3">Les fonctionnalites de la catégorie sont preactivees. Vous pouvez desactiver localement celles que cette institution ne doit pas utiliser.</div>
                                <div class="feature-picker">
                                    @foreach ($groupedFeatures as $groupLabel => $groupFeatures)
                                        <section class="feature-picker-group">
                                            <div class="small text-uppercase fw-bold text-secondary mb-3">{{ $groupLabel }}</div>
                                            <div class="feature-picker-grid">
                                                @foreach ($groupFeatures as $feature)
                                                    <label for="organization-feature-create-{{ $feature->id }}" class="feature-option">
                                                        <div class="form-check">
                                                            @php
                                                                $oldApplication = $applications->firstWhere('id', (int) old('application_id'));
                                                                $defaultFeatureIds = $oldApplication?->features?->pluck('id')->all() ?? [];
                                                            @endphp
                                                            <input class="form-check-input" type="checkbox" value="{{ $feature->id }}" name="feature_ids[]" id="organization-feature-create-{{ $feature->id }}" @checked(in_array($feature->id, old('feature_ids', $defaultFeatureIds)))>
                                                            <span class="form-check-label">
                                                                <span class="feature-option-title d-block">{{ $feature->name }}</span>
                                                                <span class="feature-option-code d-block">{{ $feature->code }}</span>
                                                                @if ($feature->description)
                                                                    <span class="small text-secondary d-block mt-2">{{ $feature->description }}</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </section>
                                    @endforeach
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
    @if ($errors->any() && old('import_form'))
        <script>
            const importOrganizationsModal = document.getElementById('importOrganizationsModal');

            if (importOrganizationsModal) {
                bootstrap.Modal.getOrCreateInstance(importOrganizationsModal).show();
            }
        </script>
    @endif
    @if ($errors->any() && ! old('import_form'))
        <script>
            const createOrganizationModal = document.getElementById('createOrganizationModal');

            if (createOrganizationModal) {
                bootstrap.Modal.getOrCreateInstance(createOrganizationModal).show();
            }
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const applicationSelect = document.getElementById('organizationApplicationSelect');

            if (!applicationSelect) {
                return;
            }

            const applyApplicationDefaults = () => {
                const selectedOption = applicationSelect.options[applicationSelect.selectedIndex];
                const featureIds = (selectedOption?.dataset.featureIds || '')
                    .split(',')
                    .map((value) => value.trim())
                    .filter(Boolean);
                const featureInputs = document.querySelectorAll('input[name="feature_ids[]"]');

                featureInputs.forEach((input) => {
                    input.checked = featureIds.includes(input.value);
                });
            };

            applicationSelect.addEventListener('change', applyApplicationDefaults);
        });
    </script>
@endsection
