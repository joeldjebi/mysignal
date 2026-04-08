@php
    $defaultPublicUserType = $publicUserTypes->firstWhere('code', 'UP') ?? $publicUserTypes->first();
    $selectedTypeId = old('public_user_type_id', $publicUser?->public_user_type_id ?? $defaultPublicUserType?->id);
    $selectedType = $publicUserTypes->firstWhere('id', (int) $selectedTypeId) ?? $defaultPublicUserType;
    $selectedPricingRule = $selectedType?->pricingRule;
    $isBusinessProfile = $selectedType?->profile_kind === 'business';
@endphp

<style>
    .public-user-form-shell {
        display: grid;
        gap: 1.1rem;
    }
    .public-user-form-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, .9fr);
        gap: 1rem;
        align-items: start;
    }
    .public-user-section {
        border: 1px solid rgba(16,42,67,.08);
        border-radius: 22px;
        background: rgba(255,255,255,.92);
        padding: 1.05rem;
    }
    .public-user-section-title {
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--acepen-muted);
        font-weight: 800;
        margin-bottom: .85rem;
    }
    .public-user-summary {
        border: 1px solid rgba(16,42,67,.08);
        border-radius: 24px;
        background: linear-gradient(145deg, rgba(12,36,53,.96), rgba(30,88,119,.94));
        color: #fff;
        padding: 1.1rem;
        box-shadow: 0 18px 34px rgba(16,42,67,.12);
    }
    .public-user-summary-kicker {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: rgba(255,255,255,.66);
        font-weight: 800;
        margin-bottom: .45rem;
    }
    .public-user-summary-title {
        font-size: 1.2rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: .35rem;
    }
    .public-user-summary-copy {
        color: rgba(255,255,255,.76);
        font-size: .92rem;
        line-height: 1.6;
    }
    .public-user-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .8rem;
        margin-top: 1rem;
    }
    .public-user-summary-box {
        border-radius: 18px;
        background: rgba(255,255,255,.09);
        border: 1px solid rgba(255,255,255,.12);
        padding: .85rem;
    }
    .public-user-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: rgba(255,255,255,.62);
        font-weight: 800;
        margin-bottom: .25rem;
    }
    .public-user-hint {
        border-radius: 16px;
        background: rgba(196,155,72,.1);
        border: 1px solid rgba(196,155,72,.18);
        color: #6d5418;
        padding: .8rem .9rem;
        font-size: .88rem;
        line-height: 1.55;
    }
    .business-fields-hidden {
        display: none;
    }
    @media (max-width: 991.98px) {
        .public-user-form-hero {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767.98px) {
        .public-user-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="col-12 public-user-form-shell">
    <div class="public-user-form-hero">
        <section class="public-user-section">
            <div class="public-user-section-title">Profil cible</div>
            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label">Type d usager public</label>
                    <select name="public_user_type_id" class="form-select" id="publicUserTypeSelect-{{ $mode }}" required>
                        <option value="">Selectionner</option>
                        @foreach ($publicUserTypes as $publicUserType)
                            <option
                                value="{{ $publicUserType->id }}"
                                data-profile-kind="{{ $publicUserType->profile_kind }}"
                                data-type-name="{{ $publicUserType->name }}"
                                data-pricing-label="{{ $publicUserType->pricingRule?->label }}"
                                data-pricing-amount="{{ $publicUserType->pricingRule ? number_format($publicUserType->pricingRule->amount, 0, ',', ' ') . ' ' . $publicUserType->pricingRule->currency : '' }}"
                                @selected((string) $selectedTypeId === (string) $publicUserType->id)
                            >
                                {{ $publicUserType->name }} · {{ $publicUserType->pricingRule?->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Numero WhatsApp</label>
                    <select class="form-select" name="is_whatsapp_number">
                        <option value="0" @selected((string) old('is_whatsapp_number', (int) ($publicUser?->is_whatsapp_number ?? false)) === '0')>Non</option>
                        <option value="1" @selected((string) old('is_whatsapp_number', (int) ($publicUser?->is_whatsapp_number ?? false)) === '1')>Oui</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="public-user-hint" id="publicUserTypeHint-{{ $mode }}">
                        Selectionnez le type d usager public. Le formulaire adapte automatiquement les champs attendus, notamment pour une entreprise.
                    </div>
                </div>
            </div>
        </section>

        <aside class="public-user-summary">
            <div class="public-user-summary-kicker">Synthese</div>
            <div class="public-user-summary-title" id="publicUserSummaryType-{{ $mode }}">{{ $selectedType?->name ?: 'Type non selectionne' }}</div>
            <div class="public-user-summary-copy" id="publicUserSummaryProfile-{{ $mode }}">
                {{ $selectedType?->profile_kind === 'business' ? 'Compte entreprise avec informations juridiques et administratives.' : 'Compte particulier avec informations personnelles simplifiees.' }}
            </div>
            <div class="public-user-summary-grid">
                <div class="public-user-summary-box">
                    <div class="public-user-summary-label">Tarification</div>
                    <div class="fw-semibold" id="publicUserSummaryPricingLabel-{{ $mode }}">{{ $selectedPricingRule?->label ?: '-' }}</div>
                    <div class="small text-white-50 mt-1" id="publicUserSummaryPricingAmount-{{ $mode }}">{{ $selectedPricingRule ? number_format($selectedPricingRule->amount, 0, ',', ' ') . ' ' . $selectedPricingRule->currency : '-' }}</div>
                </div>
                <div class="public-user-summary-box">
                    <div class="public-user-summary-label">Mode de profil</div>
                    <div class="fw-semibold" id="publicUserSummaryKind-{{ $mode }}">{{ $selectedType?->profile_kind === 'business' ? 'Entreprise' : 'Particulier' }}</div>
                    <div class="small text-white-50 mt-1">Le formulaire s ajuste automatiquement.</div>
                </div>
            </div>
        </aside>
    </div>

    <section class="public-user-section">
        <div class="public-user-section-title">Identite</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Prenom</label>
                <input class="form-control" name="first_name" value="{{ old('first_name', $publicUser?->first_name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nom</label>
                <input class="form-control" name="last_name" value="{{ old('last_name', $publicUser?->last_name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Commune</label>
                <select class="form-select" name="commune" required>
                    <option value="">Selectionner une commune</option>
                    @foreach ($communes as $commune)
                        <option value="{{ $commune->name }}" @selected(old('commune', $publicUser?->commune) === $commune->name)>{{ $commune->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="public-user-section">
        <div class="public-user-section-title">Contact</div>
        <div class="row g-3">
            <div class="col-md-4">
                @include('partials.phone-field', ['value' => old('phone', $publicUser?->phone), 'placeholder' => '0700000000'])
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="{{ old('email', $publicUser?->email) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ $publicUser ? 'Nouveau mot de passe' : 'Mot de passe' }}</label>
                <input class="form-control" type="password" name="password" @required(! $publicUser) placeholder="{{ $publicUser ? 'Laisser vide pour conserver' : 'Minimum 8 caracteres' }}">
            </div>
            <div class="col-12">
                <label class="form-label">Adresse</label>
                <input class="form-control" name="address" value="{{ old('address', $publicUser?->address) }}" placeholder="Adresse principale ou adresse de contact">
            </div>
        </div>
    </section>

    <section class="public-user-section {{ $isBusinessProfile ? '' : 'business-fields-hidden' }}" id="publicUserBusinessFields-{{ $mode }}">
        <div class="public-user-section-title">Informations Entreprise</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Raison sociale</label>
                <input class="form-control" name="company_name" value="{{ old('company_name', $publicUser?->company_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">RCCM / Immatriculation</label>
                <input class="form-control" name="company_registration_number" value="{{ old('company_registration_number', $publicUser?->company_registration_number) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Identifiant fiscal</label>
                <input class="form-control" name="tax_identifier" value="{{ old('tax_identifier', $publicUser?->tax_identifier) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Secteur d activite</label>
                <select class="form-select" name="business_sector">
                    <option value="">Selectionner un secteur</option>
                    @foreach ($businessSectors as $businessSector)
                        <option value="{{ $businessSector->name }}" @selected(old('business_sector', $publicUser?->business_sector) === $businessSector->name)>{{ $businessSector->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Adresse de l entreprise</label>
                <input class="form-control" name="company_address" value="{{ old('company_address', $publicUser?->company_address) }}">
            </div>
        </div>
    </section>
</div>
