@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un type d usager public')
@section('page-title', 'Modifier un type d usager public')
@section('page-description', 'Mettre a jour le profil, la tarification et les exigences d un type d usager public.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $publicUserType->name }}</div>
        <form method="POST" action="{{ route('super-admin.public-user-types.update', $publicUserType) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ old('code', $publicUserType->code) }}" required></div>
            <div class="col-md-8"><label class="form-label">Nom</label><input class="form-control" name="name" value="{{ old('name', $publicUserType->name) }}" required></div>
            <div class="col-md-6">
                <label class="form-label">Type de profil</label>
                <select class="form-select" name="profile_kind" required>
                    <option value="individual" @selected(old('profile_kind', $publicUserType->profile_kind) === 'individual')>Particulier</option>
                    <option value="business" @selected(old('profile_kind', $publicUserType->profile_kind) === 'business')>Entreprise</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tarification associee</label>
                <select class="form-select" name="pricing_rule_id" required>
                    @foreach ($pricingRules as $pricingRule)
                        <option value="{{ $pricingRule->id }}" @selected((string) old('pricing_rule_id', $publicUserType->pricing_rule_id) === (string) $pricingRule->id)>{{ $pricingRule->label }} · {{ number_format($pricingRule->amount, 0, ',', ' ') }} {{ $pricingRule->currency }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Ordre</label><input class="form-control" type="number" min="1" max="999" name="sort_order" value="{{ old('sort_order', $publicUserType->sort_order) }}"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="3" name="description">{{ old('description', $publicUserType->description) }}</textarea></div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.public-user-types.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
