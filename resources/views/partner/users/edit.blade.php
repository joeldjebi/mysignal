@extends('partner.layouts.app')

@section('title', config('app.name').' | Modifier un user partenaire')
@section('page-title', 'Modifier un user partenaire')
@section('page-description', 'Mettez a jour les acces web ou mobile de votre equipe partenaire.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $userAccount->name }}</div>
        <form method="POST" action="{{ route('partner.users.update', $userAccount) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-5">
                <label class="form-label">Nom complet</label>
                <input type="text" name="name" value="{{ old('name', $userAccount->name) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $userAccount->email) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                @include('partials.phone-field', ['value' => old('phone', $userAccount->phone), 'placeholder' => '0700000000'])
            </div>
            <div class="col-md-4">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas changer">
            </div>
            <div class="col-md-4">
                <label class="form-label">Role</label>
                <select name="role_code" class="form-select" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->code }}" @selected(old('role_code', $userAccount->roles->pluck('code')->first()) === $role->code)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected(old('status', $userAccount->status) === 'active')>Actif</option>
                    <option value="inactive" @selected(old('status', $userAccount->status) === 'inactive')>Inactif</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('partner.users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
