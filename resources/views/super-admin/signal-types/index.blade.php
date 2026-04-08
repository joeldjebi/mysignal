@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Types de signaux')
@section('page-title', 'Types de signaux')
@section('page-description', 'Referentiel des signaux publics, gerable par application puis par organisation quand un parametrage specifique est necessaire.')

@section('header-badges')
    <span class="badge-soft">{{ $signalTypes->total() }} types</span>
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
        $applicationCodeByIdPayload = $applications->mapWithKeys(fn ($application) => [
            $application->id => $application->code,
        ]);
    @endphp
    <section class="panel-card">
        <div class="fw-bold mb-3">Catalogue des signaux</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, libelle, application, organisation">
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
                            <label class="form-label small text-secondary">Organisation</label>
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
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-dark w-100">OK</button>
                        </div>
                    </div>
                </form>
                <div class="table-toolbar">
                    <div class="table-meta">{{ $signalTypes->total() }} resultat{{ $signalTypes->total() > 1 ? 's' : '' }}</div>
                    <a href="{{ route('super-admin.signal-types.index') }}" class="btn btn-outline-secondary btn-sm">RAZ</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Application</th>
                                <th>Organisation</th>
                                <th>Signal</th>
                                <th>SLA defaut</th>
                                <th>Champs</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($signalTypes as $signalType)
                                <tr>
                                    <td>{{ $signalType->application?->name ?: '-' }}</td>
                                    <td>{{ $signalType->organization?->name ?: 'Type partage a toute l application' }}</td>
                                    <td>
                                        <div>{{ $signalType->label }}</div>
                                        <div class="small text-secondary">{{ $signalType->code }}</div>
                                        <div class="small text-secondary mt-1">{{ $signalType->description ?: '-' }}</div>
                                    </td>
                                    <td><span class="status-chip">{{ $signalType->default_sla_hours ? $signalType->default_sla_hours.' h' : '-' }}</span></td>
                                    <td>{{ count($signalType->data_fields ?? []) }} champ(s)</td>
                                    <td><span class="status-chip">{{ $signalType->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.signal-types.edit', $signalType) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.signal-types.toggle-status', $signalType) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $signalType->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.signal-types.destroy', $signalType) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-secondary">Aucun type de signal enregistre.</td></tr>
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
                        <div>
                            <label class="form-label">Application</label>
                            <select name="application_id" class="form-select" id="saSignalTypeApplicationCreate" required>
                                <option value="">Choisir une application</option>
                                @foreach ($applications as $application)
                                    <option value="{{ $application->id }}" @selected(old('application_id') == $application->id)>{{ $application->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Organisation</label>
                            <select name="organization_id" class="form-select" id="saSignalTypeOrganizationCreate">
                                <option value="">Type partage a toute l application</option>
                            </select>
                            <div class="small text-secondary mt-2">Laissez vide pour creer un type partage a toute l application. Selectionnez une organisation seulement si ce type lui est propre.</div>
                        </div>
                        <div>
                            <label class="form-label">Code signal</label>
                            <input type="text" name="code" class="form-control" id="saSignalTypeCodeCreate" placeholder="Code genere automatiquement" required>
                            <div class="small text-secondary mt-2">Le code est suggere automatiquement selon l application et l organisation choisies, mais vous pouvez encore le modifier.</div>
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
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        @include('partials.signal-type-field-builder', ['builderId' => 'sa-signal-type-create', 'fields' => []])
                        <button type="submit" class="btn btn-dark">Creer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createSignalTypeModal')).show();
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const organizationsByApplication = @json($organizationsByApplicationPayload);
            const applicationCodes = @json($applicationCodeByIdPayload);
            const existingCodes = @json($existingSignalTypeCodes);

            const applicationSelect = document.getElementById('saSignalTypeApplicationCreate');
            const organizationSelect = document.getElementById('saSignalTypeOrganizationCreate');
            const codeInput = document.getElementById('saSignalTypeCodeCreate');

            const slugifyCodePart = (value) => String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^A-Za-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .toUpperCase();

            const suggestCode = () => {
                const applicationId = applicationSelect.value;
                const organization = (organizationsByApplication[applicationId] || []).find((item) => String(item.id) === String(organizationSelect.value));
                const applicationCode = slugifyCodePart(applicationCodes[applicationId] || 'SIGNAL');
                const organizationCode = slugifyCodePart(organization?.code || '');
                const base = [applicationCode, organizationCode].filter(Boolean).join('_');

                if (!base) {
                    return;
                }

                let sequence = 1;
                let candidate = `${base}_${String(sequence).padStart(2, '0')}`;

                while (existingCodes.includes(candidate)) {
                    sequence += 1;
                    candidate = `${base}_${String(sequence).padStart(2, '0')}`;
                }

                if (!codeInput.value || codeInput.dataset.autoGenerated === '1') {
                    codeInput.value = candidate;
                    codeInput.dataset.autoGenerated = '1';
                }
            };

            const syncOrganizations = () => {
                const applicationId = applicationSelect.value;
                const organizations = organizationsByApplication[applicationId] || [];
                const currentValue = organizationSelect.value;

                organizationSelect.innerHTML = ['<option value="">Type partage a toute l application</option>']
                    .concat(organizations.map((organization) => `<option value="${organization.id}">${organization.name}</option>`))
                    .join('');

                if (organizations.some((organization) => String(organization.id) === String(currentValue))) {
                    organizationSelect.value = currentValue;
                }

                suggestCode();
            };

            applicationSelect?.addEventListener('change', syncOrganizations);
            organizationSelect?.addEventListener('change', suggestCode);
            codeInput?.addEventListener('input', () => {
                codeInput.dataset.autoGenerated = '0';
            });
            syncOrganizations();
        });
    </script>
@endsection
