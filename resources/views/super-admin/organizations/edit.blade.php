@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier une organisation')
@section('page-title', 'Modifier une organisation')
@section('page-description', 'Mettre a jour le portail et les informations d une organisation.')

@section('content')
    <style>
        .feature-picker {
            display: grid;
            gap: 1rem;
        }
        .feature-picker-group {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 20px;
            background: rgba(248,250,252,.9);
            padding: .95rem;
        }
        .feature-picker-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .8rem;
        }
        .feature-option {
            display: block;
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 18px;
            background: #fff;
            padding: .95rem;
            min-height: 100%;
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
            gap: .8rem;
            align-items: flex-start;
            margin: 0;
        }
        .feature-option .form-check-input {
            margin-top: .18rem;
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
            margin-top: .2rem;
        }
        .org-edit-shell {
            display: grid;
            gap: 1.2rem;
        }
        @media (max-width: 1199.98px) {
            .feature-picker-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767.98px) {
            .feature-picker-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $organization->name }}</div>
        <form method="POST" action="{{ route('super-admin.organizations.update', $organization) }}" class="org-edit-shell">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Application</label>
                    <select name="application_id" class="form-select">
                        <option value="">Aucune application liee</option>
                        @foreach ($applications as $application)
                            <option value="{{ $application->id }}" @selected(old('application_id', $organization->application_id) == $application->id)>{{ $application->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type d'organisation</label>
                    <select name="organization_type_id" class="form-select" required>
                        @foreach ($organizationTypes as $organizationType)
                            <option value="{{ $organizationType->id }}" @selected(old('organization_type_id', $organization->organization_type_id) == $organizationType->id)>{{ $organizationType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" value="{{ old('code', $organization->code) }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cle portail</label>
                    <input type="text" name="portal_key" value="{{ old('portal_key', $organization->portal_key) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    @include('partials.phone-field', ['value' => old('phone', $organization->phone), 'placeholder' => '0700000000'])
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" value="{{ old('name', $organization->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $organization->email) }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $organization->description) }}</textarea>
                </div>
            </div>
            <div>
                <label class="form-label">Fonctionnalites de l'institution</label>
                <div class="small text-secondary mb-3">Les fonctionnalites heritees de l'application peuvent etre laissees actives ou desactivees pour cette institution precisement.</div>
                <div class="feature-picker">
                    @foreach ($groupedFeatures as $groupLabel => $groupFeatures)
                        <section class="feature-picker-group">
                            <div class="small text-uppercase fw-bold text-secondary mb-3">{{ $groupLabel }}</div>
                            <div class="feature-picker-grid">
                                @foreach ($groupFeatures as $feature)
                                    <label for="organization-feature-edit-{{ $feature->id }}" class="feature-option">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $feature->id }}" name="feature_ids[]" id="organization-feature-edit-{{ $feature->id }}" @checked(in_array($feature->id, old('feature_ids', $organization->resolvedFeatureIds())))>
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
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.organizations.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
