@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Nouvel usager public')
@section('page-title', 'Nouvel usager public')
@section('page-description', 'Creer un compte public particulier ou entreprise avec un formulaire dynamique et guide.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-1">Creation d un usager public</div>
        <div class="small text-secondary mb-3">Le formulaire s ajuste selon le type d usager public et la tarification associee.</div>
        <form method="POST" action="{{ route('super-admin.public-users.store') }}" class="row g-3">
            @csrf
            @include('super-admin.public-users.partials.form-fields', ['publicUser' => null, 'publicUserTypes' => $publicUserTypes, 'mode' => 'create'])
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Creer</button>
                <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection

@section('scripts')
    @include('super-admin.public-users.partials.form-script', ['mode' => 'create'])
@endsection
