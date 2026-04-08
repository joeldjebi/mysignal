@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Organisations')
@section('page-title', 'Organisations')
@section('page-description', 'Creer les institutions et portails qui disposeront de leur propre administration locale.')

@section('header-badges')
    <span class="badge-soft">{{ $organizations->total() }} organisations</span>
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
        <div class="fw-bold mb-3">Liste des organisations</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, code, portail, email">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Application</label>
                    <select name="application_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($applications as $application)
                            <option value="{{ $application->id }}" @selected((string) request('application_id') === (string) $application->id)>{{ $application->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Type</label>
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
            <div class="text-center text-secondary py-5">Aucune organisation enregistree.</div>
        @else
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Organisation</th>
                                <th>Application</th>
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

    <div class="modal fade" id="createOrganizationModal" tabindex="-1" aria-labelledby="createOrganizationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="createOrganizationModalLabel">Nouvelle institution</h5>
                        <div class="small text-secondary">Creer une organisation et lui attribuer ses modules des l'ouverture.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.organizations.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Application</label>
                                <select name="application_id" class="form-select" id="organizationApplicationSelect">
                                    <option value="">Aucune application liee</option>
                                    @foreach ($applications as $application)
                                        <option value="{{ $application->id }}" data-feature-ids="{{ $application->features->pluck('id')->implode(',') }}" @selected(old('application_id') == $application->id)>{{ $application->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type d'organisation</label>
                                <select name="organization_type_id" class="form-select" required>
                                    <option value="">Selectionner</option>
                                    @foreach ($organizationTypes as $organizationType)
                                        <option value="{{ $organizationType->id }}" @selected(old('organization_type_id') == $organizationType->id)>{{ $organizationType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" value="{{ old('code') }}" class="form-control" placeholder="CIE" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Cle portail</label>
                                <input type="text" name="portal_key" value="{{ old('portal_key') }}" class="form-control" placeholder="portail-cie">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Compagnie Ivoirienne d Electricite" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                @include('partials.phone-field', ['value' => old('phone'), 'placeholder' => '0700000000'])
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Fonctionnalites de l'institution</label>
                                <div class="small text-secondary mb-3">Les fonctionnalites de l'application sont preactivees. Vous pouvez desactiver localement celles que cette institution ne doit pas utiliser.</div>
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
    @if ($errors->any() && old('code'))
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
