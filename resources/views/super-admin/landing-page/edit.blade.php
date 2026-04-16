@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Landing page')
@section('page-title', 'Landing page')
@section('page-description', 'Mettre a jour totalement le contenu public affiche sur la page d accueil.')

@section('header-badges')
    <span class="badge-soft">{{ $landingPage->status === 'active' ? 'Version SA active' : 'Design par defaut actif' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('super-admin.landing-page.update') }}" class="row g-4">
        @csrf
        @method('PUT')

        <div class="col-lg-8">
            <section class="panel-card">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="fw-bold">Contenu HTML complet</div>
                        <div class="small text-secondary">
                            Colle ici le HTML de la landing. Si le champ est vide ou desactive, MySignal affiche le design par defaut.
                        </div>
                    </div>
                    <a href="{{ route('public.landing') }}" target="_blank" class="btn btn-outline-dark btn-sm">Previsualiser</a>
                </div>

                <div class="mb-3">
                    <label class="form-label">Titre navigateur</label>
                    <input class="form-control" name="title" value="{{ old('title', $landingPage->title ?: 'MySignal - Plateforme de signalement consommateur') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description interne</label>
                    <input class="form-control" name="subtitle" value="{{ old('subtitle', $landingPage->subtitle) }}" placeholder="Ex: landing campagne abonnement UP">
                </div>

                <div class="mb-3">
                    <label class="form-label">HTML de la landing</label>
                    <textarea class="form-control font-monospace" name="body" rows="28" spellcheck="false" placeholder="<!DOCTYPE html>...">{{ old('body', $landingPage->body) }}</textarea>
                    <div class="small text-secondary mt-2">
                        Variables disponibles: <code>@{{ logo_url }}</code>, <code>@{{ app_name }}</code>, <code>@{{ primary_color }}</code>, <code>@{{ secondary_color }}</code>, <code>@{{ accent_color }}</code>.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-dark">Enregistrer la landing</button>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Publication</div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="landingActive" @checked(old('is_active', $landingPage->status === 'active'))>
                    <label class="form-check-label" for="landingActive">Activer la version SA</label>
                </div>
                <div class="alert alert-warning small">
                    Quand la version SA est active, le HTML ci-contre remplace totalement la landing par defaut.
                </div>

                <div class="fw-bold mb-3 mt-4">Couleurs MySignal</div>
                @php
                    $meta = $landingPage->meta ?? [];
                    $colors = [
                        'primary_color' => ['Primaire', '#183447'],
                        'secondary_color' => ['Secondaire', '#256f8f'],
                        'accent_color' => ['Accent', '#ff0068'],
                    ];
                @endphp
                @foreach ($colors as $field => [$label, $default])
                    <div class="mb-3">
                        <label class="form-label">{{ $label }}</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" value="{{ old($field, $meta[$field] ?? $default) }}" onchange="this.nextElementSibling.value = this.value">
                            <input class="form-control" name="{{ $field }}" value="{{ old($field, $meta[$field] ?? $default) }}" pattern="#[0-9A-Fa-f]{6}">
                        </div>
                    </div>
                @endforeach

                <div class="fw-bold mb-2 mt-4">Logo</div>
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $defaultLogoUrl }}" alt="MySignal" style="width:64px;height:64px;object-fit:contain;border-radius:16px;background:white;padding:.35rem;border:1px solid rgba(16,42,67,.08)">
                    <div class="small text-secondary">
                        Utilise <code>@{{ logo_url }}</code> dans le HTML pour afficher le logo officiel.
                    </div>
                </div>
            </section>
        </div>
    </form>
@endsection
