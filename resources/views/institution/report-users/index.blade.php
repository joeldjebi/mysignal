@extends('institution.layouts.app')

@section('title', config('app.name').' | Usagers publics')
@section('page-title', 'Usagers publics')
@section('page-description', 'Liste des usagers publics visibles pour cette institution, avec filtre sur les signalements.')

@section('content')
    @php
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $features ?? [], true);
    @endphp
    <section class="panel-card">
        <div class="fw-bold mb-3">Usagers publics</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, telephone, email">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Commune</label>
                    <select name="commune" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($communes as $commune)
                            <option value="{{ $commune }}" @selected(request('commune') === $commune)>{{ $commune }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Signalement</label>
                    <select name="has_reports" class="form-select">
                        <option value="">Tous</option>
                        <option value="yes" @selected(request('has_reports') === 'yes')>A deja signale</option>
                        <option value="no" @selected(request('has_reports') === 'no')>N a pas signale</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('institution.report-users.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $users->total() }} usager(s)</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Usager</th>
                        <th>Telephone</th>
                        <th>Commune</th>
                        <th>Signalements</th>
                        @if ($canViewPaymentInfo)
                            <th>Payes</th>
                        @endif
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div>{{ $user->first_name }} {{ $user->last_name }}</div>
                                <div class="small text-secondary">{{ $user->email ?: '-' }}</div>
                            </td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->commune ?: '-' }}</td>
                            <td><span class="status-chip">{{ $user->reports_count }}</span></td>
                            @if ($canViewPaymentInfo)
                                <td><span class="status-chip">{{ $user->paid_reports_count }}</span></td>
                            @endif
                            <td><span class="status-chip">{{ $user->status }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('institution.report-users.show', $user) }}" class="btn btn-sm btn-outline-dark">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canViewPaymentInfo ? 7 : 6 }}" class="text-center text-secondary">Aucun usager disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $users->currentPage() }} sur {{ $users->lastPage() }}</div>
            {{ $users->links() }}
        </div>
    </section>
@endsection
