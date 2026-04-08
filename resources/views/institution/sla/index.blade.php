@extends('institution.layouts.app')

@section('title', config('app.name').' | SLA cibles')
@section('page-title', 'SLA cibles')
@section('page-description', 'Referentiel des SLA programmes pour votre type d organisation et votre reseau.')

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold">Nouvelle regle SLA</div>
                        <div class="text-secondary small">Creation limitee a votre type d'organisation et a votre reseau.</div>
                    </div>
                    <span class="status-chip">{{ $organization?->organizationType?->name ?? 'Type non defini' }}</span>
                </div>

                <form method="POST" action="{{ route('institution.sla.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Code signal</label>
                        <input type="text" name="signal_code" class="form-control" placeholder="EL-01" required>
                    </div>
                    <div>
                        <label class="form-label">Libelle signal</label>
                        <input type="text" name="signal_label" class="form-control" placeholder="Coupure totale de courant" required>
                    </div>
                    <div>
                        <label class="form-label">SLA cible (heures)</label>
                        <input type="number" min="1" max="999" name="sla_hours" class="form-control" placeholder="4" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark">Creer</button>
                </form>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold">Referentiel SLA</div>
                        <div class="text-secondary small">Les admins institutionnels peuvent creer, modifier et activer/desactiver, sans suppression.</div>
                    </div>
                </div>

                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Statut</label>
                            <select name="status" class="form-select">
                                <option value="">Tous</option>
                                <option value="active" @selected(request('status') === 'active')>Actif</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex gap-2">
                            <button class="btn btn-dark">Filtrer</button>
                            <a href="{{ route('institution.sla.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Reseau</th>
                                <th>Signal</th>
                                <th>SLA</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($slaPolicies as $slaPolicy)
                                <tr>
                                    <td>{{ $slaPolicy->network_type }}</td>
                                    <td>
                                        <div>{{ $slaPolicy->signal_label }}</div>
                                        <div class="small text-secondary">{{ $slaPolicy->signal_code }}</div>
                                    </td>
                                    <td><span class="status-chip">{{ $slaPolicy->sla_hours }} h</span></td>
                                    <td>{{ $slaPolicy->description ?: '-' }}</td>
                                    <td><span class="status-chip">{{ $slaPolicy->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('institution.sla.edit', $slaPolicy) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('institution.sla.toggle-status', $slaPolicy) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $slaPolicy->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-secondary">Aucune regle SLA disponible pour ce portail.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
