@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Journaux d activite')
@section('page-title', 'Journaux d activite')
@section('page-description', 'Consulter les activites visibles selon votre profil, vos permissions et le perimetre defini par le super admin.')

@section('header-badges')
    <span class="badge-soft">{{ $logs->total() }} entree{{ $logs->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Historique des activites</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Action, acteur, description...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Portail</label>
                    <select name="portal" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($portals as $portal)
                            <option value="{{ $portal }}" @selected(request('portal') === $portal)>{{ $portal }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Action</label>
                    <select name="action_type" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action_type') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.activity-logs.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Portail</th>
                        <th>Acteur</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Sujet</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d/m/Y H:i:s') ?: '-' }}</td>
                            <td><span class="status-chip">{{ $log->portal }}</span></td>
                            <td>
                                @if ($log->actorUser)
                                    <div class="fw-semibold">{{ $log->actorUser->name }}</div>
                                    <div class="small text-secondary">{{ $log->actorUser->email }}</div>
                                @elseif ($log->actorPublicUser)
                                    <div class="fw-semibold">{{ trim(($log->actorPublicUser->first_name ?? '').' '.($log->actorPublicUser->last_name ?? '')) }}</div>
                                    <div class="small text-secondary">{{ $log->actorPublicUser->phone }}</div>
                                @else
                                    <span class="text-secondary">Systeme</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->action }}</div>
                                <div class="small text-secondary">{{ $log->ip_address ?: '-' }}</div>
                            </td>
                            <td>{{ $log->description ?: '-' }}</td>
                            <td>
                                @if ($log->subject)
                                    <div class="fw-semibold">{{ class_basename($log->subject_type) }}</div>
                                    <div class="small text-secondary">#{{ $log->subject_id }}</div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($log->actorPublicUser)
                                    <a href="{{ route('super-admin.public-users.show', $log->actorPublicUser) }}" class="btn btn-sm btn-outline-dark">Details</a>
                                @elseif ($log->actorUser && ! $log->actorUser->is_super_admin && $log->actorUser->organization_id === null)
                                    <a href="{{ route('super-admin.system-users.show', $log->actorUser) }}" class="btn btn-sm btn-outline-dark">Details</a>
                                @elseif ($log->actorUser && $log->actorUser->organization_id !== null)
                                    <a href="{{ route('super-admin.institution-admins.edit', $log->actorUser) }}" class="btn btn-sm btn-outline-dark">Details</a>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary">Aucune activite visible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $logs->currentPage() }} sur {{ $logs->lastPage() }}</div>
            {{ $logs->links() }}
        </div>
    </section>
@endsection
