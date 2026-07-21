@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Maintenance')
@section('page-title', 'Maintenance')
@section('page-description', 'Piloter les options sensibles et vider les données de test avant le démarrage en production avec des profils contrôlés.')

@section('header-badges')
    <span class="badge-soft">{{ $cleanupEnabled ? 'Nettoyage activé' : 'Nettoyage verrouillé' }}</span>
@endsection

@section('content')
    <style>
        .maintenance-cleanup-page .fw-bold {
            font-weight: 600 !important;
        }
        .maintenance-cleanup-page .fw-semibold {
            font-weight: 500 !important;
        }
        .maintenance-cleanup-page code {
            font-weight: 400;
        }
    </style>

    <div class="maintenance-cleanup-page">
    <section class="panel-card mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <div class="fw-bold mb-1">Notifications de proximité</div>
                <div class="text-secondary small">
                    Active ou désactive l’envoi de notifications aux usagers situés dans un rayon de 1 km lorsqu’un signalement compatible est créé.
                </div>
                <div class="small text-secondary mt-2">{{ $nearbyReportNotificationsFeature->name }}</div>
            </div>
            <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
                <span class="status-chip">{{ $nearbyReportNotificationsFeature->status === 'active' ? 'Active' : 'Inactive' }}</span>
                <form method="POST" action="{{ route('super-admin.maintenance.nearby-report-notifications.toggle') }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn {{ $nearbyReportNotificationsFeature->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                        {{ $nearbyReportNotificationsFeature->status === 'active' ? 'Désactiver' : 'Activer' }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="panel-card mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <div class="fw-bold mb-1">Nettoyage des données</div>
                <div class="text-secondary small">
                    Les profils et tables ci-dessous ne touchent pas aux données essentielles: utilisateurs internes, rôles, permissions, référentiels, catégories, institutions, tarifications, types d’usagers et types de signal.
                </div>
            </div>
            <div class="text-lg-end">
                <div class="small text-secondary">Confirmation requise</div>
                <div class="fw-bold">{{ $confirmationText }}</div>
            </div>
        </div>

        @unless ($cleanupEnabled)
            <div class="alert alert-warning mt-3 mb-0">
                Le nettoyage est désactivé. Activez-le dans l’environnement uniquement le jour de la préparation en production.
            </div>
        @endunless
    </section>

    <div class="row g-4">
        @foreach ($profiles as $profile)
            <div class="col-xl-6">
                <section class="panel-card h-100">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <div>
                            <div class="fw-bold">{{ $profile['label'] }}</div>
                            <div class="small text-secondary">{{ $profile['description'] }}</div>
                        </div>
                        <span class="status-chip align-self-start">{{ number_format($profile['rows_count'], 0, ',', ' ') }} lignes</span>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Données</th>
                                    <th class="text-end">Lignes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($profile['display_counts'] as $table)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $table['label'] }}</div>
                                        </td>
                                        <td class="text-end">{{ number_format($table['count'], 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-secondary">Aucune table disponible pour ce profil.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <form method="POST" action="{{ route('super-admin.maintenance.cleanup.destroy') }}" class="row g-2 align-items-end">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="profile" value="{{ $profile['code'] }}">
                        <div class="col-md-8">
                            <label class="form-label small text-secondary">Tapez {{ $confirmationText }} pour confirmer</label>
                            <input class="form-control" name="confirmation" placeholder="{{ $confirmationText }}" @disabled(! $cleanupEnabled || $profile['rows_count'] < 1) required>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-danger w-100" @disabled(! $cleanupEnabled || $profile['rows_count'] < 1)>
                                Vider
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        @endforeach
    </div>

    <section class="panel-card mt-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div>
                <div class="fw-bold">Vider un groupe de données</div>
                <div class="small text-secondary">
                    Seuls les groupes de données métier ou de test autorisés sont disponibles. Les données essentielles sont exclues et ne peuvent pas être sélectionnées.
                </div>
            </div>
            <span class="badge-soft align-self-lg-start">{{ count($tables) }} groupe{{ count($tables) > 1 ? 's' : '' }} autorisé{{ count($tables) > 1 ? 's' : '' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Données</th>
                        <th>Description</th>
                        <th>Rôle et importance</th>
                        <th class="text-end">Lignes</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tables as $table)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $table['label'] }}</div>
                            </td>
                            <td>
                                <div>{{ $table['role'] }}</div>
                                <div class="small text-secondary mt-1">Impact: {{ $table['cleanup_impact'] }}</div>
                            </td>
                            <td>{{ $table['importance'] }}</td>
                            <td class="text-end">{{ number_format($table['rows_count'], 0, ',', ' ') }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('super-admin.maintenance.cleanup.table.destroy') }}" class="d-inline-flex flex-column flex-xl-row gap-2 justify-content-end align-items-xl-center">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="table" value="{{ $table['name'] }}">
                                    <label class="form-check small text-secondary mb-0">
                                        <input class="form-check-input" type="checkbox" name="include_dependencies" value="1" @disabled(! $cleanupEnabled || $table['rows_count'] < 1)>
                                        Inclure les données liées
                                    </label>
                                    <input class="form-control form-control-sm" name="confirmation" placeholder="{{ $confirmationText }}" style="width: 130px" @disabled(! $cleanupEnabled || $table['rows_count'] < 1) required>
                                    <button class="btn btn-sm btn-outline-danger" @disabled(! $cleanupEnabled || $table['rows_count'] < 1)>
                                        Vider
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary">Aucune table métier disponible.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="alert alert-info mb-0">
            Sans les données liées, le nettoyage individuel est refusé si le groupe est encore rattaché à d’autres données non vides. Avec les données liées, seuls les groupes métier ou de test autorisés sont inclus; les données essentielles restent exclues.
        </div>
    </section>
    </div>
@endsection
