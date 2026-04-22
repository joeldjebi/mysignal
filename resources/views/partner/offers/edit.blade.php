@extends('partner.layouts.app')

@section('title', config('app.name').' | Modifier une offre')
@section('page-title', 'Modifier une offre')
@section('page-description', 'Ajustez les regles de reduction qui seront disponibles dans l application mobile partenaire.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $offer->name }}</div>
        <form method="POST" action="{{ route('partner.offers.update', $offer) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $offer->code) }}" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $offer->name) }}" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $offer->description) }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="discount_type" class="form-select" required>
                    <option value="percentage" @selected(old('discount_type', $offer->discount_type) === 'percentage')>Pourcentage</option>
                    <option value="fixed_amount" @selected(old('discount_type', $offer->discount_type) === 'fixed_amount')>Montant fixe</option>
                    <option value="custom" @selected(old('discount_type', $offer->discount_type) === 'custom')>Custom</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Valeur</label>
                <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', $offer->discount_value) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Devise</label>
                <input type="text" name="currency" value="{{ old('currency', $offer->currency) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select" required>
                    <option value="draft" @selected(old('status', $offer->status) === 'draft')>Brouillon</option>
                    <option value="active" @selected(old('status', $offer->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $offer->status) === 'inactive')>Inactive</option>
                    <option value="archived" @selected(old('status', $offer->status) === 'archived')>Archivee</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Achat minimum</label>
                <input type="number" step="0.01" min="0" name="minimum_purchase_amount" value="{{ old('minimum_purchase_amount', $offer->minimum_purchase_amount) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Reduction max</label>
                <input type="number" step="0.01" min="0" name="maximum_discount_amount" value="{{ old('maximum_discount_amount', $offer->maximum_discount_amount) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max usages / carte</label>
                <input type="number" min="1" name="max_uses_per_card" value="{{ old('max_uses_per_card', $offer->max_uses_per_card) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max usages / jour</label>
                <input type="number" min="1" name="max_uses_per_day" value="{{ old('max_uses_per_day', $offer->max_uses_per_day) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Debut</label>
                <input type="date" name="starts_at" value="{{ old('starts_at', optional($offer->starts_at)->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Fin</label>
                <input type="date" name="ends_at" value="{{ old('ends_at', optional($offer->ends_at)->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('partner.offers.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
