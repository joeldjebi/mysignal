@extends('super-admin.layouts.app')

@php
    $portalTitles = [
        'huissier' => 'Mes dossiers de constat',
        'aoda' => 'Dossiers ordre des avocats',
        'avocat' => 'Mes dossiers judiciaires',
    ];
@endphp

@section('title', config('app.name').' | '.($portalTitles[$portal] ?? 'Dossiers'))
@section('page-title', $portalTitles[$portal] ?? 'Dossiers contentieux')
@section('page-description', 'Suivi operationnel des dossiers contentieux selon votre role.')

@section('header-badges')
    <span class="badge-soft">{{ $cases->total() }} dossier{{ $cases->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-9">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference dossier, signalement, usager...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('backoffice.legal-cases.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Dossier</th>
                        <th>Victime</th>
                        <th>Organisation</th>
                        <th>Huissier</th>
                        <th>Avocat</th>
                        <th>Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cases as $case)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $case->reference }}</div>
                                <div class="small text-secondary">{{ $case->incidentReport?->reference ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ trim(($case->publicUser?->first_name ?? '').' '.($case->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $case->publicUser?->phone ?: '-' }}</div>
                            </td>
                            <td>{{ $case->organization?->name ?: '-' }}</td>
                            <td>
                                <div>{{ $case->bailiff?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $case->bailiff_completed_at ? 'Constat termine' : 'En cours' }}</div>
                            </td>
                            <td>
                                <div>{{ $case->lawyer?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $case->lawyer_completed_at ? 'Procedure terminee' : ($case->lawyer_assigned_at ? 'Attribue' : 'Non attribue') }}</div>
                            </td>
                            <td><span class="status-chip">{{ $case->status }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('backoffice.legal-cases.show', $case) }}" class="btn btn-sm btn-outline-dark">Ouvrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary">Aucun dossier disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $cases->currentPage() }} sur {{ $cases->lastPage() }}</div>
            {{ $cases->links() }}
        </div>
    </section>
@endsection
