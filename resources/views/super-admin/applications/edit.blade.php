@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier une application')
@section('page-title', 'Modifier une application')
@section('page-description', 'Ajuster l identite, les textes et le positionnement d une application metier.')

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
            grid-template-columns: repeat(2, minmax(0, 1fr));
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
        }
        .feature-option .form-check {
            display: flex;
            gap: .8rem;
            align-items: flex-start;
            margin: 0;
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
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="panel-card h-100">
                <div class="small text-secondary fw-semibold mb-2">Application</div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset($application->logo_path ?: 'image/logo/logo-my-signal.png') }}" alt="Logo {{ $application->name }}" style="width:52px;height:52px;border-radius:16px;object-fit:contain;background:#fff;padding:.35rem;box-shadow:0 12px 24px rgba(16,42,67,.08);">
                    <div>
                        <div class="h5 fw-bold mb-1">{{ $application->name }}</div>
                        <div class="text-secondary small">{{ $application->code }} · {{ $application->slug }}</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="status-chip">{{ $application->status }}</span>
                    <span class="status-chip">Ordre {{ $application->sort_order }}</span>
                </div>
                <div class="text-secondary small mb-4">{{ $application->tagline ?: 'Aucun slogan renseigne.' }}</div>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="{{ route('super-admin.signal-types.index', ['application_id' => $application->id]) }}" class="btn btn-sm btn-outline-dark">Voir les types de signaux</a>
                    <a href="{{ route('super-admin.organizations.index', ['application_id' => $application->id]) }}" class="btn btn-sm btn-outline-dark">Voir les organisations</a>
                </div>
                <div class="vstack gap-3">
                    <div>
                        <div class="small text-secondary">Institutions</div>
                        <div class="fw-semibold">{{ $application->organizations_count }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Types de signaux</div>
                        <div class="fw-semibold">{{ $application->signal_types_count }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Signalements</div>
                        <div class="fw-semibold">{{ $application->incident_reports_count }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Fonctionnalites par defaut</div>
                        <div class="fw-semibold">{{ $application->features_count }}</div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Edition de l'application</div>
                <form method="POST" action="{{ route('super-admin.applications.update', $application) }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-4">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" value="{{ old('code', $application->code) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" value="{{ old('name', $application->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $application->slug) }}" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Slogan</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $application->tagline) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" min="1" max="999" name="sort_order" value="{{ old('sort_order', $application->sort_order) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description courte</label>
                        <input type="text" name="short_description" value="{{ old('short_description', $application->short_description) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description longue</label>
                        <textarea name="long_description" class="form-control" rows="4">{{ old('long_description', $application->long_description) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chemin logo</label>
                        <input type="text" name="logo_path" value="{{ old('logo_path', $application->logo_path) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chemin image hero</label>
                        <input type="text" name="hero_image_path" value="{{ old('hero_image_path', $application->hero_image_path) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Couleur primaire</label>
                        <input type="text" name="primary_color" value="{{ old('primary_color', $application->primary_color) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Couleur secondaire</label>
                        <input type="text" name="secondary_color" value="{{ old('secondary_color', $application->secondary_color) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Couleur accent</label>
                        <input type="text" name="accent_color" value="{{ old('accent_color', $application->accent_color) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Fonctionnalites par defaut de l'application</label>
                        <div class="small text-secondary mb-3">Ces fonctionnalites sont automatiquement ouvertes aux organisations de cette application, sauf desactivation explicite au niveau d'une organisation.</div>
                        <div class="feature-picker">
                            @foreach ($groupedFeatures as $groupLabel => $groupFeatures)
                                <section class="feature-picker-group">
                                    <div class="small text-uppercase fw-bold text-secondary mb-3">{{ $groupLabel }}</div>
                                    <div class="feature-picker-grid">
                                        @foreach ($groupFeatures as $feature)
                                            <label for="application-feature-edit-{{ $feature->id }}" class="feature-option">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="{{ $feature->id }}" name="feature_ids[]" id="application-feature-edit-{{ $feature->id }}" @checked(in_array($feature->id, old('feature_ids', $application->features->pluck('id')->all())))>
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
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Enregistrer</button>
                        <a href="{{ route('super-admin.applications.index') }}" class="btn btn-outline-secondary">Retour</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
