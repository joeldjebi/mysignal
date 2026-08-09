@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Journaux d’activité')
@section('page-title', 'Journaux d’activité')
@section('page-description', 'Consulter les activités visibles selon votre profil, vos permissions et le périmètre défini par le super admin.')

@section('header-badges')
    <span class="badge-soft">{{ $logs->total() }} entrée{{ $logs->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Historique des activités</div>
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
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Par page</label>
                    <select name="per_page" class="form-select">
                        @foreach ([20, 50, 75, 100] as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected((int) request('per_page', 20) === $perPageOption)>{{ $perPageOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.activity-logs.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
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
                                    <span class="text-secondary">Système</span>
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
                                @if (! empty($log->properties))
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-bs-toggle="modal" data-bs-target="#activityLogDetails{{ $log->id }}">
                                        Voir le journal
                                    </button>
                                @endif

                                @if ($log->actorPublicUser)
                                    <a href="{{ route('super-admin.public-users.show', $log->actorPublicUser) }}" class="btn btn-sm btn-outline-dark">Détails</a>
                                @elseif ($log->actorUser && ! $log->actorUser->is_super_admin && $log->actorUser->organization_id === null)
                                    <a href="{{ route('super-admin.system-users.show', $log->actorUser) }}" class="btn btn-sm btn-outline-dark">Détails</a>
                                @elseif ($log->actorUser && $log->actorUser->organization_id !== null)
                                    <a href="{{ route('super-admin.institution-admins.edit', $log->actorUser) }}" class="btn btn-sm btn-outline-dark">Détails</a>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr><td colspan="7" class="text-center text-secondary">Aucune activité visible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach ($logs as $log)
            @if (! empty($log->properties))
                <div class="modal fade" id="activityLogDetails{{ $log->id }}" tabindex="-1" aria-labelledby="activityLogDetailsLabel{{ $log->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="activityLogDetailsLabel{{ $log->id }}">Détails du journal</h5>
                                    <div class="small text-secondary">{{ $log->created_at?->format('d/m/Y H:i:s') }} - {{ $log->action }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                <pre class="bg-light border rounded p-3 small mb-0" style="white-space: pre-wrap;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $logs->currentPage() }} sur {{ $logs->lastPage() }}</div>
            {{ $logs->links() }}
        </div>
    </section>
@endsection
