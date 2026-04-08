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
        $applicationCodeByIdPayload = $applications->mapWithKeys(fn ($application) => [
            $application->id => $application->code,
        ]);
    @endphp
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="small text-secondary fw-semibold mb-2">Type de signal</div>
                <div class="h5 fw-bold mb-1">{{ $signalType->label }}</div>
                <div class="text-secondary small mb-4">{{ $signalType->code }} · {{ $signalType->application?->name ?: 'Sans application' }} · {{ $signalType->organization?->name ?: 'Type partage' }}</div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="status-chip">{{ $signalType->status }}</span>
                    <span class="status-chip">{{ $signalType->default_sla_hours ? $signalType->default_sla_hours.' h' : 'Sans SLA defaut' }}</span>
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
                        <label class="form-label">Application</label>
                        <select name="application_id" class="form-select" id="saSignalTypeApplicationEdit" required>
                            <option value="">Choisir une application</option>
                            @foreach ($applications as $application)
                                <option value="{{ $application->id }}" @selected(old('application_id', $signalType->application_id) == $application->id)>{{ $application->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Organisation</label>
                        <select name="organization_id" class="form-select" id="saSignalTypeOrganizationEdit">
                            <option value="">Type partage a toute l application</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Code signal</label>
                        <input type="text" name="code" value="{{ old('code', $signalType->code) }}" class="form-control" id="saSignalTypeCodeEdit" required>
                        <div class="small text-secondary mt-2">Le code peut etre regenere automatiquement selon l application et l organisation, puis ajuste manuellement si besoin.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SLA par defaut (heures)</label>
                        <input type="number" min="1" max="999" name="default_sla_hours" value="{{ old('default_sla_hours', $signalType->default_sla_hours) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Libelle</label>
                        <input type="text" name="label" value="{{ old('label', $signalType->label) }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $signalType->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        @include('partials.signal-type-field-builder', ['builderId' => 'sa-signal-type-edit', 'fields' => old() ? collect(old('field_keys', []))->map(function ($key, $index) {
                            return [
                                'key' => $key,
                                'label' => old('field_labels.'.$index),
                                'type' => old('field_types.'.$index, 'text'),
                                'required' => in_array((string) $index, array_map('strval', old('field_required', [])), true),
                            ];
                        })->all() : ($signalType->data_fields ?? [])])
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Enregistrer</button>
                        <a href="{{ route('super-admin.signal-types.index') }}" class="btn btn-outline-secondary">Retour</a>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const organizationsByApplication = @json($organizationsByApplicationPayload);
            const applicationCodes = @json($applicationCodeByIdPayload);
            const existingCodes = @json($existingSignalTypeCodes);

            const applicationSelect = document.getElementById('saSignalTypeApplicationEdit');
            const organizationSelect = document.getElementById('saSignalTypeOrganizationEdit');
            const selectedOrganizationId = @json((string) old('organization_id', $signalType->organization_id));
            const codeInput = document.getElementById('saSignalTypeCodeEdit');

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
                const currentValue = organizationSelect.dataset.currentValue || selectedOrganizationId;

                organizationSelect.innerHTML = ['<option value="">Type partage a toute l application</option>']
                    .concat(organizations.map((organization) => `<option value="${organization.id}">${organization.name}</option>`))
                    .join('');

                if (organizations.some((organization) => String(organization.id) === String(currentValue))) {
                    organizationSelect.value = String(currentValue);
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
