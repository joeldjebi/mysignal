@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Admins institutionnels')
@section('page-title', 'Admins institutionnels')
@section('page-description', 'Créer les comptes admins racine qui géreront ensuite leurs utilisateurs, rôles et permissions dans leur portail.')

@section('header-badges')
    <span class="badge-soft">{{ $admins->total() }} admins</span>
    <a href="{{ route('super-admin.institution-admins.orphaned') }}" class="btn btn-outline-warning">
        AI sans institution
        @if (($orphanedAdminsCount ?? 0) > 0)
            <span class="badge text-bg-warning ms-1">{{ $orphanedAdminsCount }}</span>
        @endif
    </a>
    <a href="{{ route('institution.login') }}" class="btn btn-outline-dark" target="_blank" rel="noopener">
        Page login AI
    </a>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createInstitutionAdminModal">
        Créer une institution
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
        .letter-history-meta {
            display: grid;
            gap: .15rem;
            min-width: 180px;
        }
        .batch-letters-loader {
            position: fixed;
            inset: 0;
            z-index: 1100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(24, 52, 71, .5);
            backdrop-filter: blur(7px);
        }
        .batch-letters-loader.is-visible { display: flex; }
        .batch-letters-loader-card {
            width: min(410px, 100%);
            padding: 1.5rem;
            border: 1px solid rgba(16, 42, 67, .1);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 28px 70px rgba(16, 42, 67, .28);
            text-align: center;
        }
        .batch-letters-loader-ring {
            width: 52px;
            height: 52px;
            margin: 0 auto 1rem;
            border: 4px solid rgba(255, 161, 23, .2);
            border-top-color: #ffa117;
            border-radius: 50%;
            animation: batchLettersSpin .8s linear infinite;
        }
        .batch-letters-loader-bar {
            height: 6px;
            overflow: hidden;
            margin-top: 1rem;
            border-radius: 999px;
            background: rgba(103, 145, 255, .16);
        }
        .batch-letters-loader-bar span {
            display: block;
            width: 42%;
            height: 100%;
            border-radius: inherit;
            background: #6791ff;
            animation: batchLettersProgress 1.25s ease-in-out infinite;
        }
        @keyframes batchLettersSpin { to { transform: rotate(360deg); } }
        @keyframes batchLettersProgress {
            0% { transform: translateX(-120%); }
            100% { transform: translateX(340%); }
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
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, téléphone">
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
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Par page</label>
                    <select name="per_page" class="form-select">
                        @foreach ([12, 25, 50, 100] as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected((int) request('per_page', 12) === $perPageOption)>{{ $perPageOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-dark">Filtrer</button>
                <a href="{{ route('super-admin.institution-admins.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $admins->total() }} résultat{{ $admins->total() > 1 ? 's' : '' }}</div>
            <button type="button" class="btn btn-outline-success" id="generateLettersButton" disabled data-bs-toggle="modal" data-bs-target="#generateLettersModal">
                Générer les courriers sélectionnés <span class="badge text-bg-success ms-1" id="selectedAdminsCount">0</span>
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th style="width:42px"><input type="checkbox" class="form-check-input" id="selectAllInstitutionAdmins" aria-label="Sélectionner tous les admins affichés"></th>
                        <th>Admin</th>
                        <th>Organisation</th>
                        <th>Fonctionnalités</th>
                        <th>Statut</th>
                        <th>Courriers</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admins as $admin)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input institution-admin-selector" value="{{ $admin->id }}" aria-label="Sélectionner {{ $admin->name }}">
                            </td>
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
                            <td>
                                @if ($admin->activation_letters_count > 0 && $admin->latestActivationLetter)
                                    <div class="letter-history-meta small">
                                        <span class="fw-semibold">{{ $admin->activation_letters_count }} courrier{{ $admin->activation_letters_count > 1 ? 's' : '' }}</span>
                                        <span class="text-secondary">Dernier : {{ $admin->latestActivationLetter->generated_at?->format('d/m/Y à H:i') ?: $admin->latestActivationLetter->created_at?->format('d/m/Y à H:i') }}</span>
                                        <span class="text-secondary">Par {{ $admin->latestActivationLetter->creator?->name ?: 'Utilisateur supprimé' }}</span>
                                    </div>
                                @else
                                    <span class="small text-secondary">Jamais généré</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.institution-admins.edit', $admin) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <a href="{{ route('super-admin.institution-admins.activation-letter.edit', $admin) }}" class="btn btn-sm btn-outline-success">Générer le courrier</a>
                                    <a href="{{ route('super-admin.institution-admins.activation-letter.history', $admin) }}" class="btn btn-sm btn-outline-secondary">Historique</a>
                                    @if (auth()->user()?->is_super_admin || auth()->user()?->hasPermissionCode('SA_INSTITUTION_ADMINS_UPDATE') || auth()->user()?->hasPermissionCode('SA_INSTITUTION_ADMINS_MANAGE'))
                                        <form method="POST" action="{{ route('super-admin.institution-admins.send-access', $admin) }}" onsubmit="return confirm('Un nouveau mot de passe temporaire sera généré puis envoyé par SMS et par e-mail. Continuer ?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary">Envoyer les accès</button>
                                        </form>
                                    @endif
                                    @if (auth()->user()?->hasPermissionCode('SA_INSTITUTION_ADMINS_TOGGLE_STATUS'))
                                        <form method="POST" action="{{ route('super-admin.institution-admins.toggle-status', $admin) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $admin->status === 'active' ? 'Désactiver' : 'Activer' }}</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('super-admin.institution-admins.destroy', $admin) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary">Aucun admin institutionnel enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $admins->currentPage() }} sur {{ $admins->lastPage() }}</div>
            {{ $admins->links() }}
        </div>
    </section>

    <div class="batch-letters-loader" id="batchLettersLoader" aria-hidden="true">
        <div class="batch-letters-loader-card" role="status" aria-live="polite">
            <div class="batch-letters-loader-ring" aria-hidden="true"></div>
            <div class="fw-bold mb-1" id="batchLettersLoaderTitle">Génération des courriers</div>
            <div class="text-secondary small" id="batchLettersLoaderMessage">Préparation des courriers officiels et de leurs QR codes.</div>
            <div class="batch-letters-loader-bar" aria-hidden="true"><span></span></div>
        </div>
    </div>

    <div class="modal fade" id="generateLettersModal" tabindex="-1" aria-labelledby="generateLettersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="generateLettersModalLabel">Générer un lot de courriers</h5>
                        <div class="small text-secondary"><span id="selectedAdminsModalCount">0</span> institution(s) sélectionnée(s). Chaque courrier aura son propre code et QR code.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.institution-activation-letter-batches.store') }}" id="batchLettersForm">
                    @csrf
                    <div id="selectedInstitutionAdminsInputs"></div>
                    <div class="modal-body">
                        <div class="alert alert-info small mb-3">
                            Si un ancien code d’activation est encore en attente pour une institution sélectionnée, il sera remplacé par le nouveau code. Les courriers précédents restent disponibles dans l’historique.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Nom du lot</label>
                                <input name="batch_label" class="form-control" placeholder="Ex. Relance des institutions - octobre 2026">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Lieu d’émission <span class="text-danger">*</span></label>
                                <input name="issue_place" class="form-control" value="Abidjan" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Objet du courrier <span class="text-danger">*</span></label>
                                <input name="letter_subject" class="form-control" value="Désignation du point focal My-Signal" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date d’émission <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Validité du code</label>
                                <input type="date" name="expires_at" class="form-control" value="{{ now()->addDays(30)->toDateString() }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Générer et télécharger le lot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createInstitutionAdminModal" tabindex="-1" aria-labelledby="createInstitutionAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="createInstitutionAdminModalLabel">Nouvel admin institutionnel</h5>
                        <div class="small text-secondary">Créer le compte admin racine d’un portail institutionnel.</div>
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
                                    <option value="">Sélectionner</option>
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
                                <label class="form-label">Fonctionnalités attribuées</label>
                                <div class="small text-secondary mb-2">Les fonctionnalités ouvertes pour l’institution sont présélectionnées automatiquement. Vous pouvez ensuite limiter l’AI à un sous-ensemble.</div>
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
                                            <div class="text-secondary small">Aucune fonctionnalité active.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
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
            const selectAll = document.getElementById('selectAllInstitutionAdmins');
            const selections = Array.from(document.querySelectorAll('.institution-admin-selector'));
            const actionButton = document.getElementById('generateLettersButton');
            const countBadge = document.getElementById('selectedAdminsCount');
            const modalCount = document.getElementById('selectedAdminsModalCount');
            const inputs = document.getElementById('selectedInstitutionAdminsInputs');
            const batchForm = document.getElementById('batchLettersForm');
            const loader = document.getElementById('batchLettersLoader');
            const loaderTitle = document.getElementById('batchLettersLoaderTitle');
            const loaderMessage = document.getElementById('batchLettersLoaderMessage');

            const setLoader = (visible, title, message) => {
                if (!loader) {
                    return;
                }

                if (title) {
                    loaderTitle.textContent = title;
                }
                if (message) {
                    loaderMessage.textContent = message;
                }

                loader.classList.toggle('is-visible', visible);
                loader.setAttribute('aria-hidden', visible ? 'false' : 'true');
            };

            const responseMessage = async (response) => {
                const payload = await response.json().catch(() => ({}));
                const errors = Object.values(payload.errors || {}).flat();

                return errors[0] || payload.message || 'La génération du lot a échoué. Veuillez réessayer.';
            };

            const filenameFromResponse = (response) => {
                const header = response.headers.get('Content-Disposition') || '';
                const match = header.match(/filename="?([^";]+)"?/i);

                return match?.[1] || 'courriers-institutionnels.zip';
            };

            const requestHeaders = {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            };

            const refreshSelection = () => {
                const selected = selections.filter((input) => input.checked);
                const count = selected.length;

                actionButton.disabled = count === 0;
                countBadge.textContent = count;
                modalCount.textContent = count;
                inputs.replaceChildren(...selected.map((input) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'institution_admin_ids[]';
                    hidden.value = input.value;
                    return hidden;
                }));

                selectAll.checked = count > 0 && count === selections.length;
                selectAll.indeterminate = count > 0 && count < selections.length;
            };

            selectAll?.addEventListener('change', () => {
                selections.forEach((input) => { input.checked = selectAll.checked; });
                refreshSelection();
            });

            selections.forEach((input) => input.addEventListener('change', refreshSelection));
            refreshSelection();

            batchForm?.addEventListener('submit', async (event) => {
                if (!window.fetch) {
                    return;
                }

                event.preventDefault();
                const submitButton = batchForm.querySelector('button[type="submit"]');
                const selectedCount = selections.filter((input) => input.checked).length;

                submitButton.disabled = true;
                setLoader(
                    true,
                    'Courriers générés : 0 / '+selectedCount,
                    selectedCount+' courrier'+(selectedCount > 1 ? 's' : '')+' à préparer. Préparation des QR codes et des documents officiels.'
                );
                bootstrap.Modal.getInstance(document.getElementById('generateLettersModal'))?.hide();

                try {
                    const generationResponse = await fetch(batchForm.action, {
                        method: 'POST',
                        body: new FormData(batchForm),
                        credentials: 'same-origin',
                        headers: requestHeaders,
                    });

                    if (!generationResponse.ok) {
                        throw new Error(await responseMessage(generationResponse));
                    }

                    const generation = await generationResponse.json();
                    let preparedCount = 0;

                    for (const letter of generation.letters || []) {
                        const prepareResponse = await fetch(letter.prepare_url, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: requestHeaders,
                        });

                        if (!prepareResponse.ok) {
                            throw new Error(await responseMessage(prepareResponse));
                        }

                        preparedCount += 1;
                        setLoader(
                            true,
                            'Courriers générés : '+preparedCount+' / '+generation.total,
                            'Le courrier '+preparedCount+' est prêt. Les autres courriers sont en cours de préparation.'
                        );
                    }

                    setLoader(
                        true,
                        'Création du fichier ZIP',
                        preparedCount+' courrier'+(preparedCount > 1 ? 's' : '')+' généré'+(preparedCount > 1 ? 's' : '')+' sur '+generation.total+'. L’archive est en cours de création.'
                    );

                    const archiveResponse = await fetch(generation.archive_url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: requestHeaders,
                    });

                    if (!archiveResponse.ok) {
                        throw new Error(await responseMessage(archiveResponse));
                    }

                    const archivePayload = await archiveResponse.json();
                    setLoader(
                        true,
                        'Téléchargement du fichier ZIP',
                        preparedCount+' courrier'+(preparedCount > 1 ? 's' : '')+' généré'+(preparedCount > 1 ? 's' : '')+'. Le téléchargement démarre automatiquement.'
                    );

                    const downloadResponse = await fetch(archivePayload.download_url, {
                        credentials: 'same-origin',
                    });

                    if (!downloadResponse.ok) {
                        throw new Error(await responseMessage(downloadResponse));
                    }

                    const archive = await downloadResponse.blob();
                    const downloadLink = document.createElement('a');
                    downloadLink.href = URL.createObjectURL(archive);
                    downloadLink.download = filenameFromResponse(downloadResponse);
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    downloadLink.remove();
                    window.setTimeout(() => URL.revokeObjectURL(downloadLink.href), 1000);

                    setLoader(false);
                    window.setTimeout(() => window.location.reload(), 700);
                } catch (error) {
                    setLoader(false);
                    alert(error.message || 'La génération du lot a échoué. Veuillez réessayer.');
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('generateLettersModal')).show();
                } finally {
                    submitButton.disabled = false;
                }
            });
        })();
    </script>
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
