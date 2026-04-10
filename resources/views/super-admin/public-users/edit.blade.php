@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un usager public')
@section('page-title', 'Modifier un usager public')
@section('page-description', 'Mettre a jour un compte public particulier ou entreprise.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-1">Edition de {{ $publicUser->first_name }} {{ $publicUser->last_name }}</div>
        <div class="small text-secondary mb-3">Le formulaire s ajuste selon le type d usager public et sa tarification associee.</div>
        <form method="POST" action="{{ route('super-admin.public-users.update', $publicUser) }}" class="row g-3">
            @csrf
            @method('PUT')
            @include('super-admin.public-users.partials.form-fields', ['publicUser' => $publicUser, 'publicUserTypes' => $publicUserTypes, 'mode' => 'edit'])
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.public-users.show', $publicUser) }}" class="btn btn-outline-dark">Voir les signalements</a>
                <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection

@section('scripts')
    @include('super-admin.public-users.partials.form-script', ['mode' => 'edit'])
@endsection
