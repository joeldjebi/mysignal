@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Historique des paiements')
@section('page-title', 'Historique des paiements')
@section('page-description', 'Consulter tous les paiements effectues par les usagers publics, suivre leur statut et retrouver rapidement le signalement ou l usager associe.')

@section('header-badges')
    <span class="badge-soft">{{ $payments->total() }} paiement{{ $payments->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Historique des paiements</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, fournisseur, usager, signalement...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['pending' => 'En attente', 'paid' => 'Paye', 'failed' => 'Echoue', 'cancelled' => 'Annule'] as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Fournisseur</label>
                    <select name="provider" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($providers as $provider)
                            <option value="{{ $provider }}" @selected(request('provider') === $provider)>{{ $provider }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Application</label>
                    <select name="application_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($applications as $application)
                            <option value="{{ $application->id }}" @selected((string) request('application_id') === (string) $application->id)>{{ $application->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Organisation</label>
                    <select name="organization_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.payments.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $payments->total() }} resultat{{ $payments->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Paiement</th>
                        <th>Usager public</th>
                        <th>Signalement</th>
                        <th>Montant</th>
                        <th>Fournisseur</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $payment->reference }}</div>
                                <div class="small text-secondary">{{ $payment->initiated_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small text-secondary">Ref fournisseur : {{ $payment->provider_reference ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ trim(($payment->publicUser?->first_name ?? '').' '.($payment->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $payment->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $payment->publicUser?->publicUserType?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $payment->incidentReport?->reference ?: '-' }}</div>
                                <div class="small text-secondary">{{ $payment->incidentReport?->signal_label ?: $payment->incidentReport?->signal_code ?: $payment->incidentReport?->incident_type ?: '-' }}</div>
                                <div class="small text-secondary">{{ $payment->incidentReport?->application?->name ?: '-' }} / {{ $payment->incidentReport?->organization?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency ?: 'XOF' }}</div>
                                <div class="small text-secondary">{{ $payment->pricingRule?->label ?: 'Tarification non renseignee' }}</div>
                            </td>
                            <td>
                                <div>{{ $payment->provider ?: '-' }}</div>
                                <div class="small text-secondary">{{ $payment->paid_at?->format('d/m/Y H:i') ?: 'Paiement non confirme' }}</div>
                            </td>
                            <td>
                                <span class="status-chip">{{ $payment->status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    @if ($payment->publicUser)
                                        <a href="{{ route('super-admin.public-users.show', $payment->publicUser) }}" class="btn btn-sm btn-outline-dark">Voir l usager</a>
                                    @endif
                                    @if ($payment->incidentReport?->reparationCase)
                                        <a href="{{ route('super-admin.reparation-cases.show', $payment->incidentReport->reparationCase) }}" class="btn btn-sm btn-outline-secondary">Voir le dossier</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary">Aucun paiement trouve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $payments->currentPage() }} sur {{ $payments->lastPage() }}</div>
            {{ $payments->links() }}
        </div>
    </section>
@endsection
