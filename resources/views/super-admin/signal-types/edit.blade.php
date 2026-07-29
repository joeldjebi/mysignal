@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier type de signal')
@section('page-title', 'Modifier un type de signal')
@section('page-description', 'Ajuster un type de signal partage par application ou specifique a une organisation.')

@section('content')
    @php
        $organizationsByApplicationPayload = $applications->mapWithKeys(fn ($application) => [
            $application->id => $application->organizations->map(fn ($organization) => [
                'id' => $organization->id,
                'code' => $organization->code,
                'name' => $organization->name,
            ])->values(),
        ]);
        $signalTypeOrganizations = $signalType->organizations->isNotEmpty()
            ? $signalType->organizations
            : collect([$signalType->organization])->filter();
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
    </style>
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="small text-secondary fw-semibold mb-2">Type de signal</div>
                <div class="h5 fw-bold mb-1">{{ $signalType->label }}</div>
                <div class="text-secondary small mb-4">{{ $signalType->code }} · {{ $signalType->application?->name ?: 'Sans catégorie' }} · {{ $signalTypeOrganizations->isNotEmpty() ? $signalTypeOrganizations->pluck('name')->join(', ') : 'Type partage' }}</div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="status-chip">{{ $signalType->status }}</span>
                    <span class="status-chip">{{ $signalType->default_sla_hours ? $signalType->default_sla_hours.' h' : 'SansTCM defaut' }}</span>
                    <span class="status-chip">{{ $signalType->requires_public_user_identifier ? 'Identifiant requis' : 'Identifiant selon catégorie' }}</span>
                </div>
                <div class="text-secondary small">{{ $signalType->description ?: 'Aucune description detaillee pour le moment.' }}</div>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Edition du referentiel</div>
                <form method="POST" action="{{ route('super-admin.signal-types.update', $signalType) }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-4">
                        <label class="form-label">Catégorie</label>
                        <select name="application_id" class="form-select" id="saSignalTypeApplicationEdit" required>
                            <option value="">Choisir une catégorie</option>
                            @foreach ($applications as $application)
                                <option value="{{ $application->id }}" @selected(old('application_id', $signalType->application_id) == $application->id)>{{ $application->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Institution concernée</label>
                        <div class="institution-picker-toolbar">
                            <input type="search" class="form-control" id="saSignalTypeOrganizationEditFilter" placeholder="Filtrer les institutions">
                            <button type="button" class="btn btn-outline-dark" id="saSignalTypeOrganizationEditToggle">Tout sélectionner</button>
                        </div>
                        <div class="institution-picker">
                            <div id="saSignalTypeOrganizationEdit" class="institution-picker-grid"></div>
                        </div>
                        <div class="small text-secondary mt-2" id="saSignalTypeOrganizationEditStatus">Aucune institution selectionnee : type partage a toute la catégorie.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SLA par defaut (heures)</label>
                        <input type="number" min="1" max="999" name="default_sla_hours" value="{{ old('default_sla_hours', $signalType->default_sla_hours) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Identifiant lors du signalement</label>
                        <select name="requires_public_user_identifier" class="form-select">
                            <option value="0" @selected(! old('requires_public_user_identifier', $signalType->requires_public_user_identifier))>Selon la catégorie</option>
                            <option value="1" @selected(old('requires_public_user_identifier', $signalType->requires_public_user_identifier))>Requis pour ce type</option>
                        </select>
                        <div class="small text-secondary mt-1">Si rien n’est forcé ici, l’affichage dépend uniquement de la catégorie.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Libelle</label>
                        <input type="text" name="label" value="{{ old('label', $signalType->label) }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $signalType->description) }}</textarea>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Enregistrer</button>
                        <a href="{{ route('super-admin.signal-types.index') }}" class="btn btn-outline-secondary">Retour</a>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <section class="panel-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <div class="fw-bold">Sous-types de signal</div>
                        <div class="small text-secondary">Si au moins un sous-type actif existe, le UP devra choisir un sous-type ou Autre.</div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge-soft">{{ $signalType->subTypes->count() }} sous-types</span>
                        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createSignalSubTypeModal">
                            Ajouter un sous-type de signal
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Ordre</th>
                                <th>Code</th>
                                <th>Libelle</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($signalType->subTypes as $subType)
                                <tr>
                                    <td style="width: 90px;"><span class="status-chip">{{ $subType->sort_order }}</span></td>
                                    <td>{{ $subType->code }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('super-admin.signal-types.sub-types.update', [$signalType, $subType]) }}" class="row g-2 align-items-end">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-md-5">
                                                <label class="form-label small text-secondary">Libelle</label>
                                                <input class="form-control form-control-sm" name="label" value="{{ $subType->label }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-secondary">Description</label>
                                                <input class="form-control form-control-sm" name="description" value="{{ $subType->description }}">
                                            </div>
                                            <div class="col-md-1">
                                                <button class="btn btn-sm btn-outline-dark w-100">OK</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td><span class="status-chip">{{ $subType->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <form method="POST" action="{{ route('super-admin.signal-types.sub-types.toggle-status', [$signalType, $subType]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $subType->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.signal-types.sub-types.destroy', [$signalType, $subType]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Aucun sous-type enregistre pour ce type de signal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="createSignalSubTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden;">
                <div class="modal-header px-4 py-3 border-0" style="background: linear-gradient(145deg, #0f2738, #1b4867); color: white;">
                    <div>
                        <div class="small text-white-50 fw-semibold mb-1">Sous-type de signal</div>
                        <div class="h5 fw-bold mb-0">Ajouter un sous-type de signal</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('super-admin.signal-types.sub-types.store', $signalType) }}" class="vstack gap-3">
                        @csrf
                        <input type="hidden" name="sub_type_form" value="1">
                        <div>
                            <label class="form-label">Libelle <span class="text-danger">*</span></label>
                            <input type="text" name="label" value="{{ old('sub_type_form') ? old('label') : '' }}" class="form-control" placeholder="Cable arrache" required>
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('sub_type_form') ? old('description') : '' }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-dark">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any() && old('sub_type_form'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createSignalSubTypeModal')).show();
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const organizationsByApplication = @json($organizationsByApplicationPayload);
            const applicationSelect = document.getElementById('saSignalTypeApplicationEdit');
            const organizationPicker = document.getElementById('saSignalTypeOrganizationEdit');
            const organizationStatus = document.getElementById('saSignalTypeOrganizationEditStatus');
            const organizationFilter = document.getElementById('saSignalTypeOrganizationEditFilter');
            const organizationToggle = document.getElementById('saSignalTypeOrganizationEditToggle');
            const selectedOrganizationIds = @json(collect(old('organization_ids', $signalType->organizations->pluck('id')->when($signalType->organization_id !== null, fn ($ids) => $ids->push($signalType->organization_id))->unique()->values()->all()))->map(fn ($id) => (string) $id)->values());

            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const checkedOrganizationIds = () => Array.from(organizationPicker.querySelectorAll('input[name="organization_ids[]"]:checked'))
                .map((input) => String(input.value));

            const visibleOrganizationOptions = () => Array.from(organizationPicker.querySelectorAll('.institution-picker-option'))
                .filter((option) => !option.classList.contains('d-none'));

            const updateOrganizationStatus = () => {
                const count = checkedOrganizationIds().length;
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
                const currentValues = checkedOrganizationIds();
                const valuesToRestore = currentValues.length ? currentValues : selectedOrganizationIds;

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
        });
    </script>
@endsection
