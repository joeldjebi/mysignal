@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un admin institutionnel')
@section('page-title', 'Modifier un admin institutionnel')
@section('page-description', 'Mettre a jour un compte admin racine de portail institutionnel.')

@section('content')
    <style>
        .feature-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }
        .feature-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            padding: .85rem;
            height: 100%;
            background: #fff;
        }
        .feature-card.is-disabled {
            opacity: .55;
            background: rgba(148, 163, 184, 0.08);
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
    @php
        $selectedOrganization = $organizations->firstWhere('id', (int) old('organization_id', $institutionAdmin->organization_id));
        $allowedFeatureIds = collect($selectedOrganization?->resolvedFeatureIds() ?? $institutionAdmin->organization?->resolvedFeatureIds() ?? [])
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $selectedFeatureIds = old('feature_ids', $institutionAdmin->features->pluck('id')->all() ?: $allowedFeatureIds);
    @endphp
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $institutionAdmin->name }}</div>
        <form method="POST" action="{{ route('super-admin.institution-admins.update', $institutionAdmin) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Organisation</label>
                <select name="organization_id" class="form-select" id="institutionAdminEditOrganizationSelect" required>
                    @foreach ($organizations as $organization)
                        <option value="{{ $organization->id }}" data-feature-ids="{{ implode(',', $organization->resolvedFeatureIds()) }}" @selected(old('organization_id', $institutionAdmin->organization_id) == $organization->id)>{{ $organization->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nom complet</label>
                <input type="text" name="name" value="{{ old('name', $institutionAdmin->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $institutionAdmin->email) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                @include('partials.phone-field', ['value' => old('phone', $institutionAdmin->phone), 'placeholder' => '0700000000'])
            </div>
            <div class="col-md-6">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas changer">
            </div>
            <div class="col-12">
                <label class="form-label">Fonctionnalites attribuees</label>
                <div class="small text-secondary mb-2">Seules les fonctionnalites ouvertes pour l'organisation peuvent etre attribuees. Vous pouvez limiter l'AI a un sous-ensemble.</div>
                <div class="small text-secondary mb-3"><span id="institutionAdminEditFeatureCount">{{ count(array_intersect($selectedFeatureIds, $allowedFeatureIds)) }}</span> fonctionnalite(s) active(s) pour cet AI.</div>
                <div class="border rounded-3 p-3" style="max-height: 240px; overflow:auto;">
                    <div class="feature-grid-3">
                        @foreach ($features as $feature)
                            @php
                                $isAllowedFeature = in_array((int) $feature->id, $allowedFeatureIds, true);
                            @endphp
                            <div>
                                <div class="feature-card {{ $isAllowedFeature ? '' : 'is-disabled' }}">
                                    <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="{{ $feature->id }}"
                                        name="feature_ids[]"
                                        id="feature-edit-{{ $feature->id }}"
                                        data-allowed="{{ $isAllowedFeature ? '1' : '0' }}"
                                        @checked(in_array($feature->id, $selectedFeatureIds))
                                        @disabled(! $isAllowedFeature)
                                    >
                                    <label class="form-check-label" for="feature-edit-{{ $feature->id }}">
                                        <span class="d-block">{{ $feature->name }}</span>
                                        <span class="small text-muted d-block">{{ $feature->code }}</span>
                                        @if ($feature->description)
                                            <span class="small text-secondary">{{ $feature->description }}</span>
                                        @endif
                                        @unless ($isAllowedFeature)
                                            <span class="small text-secondary d-block mt-1">Non disponible pour cette organisation.</span>
                                        @endunless
                                    </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.institution-admins.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const organizationSelect = document.getElementById('institutionAdminEditOrganizationSelect');

            if (!organizationSelect) {
                return;
            }

            const applyOrganizationDefaults = () => {
                const selectedOption = organizationSelect.options[organizationSelect.selectedIndex];
                const featureIds = (selectedOption?.dataset.featureIds || '')
                    .split(',')
                    .map((value) => value.trim())
                    .filter(Boolean);
                const featureInputs = document.querySelectorAll('input[name="feature_ids[]"]');
                const featureCount = document.getElementById('institutionAdminEditFeatureCount');

                featureInputs.forEach((input) => {
                    const allowed = featureIds.includes(input.value);
                    input.checked = allowed;
                    input.disabled = !allowed;
                    input.dataset.allowed = allowed ? '1' : '0';
                    input.closest('.feature-card')?.classList.toggle('is-disabled', !allowed);
                });

                if (featureCount) {
                    featureCount.textContent = featureIds.length;
                }
            };

            document.querySelectorAll('input[name="feature_ids[]"]').forEach((input) => {
                input.addEventListener('change', () => {
                    const featureCount = document.getElementById('institutionAdminEditFeatureCount');

                    if (!featureCount) {
                        return;
                    }

                    featureCount.textContent = document.querySelectorAll('input[name="feature_ids[]"]:checked').length;
                });
            });

            organizationSelect.addEventListener('change', applyOrganizationDefaults);
        });
    </script>
@endsection
