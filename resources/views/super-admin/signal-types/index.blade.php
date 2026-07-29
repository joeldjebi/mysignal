@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Types de signaux')
@section('page-title', 'Types de signaux')
@section('page-description', 'Referentiel des signaux publics, gerable par application puis par organisation quand un parametrage specifique est necessaire.')

@section('header-badges')
    <span class="badge-soft">{{ $signalTypes->total() }} types</span>
    <a href="{{ route('super-admin.signal-types.import-template') }}" class="btn btn-outline-dark">Modele CSV</a>
    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importSignalTypesModal">Importer CSV</button>
    <form method="POST" action="{{ route('super-admin.signal-types.clear') }}" onsubmit="return confirm('Vider tous les types de signaux ? Cette action est irreversible.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger" @disabled($signalTypes->total() === 0)>Vider les types</button>
    </form>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createSignalTypeModal">Nouveau type de signal</button>
@endsection

@section('content')
    @php
        $organizationsByApplicationPayload = $applications->mapWithKeys(fn ($application) => [
            $application->id => $application->organizations->map(fn ($organization) => [
                'id' => $organization->id,
                'code' => $organization->code,
                'name' => $organization->name,
            ])->values(),
        ]);
    @endphp
    <style>
        .institution-picker {
            border: 1px solid rgba(16,42,67,.12);
            border-radius: 14px;
            background: rgba(248,250,252,.95);
            max-height: 220px;
            overflow: auto;
            padding: .75rem;
        }
        .institution-picker-grid {
            display: grid;
            gap: .55rem;
        }
        .institution-picker-toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .6rem;
            margin-bottom: .65rem;
        }
        .institution-picker-option {
            display: flex;
            gap: .6rem;
            align-items: flex-start;
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 12px;
            background: #fff;
            padding: .65rem .75rem;
            cursor: pointer;
        }
        .institution-picker-option .form-check-input {
            margin-top: .2rem;
        }
        .bulk-check-cell {
            width: 44px;
        }
    </style>
    <section class="panel-card">
        <div class="fw-bold mb-3">Catalogue des signaux</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, libelle, application, organisation">
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
                            <label class="form-label small text-secondary">Institution concernée</label>
                            <select name="organization_id" class="form-select">
                                <option value="">Tous</option>
                                @foreach ($applications as $application)
                                    @foreach ($application->organizations as $organization)
                                        <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $application->name }} · {{ $organization->name }}</option>
                                    @endforeach
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
                            <button class="btn btn-dark w-100">OK</button>
                        </div>
                    </div>
                </form>
                <div class="table-toolbar">
                    <div class="table-meta">{{ $signalTypes->total() }} resultat{{ $signalTypes->total() > 1 ? 's' : '' }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <form
                            method="POST"
                            action="{{ route('super-admin.signal-types.destroy-selected') }}"
                            id="bulkDeleteSignalTypesForm"
                            onsubmit="return confirm('Supprimer les types de signaux selectionnes ? Cette action est irreversible.');"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" id="bulkDeleteSignalTypesButton" disabled>Supprimer la sélection</button>
                        </form>
                        <a href="{{ route('super-admin.signal-types.index') }}" class="btn btn-outline-secondary btn-sm">RAZ</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="bulk-check-cell">
                                    <input class="form-check-input" type="checkbox" id="selectAllSignalTypesOnPage" @disabled($signalTypes->count() === 0)>
                                </th>
                                <th>Catégorie</th>
                                <th>Institution concernée</th>
                                <th>Signal</th>
                                <th>SLA defaut</th>
                                <th>Identifiant</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($signalTypes as $signalType)
                                <tr data-signal-type-row="{{ $signalType->id }}">
                                    <td class="bulk-check-cell">
                                        <input
                                            class="form-check-input signal-type-bulk-checkbox"
                                            type="checkbox"
                                            name="signal_type_ids[]"
                                            value="{{ $signalType->id }}"
                                            form="bulkDeleteSignalTypesForm"
                                        >
                                    </td>
                                    <td>{{ $signalType->application?->name ?: '-' }}</td>
                                    <td>
                                        @php
                                            $signalTypeOrganizations = $signalType->organizations->isNotEmpty()
                                                ? $signalType->organizations
                                                : collect([$signalType->organization])->filter();
                                        @endphp
                                        {{ $signalTypeOrganizations->isNotEmpty() ? $signalTypeOrganizations->pluck('name')->join(', ') : 'Type partage a toute la catégorie' }}
                                    </td>
                                    <td>
                                        <div>{{ $signalType->label }}</div>
                                        <div class="small text-secondary">{{ $signalType->code }}</div>
                                        <div class="small text-secondary mt-1">{{ $signalType->description ?: '-' }}</div>
                                    </td>
                                    <td><span class="status-chip">{{ $signalType->default_sla_hours ? $signalType->default_sla_hours.' h' : '-' }}</span></td>
                                    <td>
                                        <span class="status-chip {{ $signalType->requires_public_user_identifier ? 'chip-warning' : 'chip-neutral' }}">
                                            {{ $signalType->requires_public_user_identifier ? 'Requis' : 'Selon catégorie' }}
                                        </span>
                                    </td>
                                    <td><span class="status-chip">{{ $signalType->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.signal-sub-types.index', ['signal_type_id' => $signalType->id]) }}" class="btn btn-sm btn-outline-primary">Sous-types</a>
                                            <a href="{{ route('super-admin.signal-types.edit', $signalType) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.signal-types.toggle-status', $signalType) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $signalType->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.signal-types.destroy', $signalType) }}" class="ajax-delete-signal-type-form" data-signal-type-id="{{ $signalType->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-secondary">Aucun type de signal enregistre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $signalTypes->currentPage() }} sur {{ $signalTypes->lastPage() }}</div>
                    {{ $signalTypes->links() }}
                </div>
    </section>

    <div class="modal fade" id="createSignalTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                <div class="modal-header px-4 py-3 border-0" style="background: linear-gradient(145deg, #0f2738, #1b4867); color: white;">
                    <div>
                        <div class="small text-white-50 fw-semibold mb-1">Nouveau type de signal</div>
                        <div class="h5 fw-bold mb-0">Creer un type de signal</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('super-admin.signal-types.store') }}" class="vstack gap-3">
                        @csrf
                        <input type="hidden" name="create_form" value="1">
                        <div>
                            <label class="form-label">Catégorie</label>
                            <select name="application_id" class="form-select" id="saSignalTypeApplicationCreate" required>
                                <option value="">Choisir une catégorie</option>
                                @foreach ($applications as $application)
                                    <option value="{{ $application->id }}" @selected(old('application_id') == $application->id)>{{ $application->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Institution concernée</label>
                            <div class="institution-picker-toolbar">
                                <input type="search" class="form-control" id="saSignalTypeOrganizationCreateFilter" placeholder="Filtrer les institutions">
                                <button type="button" class="btn btn-outline-dark" id="saSignalTypeOrganizationCreateToggle">Tout sélectionner</button>
                            </div>
                            <div class="institution-picker">
                                <div id="saSignalTypeOrganizationCreate" class="institution-picker-grid"></div>
                            </div>
                            <div class="small text-secondary mt-2" id="saSignalTypeOrganizationCreateStatus">Aucune institution selectionnee : type partage a toute la catégorie.</div>
                        </div>
                        <div>
                            <label class="form-label">Libelle</label>
                            <input type="text" name="label" class="form-control" placeholder="Coupure totale de courant" required>
                        </div>
                        <div>
                            <label class="form-label">SLA par defaut (heures)</label>
                            <input type="number" min="1" max="999" name="default_sla_hours" class="form-control" placeholder="4">
                        </div>
                        <div>
                            <label class="form-label">Identifiant lors du signalement</label>
                            <select name="requires_public_user_identifier" class="form-select">
                                <option value="0" @selected(old('requires_public_user_identifier', '0') === '0')>Selon la catégorie</option>
                                <option value="1" @selected(old('requires_public_user_identifier') === '1')>Requis pour ce type</option>
                            </select>
                            <div class="small text-secondary mt-1">Si rien n’est forcé ici, l’affichage dépend uniquement de la catégorie.</div>
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark">Creer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importSignalTypesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                <div class="modal-header px-4 py-3 border-0" style="background: linear-gradient(145deg, #0f2738, #1b4867); color: white;">
                    <div>
                        <div class="small text-white-50 fw-semibold mb-1">Import CSV</div>
                        <div class="h5 fw-bold mb-0">Importer des types de signal</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('super-admin.signal-types.import') }}" class="vstack gap-3" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="import_form" value="1">
                        <div>
                            <label class="form-label">Catégorie</label>
                            <select name="application_id" class="form-select" id="saSignalTypeApplicationImport" required>
                                <option value="">Choisir une catégorie</option>
                                @foreach ($applications as $application)
                                    <option value="{{ $application->id }}" @selected(old('import_form') && old('application_id') == $application->id)>{{ $application->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Institution concernée</label>
                            <div class="institution-picker-toolbar">
                                <input type="search" class="form-control" id="saSignalTypeOrganizationImportFilter" placeholder="Filtrer les institutions">
                                <button type="button" class="btn btn-outline-dark" id="saSignalTypeOrganizationImportToggle">Tout sélectionner</button>
                            </div>
                            <div class="institution-picker">
                                <div id="saSignalTypeOrganizationImport" class="institution-picker-grid"></div>
                            </div>
                            <div class="small text-secondary mt-2" id="saSignalTypeOrganizationImportStatus">Aucune institution selectionnee : type partage a toute la catégorie.</div>
                        </div>
                        <div>
                            <label class="form-label">Fichier CSV</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv,text/plain" required>
                            <div class="small text-secondary mt-2">Colonnes attendues : Libelle, Description, SLA_defaut_heures. Seul Libelle est obligatoire.</div>
                        </div>
                        <button type="submit" class="btn btn-dark">Importer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modalId = @json(old('import_form') ? 'importSignalTypesModal' : 'createSignalTypeModal');
                bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).show();
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bulkCheckboxes = Array.from(document.querySelectorAll('.signal-type-bulk-checkbox'));
            const bulkDeleteButton = document.getElementById('bulkDeleteSignalTypesButton');
            const selectAllCheckbox = document.getElementById('selectAllSignalTypesOnPage');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const updateBulkDeleteState = () => {
                const activeCheckboxes = bulkCheckboxes.filter((checkbox) => checkbox.isConnected);
                const checkedCount = activeCheckboxes.filter((checkbox) => checkbox.checked).length;

                if (bulkDeleteButton) {
                    bulkDeleteButton.disabled = checkedCount === 0;
                    bulkDeleteButton.textContent = checkedCount > 0
                        ? `Supprimer la sélection (${checkedCount})`
                        : 'Supprimer la sélection';
                }

                if (selectAllCheckbox) {
                    selectAllCheckbox.disabled = activeCheckboxes.length === 0;
                    selectAllCheckbox.checked = checkedCount > 0 && checkedCount === activeCheckboxes.length;
                    selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < activeCheckboxes.length;
                }
            };

            const ensureEmptySignalTypeRow = () => {
                const tbody = document.querySelector('.table-modern tbody');
                const remainingRows = tbody ? tbody.querySelectorAll('tr[data-signal-type-row]').length : 0;

                if (tbody && remainingRows === 0 && !tbody.querySelector('[data-signal-type-empty-row]')) {
                    tbody.innerHTML = '<tr data-signal-type-empty-row><td colspan="7" class="text-center text-secondary">Aucun type de signal enregistre.</td></tr>';
                }
            };

            bulkCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', updateBulkDeleteState);
            });
            selectAllCheckbox?.addEventListener('change', () => {
                bulkCheckboxes.filter((checkbox) => checkbox.isConnected).forEach((checkbox) => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateBulkDeleteState();
            });
            updateBulkDeleteState();

            document.querySelectorAll('.ajax-delete-signal-type-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!confirm('Supprimer ce type de signal ? Cette action est irreversible.')) {
                        return;
                    }

                    const button = form.querySelector('button[type="submit"], button:not([type])');
                    const originalText = button?.textContent || 'Supprimer';

                    if (button) {
                        button.disabled = true;
                        button.textContent = 'Suppression...';
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'DELETE',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('delete_failed');
                        }

                        const row = form.closest('tr[data-signal-type-row]');
                        row?.remove();
                        updateBulkDeleteState();
                        ensureEmptySignalTypeRow();
                    } catch (error) {
                        alert('La suppression a echoue. Veuillez reessayer.');

                        if (button) {
                            button.disabled = false;
                            button.textContent = originalText;
                        }
                    }
                });
            });

            const organizationsByApplication = @json($organizationsByApplicationPayload);
            const oldCreateOrganizationIds = @json(! old('import_form') ? collect(old('organization_ids', []))->map(fn ($id) => (string) $id)->values() : collect());
            const oldImportOrganizationIds = @json(old('import_form') ? collect(old('organization_ids', []))->map(fn ($id) => (string) $id)->values() : collect());

            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const initInstitutionPicker = ({ applicationSelect, organizationPicker, organizationStatus, organizationFilter, organizationToggle, oldOrganizationIds }) => {
                const selectedOrganizationIds = () => Array.from(organizationPicker.querySelectorAll('input[name="organization_ids[]"]:checked'))
                    .map((input) => String(input.value));

                const visibleOrganizationOptions = () => Array.from(organizationPicker.querySelectorAll('.institution-picker-option'))
                    .filter((option) => !option.classList.contains('d-none'));

                const updateOrganizationStatus = () => {
                    const count = selectedOrganizationIds().length;
                    const visibleOptions = visibleOrganizationOptions();
                    const checkedVisibleCount = visibleOptions.filter((option) => option.querySelector('input')?.checked).length;
                    organizationStatus.textContent = count > 0
                        ? `${count} institution${count > 1 ? 's' : ''} selectionnee${count > 1 ? 's' : ''}.`
                        : 'Aucune institution selectionnee : type partage a toute la catégorie.';
                    organizationToggle.textContent = visibleOptions.length > 0 && checkedVisibleCount === visibleOptions.length
                        ? 'Tout désélectionner'
                        : 'Tout sélectionner';
                    organizationToggle.disabled = visibleOptions.length === 0;
                };

                const applyOrganizationFilter = () => {
                    const query = String(organizationFilter.value || '').trim().toLowerCase();

                    organizationPicker.querySelectorAll('.institution-picker-option').forEach((option) => {
                        const haystack = String(option.dataset.search || '').toLowerCase();
                        option.classList.toggle('d-none', query !== '' && !haystack.includes(query));
                    });

                    updateOrganizationStatus();
                };

                const syncOrganizations = () => {
                    const applicationId = applicationSelect.value;
                    const organizations = organizationsByApplication[applicationId] || [];
                    const currentValues = selectedOrganizationIds();
                    const valuesToRestore = currentValues.length ? currentValues : oldOrganizationIds;

                    organizationPicker.innerHTML = organizations.length
                        ? organizations.map((organization) => {
                            const value = String(organization.id);
                            const checked = valuesToRestore.includes(value) ? 'checked' : '';
                            return `
                                <label class="institution-picker-option" data-search="${escapeHtml(`${organization.name} ${organization.code || ''}`)}">
                                    <input class="form-check-input" type="checkbox" name="organization_ids[]" value="${escapeHtml(value)}" ${checked}>
                                    <span>
                                        <span class="fw-semibold d-block">${escapeHtml(organization.name)}</span>
                                        <span class="small text-secondary">${escapeHtml(organization.code || '')}</span>
                                    </span>
                                </label>
                            `;
                        }).join('')
                        : '<div class="small text-secondary">Selectionnez une catégorie pour afficher ses institutions.</div>';

                    organizationPicker.querySelectorAll('input[name="organization_ids[]"]').forEach((input) => {
                        input.addEventListener('change', updateOrganizationStatus);
                    });
                    applyOrganizationFilter();
                };

                organizationFilter?.addEventListener('input', applyOrganizationFilter);
                organizationToggle?.addEventListener('click', () => {
                    const visibleOptions = visibleOrganizationOptions();
                    const shouldSelect = visibleOptions.some((option) => !option.querySelector('input')?.checked);

                    visibleOptions.forEach((option) => {
                        const input = option.querySelector('input');
                        if (input) {
                            input.checked = shouldSelect;
                        }
                    });

                    updateOrganizationStatus();
                });
                applicationSelect?.addEventListener('change', syncOrganizations);
                syncOrganizations();
            };

            initInstitutionPicker({
                applicationSelect: document.getElementById('saSignalTypeApplicationCreate'),
                organizationPicker: document.getElementById('saSignalTypeOrganizationCreate'),
                organizationStatus: document.getElementById('saSignalTypeOrganizationCreateStatus'),
                organizationFilter: document.getElementById('saSignalTypeOrganizationCreateFilter'),
                organizationToggle: document.getElementById('saSignalTypeOrganizationCreateToggle'),
                oldOrganizationIds: oldCreateOrganizationIds,
            });
            initInstitutionPicker({
                applicationSelect: document.getElementById('saSignalTypeApplicationImport'),
                organizationPicker: document.getElementById('saSignalTypeOrganizationImport'),
                organizationStatus: document.getElementById('saSignalTypeOrganizationImportStatus'),
                organizationFilter: document.getElementById('saSignalTypeOrganizationImportFilter'),
                organizationToggle: document.getElementById('saSignalTypeOrganizationImportToggle'),
                oldOrganizationIds: oldImportOrganizationIds,
            });
        });
    </script>
@endsection
