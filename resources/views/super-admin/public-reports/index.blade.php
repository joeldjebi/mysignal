@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Signalements publics')
@section('page-title', 'Signalements des usagers publics')
@section('page-description', 'Consulter la liste des signalements effectues par les usagers publics, filtrer par statut et acceder rapidement au compte usager ou au dossier associe.')

@section('header-badges')
    <span class="badge-soft">{{ $reports->total() }} signalement{{ $reports->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des signalements publics</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, usager, signalement, organisation...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['submitted' => 'Soumis', 'in_progress' => 'En cours', 'resolved' => 'Resolus', 'rejected' => 'Rejetes'] as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Application</label>
                    <select name="application_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($applications as $application)
                            <option value="{{ $application->id }}" @selected((string) request('application_id') === (string) $application->id)>{{ $application->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Organisation</label>
                    <select name="organization_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Dommage</label>
                    <select name="damage" class="form-select">
                        <option value="">Tous</option>
                        <option value="with_damage" @selected(request('damage') === 'with_damage')>Avec dommage</option>
                        <option value="without_damage" @selected(request('damage') === 'without_damage')>Sans dommage</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Dossier</label>
                    <select name="reparation_case" class="form-select">
                        <option value="">Tous</option>
                        <option value="opened" @selected(request('reparation_case') === 'opened')>Dossier ouvert</option>
                        <option value="missing" @selected(request('reparation_case') === 'missing')>Sans dossier</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.public-reports.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $reports->total() }} resultat{{ $reports->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Signalement</th>
                        <th>Usager public</th>
                        <th>Application / Organisation</th>
                        <th>Localisation</th>
                        <th>Dommage</th>
                        <th>Dossier</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $report->reference }}</div>
                                <div class="small text-secondary">{{ $report->signal_label ?: $report->signal_code ?: $report->incident_type }}</div>
                                <div class="small text-secondary">{{ $report->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="mt-1"><span class="status-chip">{{ $report->status }}</span></div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ trim(($report->publicUser?->first_name ?? '').' '.($report->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->publicUser?->publicUserType?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $report->application?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->organization?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $report->commune?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->address ?: 'Adresse non renseignee' }}</div>
                            </td>
                            <td>
                                <span class="status-chip">{{ $report->damage_declared_at ? 'Declare' : 'Aucun' }}</span>
                                @if ($report->damage_declared_at)
                                    <div class="small text-secondary mt-1">{{ $report->damage_declared_at?->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($report->reparationCase)
                                    <div class="fw-semibold">{{ $report->reparationCase->reference }}</div>
                                    <div class="small text-secondary">{{ $report->reparationCase->status }}</div>
                                @else
                                    <span class="small text-secondary">Aucun dossier</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    @if ($report->publicUser)
                                        <a href="{{ route('super-admin.public-users.show', $report->publicUser) }}" class="btn btn-sm btn-outline-dark">Voir l usager</a>
                                    @endif
                                    @if ($report->reparationCase)
                                        <a href="{{ route('super-admin.reparation-cases.show', $report->reparationCase) }}" class="btn btn-sm btn-outline-secondary">Voir le dossier</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary">Aucun signalement public trouve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $reports->currentPage() }} sur {{ $reports->lastPage() }}</div>
            {{ $reports->links() }}
        </div>
    </section>
@endsection
