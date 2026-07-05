@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Sous-types de signal')
@section('page-title', 'Sous-types de signal')
@section('page-description', 'Referentiel des sous-types proposes au UP selon le type de signal selectionne.')

@section('header-badges')
    <span class="badge-soft">{{ $subTypes->total() }} sous-types</span>
    <a href="{{ route('super-admin.signal-sub-types.import-template') }}" class="btn btn-outline-dark">Modele CSV</a>
    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importSignalSubTypesModal">Importer CSV</button>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Nouveau sous-type</div>
                <form method="POST" action="{{ route('super-admin.signal-sub-types.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Type de signal</label>
                        <select name="signal_type_id" class="form-select" required>
                            <option value="">Choisir un type de signal</option>
                            @foreach ($signalTypes as $signalType)
                                <option value="{{ $signalType->id }}" @selected((string) old('signal_type_id', request('signal_type_id')) === (string) $signalType->id)>
                                    {{ $signalType->code }} - {{ $signalType->label }}
                                    @if ($signalType->organization)
                                        ({{ $signalType->organization->name }})
                                    @elseif ($signalType->application)
                                        ({{ $signalType->application->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Libelle</label>
                        <input type="text" name="label" value="{{ old('label') }}" class="form-control" placeholder="Panne compteur" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-dark">Enregistrer</button>
                </form>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Liste des sous-types</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, libelle, type de signal">
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
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Type de signal</label>
                            <select name="signal_type_id" class="form-select">
                                <option value="">Tous</option>
                                @foreach ($signalTypes as $signalType)
                                    <option value="{{ $signalType->id }}" @selected((string) request('signal_type_id') === (string) $signalType->id)>{{ $signalType->code }} - {{ $signalType->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Statut</label>
                            <select name="status" class="form-select">
                                <option value="">Tous</option>
                                <option value="active" @selected(request('status') === 'active')>Actif</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-dark w-100">OK</button>
                            <a href="{{ route('super-admin.signal-sub-types.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>

                <div class="table-toolbar">
                    <div class="table-meta">{{ $subTypes->total() }} resultat{{ $subTypes->total() > 1 ? 's' : '' }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Type de signal</th>
                                <th>Sous-type</th>
                                <th>Ordre</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subTypes as $subType)
                                <tr>
                                    <td>
                                        <div>{{ $subType->signalType?->label ?: '-' }}</div>
                                        <div class="small text-secondary">{{ $subType->signalType?->code ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $subType->label }}</div>
                                        <div class="small text-secondary">{{ $subType->code }}</div>
                                        <div class="small text-secondary mt-1">{{ $subType->description ?: '-' }}</div>
                                    </td>
                                    <td><span class="status-chip">{{ $subType->sort_order }}</span></td>
                                    <td><span class="status-chip">{{ $subType->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.signal-types.edit', $subType->signalType) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.signal-types.sub-types.toggle-status', [$subType->signalType, $subType]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $subType->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.signal-types.sub-types.destroy', [$subType->signalType, $subType]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Aucun sous-type de signal enregistre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $subTypes->currentPage() }} sur {{ $subTypes->lastPage() }}</div>
                    {{ $subTypes->links() }}
                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="importSignalSubTypesModal" tabindex="-1" aria-hidden="true">
        @php
            $selectedImportSignalTypeIds = collect(old('signal_type_ids', request('signal_type_id') ? [request('signal_type_id')] : []))
                ->map(fn ($id) => (string) $id)
                ->all();
            $importSignalTypeApplications = $signalTypes
                ->pluck('application')
                ->filter()
                ->unique('id')
                ->sortBy('name');
        @endphp
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden;">
                <div class="modal-header px-4 py-3 border-0" style="background: linear-gradient(145deg, #0f2738, #1b4867); color: white;">
                    <div>
                        <div class="small text-white-50 fw-semibold mb-1">Import CSV</div>
                        <div class="h5 fw-bold mb-0">Importer des sous-types de signal</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('super-admin.signal-sub-types.import') }}" class="vstack gap-3" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="import_form" value="1">
                        <div>
                            <label class="form-label">Categorie</label>
                            <select class="form-select" id="importSignalSubTypesApplicationFilter">
                                <option value="">Toutes les categories</option>
                                @foreach ($importSignalTypeApplications as $application)
                                    <option value="{{ $application->id }}">{{ $application->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Types de signal</label>
                            <div class="border rounded-4 p-3" id="importSignalSubTypesList" style="max-height: 320px; overflow-y: auto; background: #f8fafc;">
                                @foreach ($signalTypes as $signalType)
                                    <label
                                        class="d-flex gap-3 align-items-start bg-white border rounded-3 p-3 mb-2"
                                        data-signal-type-option
                                        data-application-id="{{ $signalType->application_id }}"
                                        style="cursor: pointer;"
                                    >
                                        <input
                                            type="checkbox"
                                            name="signal_type_ids[]"
                                            value="{{ $signalType->id }}"
                                            class="form-check-input mt-1"
                                            @checked(in_array((string) $signalType->id, $selectedImportSignalTypeIds, true))
                                        >
                                        <span class="d-block">
                                            <span class="fw-semibold d-block">{{ $signalType->label }}</span>
                                            <span class="small text-secondary">
                                                {{ $signalType->code }}
                                                @if ($signalType->organization)
                                                    - {{ $signalType->organization->name }}
                                                @elseif ($signalType->application)
                                                    - {{ $signalType->application->name }}
                                                @endif
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                                <div class="text-center text-secondary py-4 d-none" id="importSignalSubTypesEmptyState">Aucun type de signal pour cette categorie.</div>
                            </div>
                            <div class="small text-secondary mt-2">Chaque ligne du fichier sera creee comme sous-type pour tous les types selectionnes.</div>
                        </div>
                        <div>
                            <label class="form-label">Fichier CSV</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv,text/plain" required>
                            <div class="small text-secondary mt-2">Colonnes attendues : Libelle, Description, Ordre. Seul Libelle est obligatoire.</div>
                        </div>
                        <button type="submit" class="btn btn-dark">Importer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categoryFilter = document.getElementById('importSignalSubTypesApplicationFilter');
            const options = Array.from(document.querySelectorAll('[data-signal-type-option]'));
            const emptyState = document.getElementById('importSignalSubTypesEmptyState');

            if (!categoryFilter || options.length === 0) {
                return;
            }

            const applyImportSignalTypeFilter = () => {
                const applicationId = categoryFilter.value;
                let visibleCount = 0;

                options.forEach((option) => {
                    const isVisible = applicationId === '' || option.dataset.applicationId === applicationId;
                    option.classList.toggle('d-none', !isVisible);
                    visibleCount += isVisible ? 1 : 0;
                });

                emptyState?.classList.toggle('d-none', visibleCount > 0);
            };

            categoryFilter.addEventListener('change', applyImportSignalTypeFilter);
            applyImportSignalTypeFilter();
        });
    </script>

    @if ($errors->any() && old('import_form'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('importSignalSubTypesModal')).show();
            });
        </script>
    @endif
@endsection
