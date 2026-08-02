@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Mise à jour mobile')
@section('page-title', 'Mise à jour mobile')
@section('page-description', 'Définir les versions disponibles et les messages affichés dans les applications mobiles.')

@section('header-badges')
    <span class="badge-soft">Android {{ $setting->latest_version_android }}</span>
    <span class="badge-soft">iOS {{ $setting->latest_version_ios }}</span>
    <span class="badge-soft">{{ ucfirst($setting->update_type) }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <div class="fw-bold">Configuration utilisée par l’application mobile</div>
                <div class="text-secondary small">Les applications lisent cette configuration via l’API publique. Seuls les SA peuvent la modifier ici.</div>
            </div>
            <a href="{{ url('/api/v1/public/app-update') }}" target="_blank" rel="noopener" class="btn btn-outline-primary align-self-start">Voir la réponse API</a>
        </div>

        <form method="POST" action="{{ route('super-admin.mobile-app-update.update') }}" class="row g-4">
            @csrf
            @method('PUT')

            <div class="col-lg-4">
                <label class="form-label">Nom de l’application <span class="text-danger">*</span></label>
                <input type="text" name="app_name" value="{{ old('app_name', $setting->app_name) }}" class="form-control" required>
            </div>
            <div class="col-lg-4">
                <label class="form-label">Type de mise à jour <span class="text-danger">*</span></label>
                <select name="update_type" class="form-select" required>
                    <option value="minor" @selected(old('update_type', $setting->update_type) === 'minor')>Mineure</option>
                    <option value="major" @selected(old('update_type', $setting->update_type) === 'major')>Recommandée</option>
                    <option value="urgent" @selected(old('update_type', $setting->update_type) === 'urgent')>Obligatoire</option>
                </select>
                <div class="small text-secondary mt-1">Choisir “Obligatoire” pour forcer la mise à jour côté mobile.</div>
            </div>

            <div class="col-12">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="fw-bold mb-3">Android</div>
                            <div class="mb-3">
                                <label class="form-label">Dernière version <span class="text-danger">*</span></label>
                                <input type="text" name="latest_version_android" value="{{ old('latest_version_android', $setting->latest_version_android) }}" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Numéro de build <span class="text-danger">*</span></label>
                                <input type="number" min="1" name="build_version_android" value="{{ old('build_version_android', $setting->build_version_android) }}" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Lien Play Store <span class="text-danger">*</span></label>
                                <input type="url" name="play_store_url" value="{{ old('play_store_url', $setting->play_store_url) }}" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="fw-bold mb-3">iOS</div>
                            <div class="mb-3">
                                <label class="form-label">Dernière version <span class="text-danger">*</span></label>
                                <input type="text" name="latest_version_ios" value="{{ old('latest_version_ios', $setting->latest_version_ios) }}" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Numéro de build <span class="text-danger">*</span></label>
                                <input type="number" min="1" name="build_version_ios" value="{{ old('build_version_ios', $setting->build_version_ios) }}" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Lien App Store <span class="text-danger">*</span></label>
                                <input type="url" name="app_store_url" value="{{ old('app_store_url', $setting->app_store_url) }}" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="fw-bold mb-3">Messages affichés dans l’application</div>
                <div class="row g-4">
                    @foreach (['minor' => 'Mise à jour mineure', 'major' => 'Mise à jour recommandée', 'urgent' => 'Mise à jour obligatoire'] as $type => $label)
                        <div class="col-lg-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="fw-bold mb-3">{{ $label }}</div>
                                <div class="mb-3">
                                    <label class="form-label">Titre <span class="text-danger">*</span></label>
                                    <input type="text" name="messages[{{ $type }}][title]" value="{{ old('messages.'.$type.'.title', $messages[$type]['title'] ?? '') }}" class="form-control" required>
                                </div>
                                <div>
                                    <label class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea name="messages[{{ $type }}][message]" rows="5" class="form-control" required>{{ old('messages.'.$type.'.message', $messages[$type]['message'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.dashboard') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
