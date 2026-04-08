@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Tarification')
@section('page-title', 'Tarification')
@section('page-description', 'Configurer les differentes lignes tarifaires utilisables par les types d usagers publics.')

@section('header-badges')
    <span class="badge-soft">{{ $pricingRules->count() }} lignes tarifaires</span>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createPricingRuleModal">
        Nouvelle tarification
    </button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Catalogue des tarifications</div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Libelle</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pricingRules as $rule)
                        <tr>
                            <td>{{ $rule->code }}</td>
                            <td>{{ $rule->label }}</td>
                            <td>{{ number_format($rule->amount, 0, ',', ' ') }} {{ $rule->currency }}</td>
                            <td><span class="status-chip">{{ $rule->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.pricing.edit', ['pricing_rule' => $rule->id]) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.pricing.toggle-status') }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="pricing_rule_id" value="{{ $rule->id }}">
                                        <button class="btn btn-sm btn-outline-warning">{{ $rule->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.pricing.destroy') }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="pricing_rule_id" value="{{ $rule->id }}">
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary">Aucune tarification enregistree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel-card mt-4">
        <div class="fw-bold mb-3">{{ $pricingRule ? 'Modifier la tarification' : 'Apercu' }}</div>
        @if ($pricingRule)
            <form method="POST" action="{{ route('super-admin.pricing.update') }}" class="row g-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="pricing_rule_id" value="{{ $pricingRule->id }}">
                <div class="col-md-4"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ old('code', $pricingRule->code) }}" required></div>
                <div class="col-md-8"><label class="form-label">Libelle</label><input class="form-control" name="label" value="{{ old('label', $pricingRule->label) }}" required></div>
                <div class="col-md-4"><label class="form-label">Montant</label><input class="form-control" type="number" name="amount" value="{{ old('amount', $pricingRule->amount) }}" required></div>
                <div class="col-md-4"><label class="form-label">Devise</label><input class="form-control" name="currency" value="{{ old('currency', $pricingRule->currency) }}" required></div>
                <div class="col-md-4"><label class="form-label">Debut</label><input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($pricingRule->starts_at)->format('Y-m-d\TH:i')) }}"></div>
                <div class="col-md-4"><label class="form-label">Fin</label><input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($pricingRule->ends_at)->format('Y-m-d\TH:i')) }}"></div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-dark">Enregistrer</button>
                    <a href="{{ route('super-admin.pricing.edit') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        @else
            <div class="text-secondary">Selectionnez une ligne a modifier ou creez une nouvelle tarification.</div>
        @endif
    </section>

    <div class="modal fade" id="createPricingRuleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvelle tarification</h5>
                        <div class="small text-secondary">Creez une ligne reutilisable par un ou plusieurs types d usagers publics.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.pricing.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ old('code') }}" placeholder="public_up_standard" required></div>
                            <div class="col-md-8"><label class="form-label">Libelle</label><input class="form-control" name="label" value="{{ old('label') }}" placeholder="Tarification usager public" required></div>
                            <div class="col-md-4"><label class="form-label">Montant</label><input class="form-control" type="number" name="amount" value="{{ old('amount', 100) }}" required></div>
                            <div class="col-md-4"><label class="form-label">Devise</label><input class="form-control" name="currency" value="{{ old('currency', 'FCFA') }}" required></div>
                            <div class="col-md-4"><label class="form-label">Debut</label><input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"></div>
                            <div class="col-md-4"><label class="form-label">Fin</label><input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($errors->any() && ! request('pricing_rule'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createPricingRuleModal')).show();
            });
        </script>
    @endif
@endpush
