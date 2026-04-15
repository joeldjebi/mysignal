@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier plan abonnement')
@section('page-title', 'Modifier plan abonnement')
@section('page-description', 'Mettre a jour le plan annuel des usagers publics.')

@section('header-badges')
    <a href="{{ route('super-admin.subscription-plans.index') }}" class="btn btn-outline-secondary">Retour</a>
@endsection

@section('content')
    <section class="panel-card">
        <form method="POST" action="{{ route('super-admin.subscription-plans.update', $subscriptionPlan) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ old('code', $subscriptionPlan->code) }}" required></div>
            <div class="col-md-8"><label class="form-label">Nom</label><input class="form-control" name="name" value="{{ old('name', $subscriptionPlan->name) }}" required></div>
            <div class="col-md-4"><label class="form-label">Duree en mois</label><input class="form-control" type="number" min="1" max="120" name="duration_months" value="{{ old('duration_months', $subscriptionPlan->duration_months) }}" required></div>
            <div class="col-md-4"><label class="form-label">Prix</label><input class="form-control" type="number" min="0" name="price" value="{{ old('price', $subscriptionPlan->price) }}" required></div>
            <div class="col-md-4"><label class="form-label">Devise</label><input class="form-control" name="currency" value="{{ old('currency', $subscriptionPlan->currency) }}" required></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="3" name="description">{{ old('description', $subscriptionPlan->description) }}</textarea></div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActivePlan" @checked(old('is_active', $subscriptionPlan->is_active))>
                    <label class="form-check-label" for="isActivePlan">Plan actif</label>
                </div>
                <div class="small text-secondary">Un seul plan actif est autorise pour cette version.</div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.subscription-plans.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </section>
@endsection
