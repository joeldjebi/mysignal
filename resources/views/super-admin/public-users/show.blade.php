@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Signalements de '.$publicUser->first_name.' '.$publicUser->last_name)
@section('page-title', 'Signalements de l usager public')
@section('page-description', 'Consulter les signalements d un usager public et ouvrir des dossiers de reparation si necessaire.')

@section('content')
    <section class="panel-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="fw-bold mb-1">{{ $publicUser->first_name }} {{ $publicUser->last_name }}</div>
                <div class="small text-secondary">{{ $publicUser->phone }}{{ $publicUser->email ? ' · '.$publicUser->email : '' }}</div>
                <div class="small text-secondary mt-1">{{ $publicUser->publicUserType?->name ?: '-' }} · {{ $publicUser->commune ?: 'Commune non renseignee' }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('super-admin.public-users.edit', $publicUser) }}" class="btn btn-outline-dark">Modifier le compte</a>
                <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </div>
    </section>

    @include('super-admin.public-users.partials.reports-section', ['publicUser' => $publicUser])
@endsection
