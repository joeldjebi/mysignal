@extends('institution.layouts.app')

@section('page-title', 'Notifications')
@section('page-description', 'Historique des alertes liées aux signalements, dommages et dossiers de votre organisation.')

@section('content')
    @php
        $statusLabels = [
            'all' => 'Toutes',
            'unread' => 'Non lues',
            'read' => 'Lues',
        ];

        $notificationUrl = function ($notification) {
            $data = $notification->data ?? [];

            if (! empty($data['report_id'])) {
                return route('institution.reports.show', $data['report_id']);
            }

            if (! empty($data['reparation_case_id'])) {
                return route('institution.reparation-cases.show', $data['reparation_case_id']);
            }

            return route('institution.notifications.index');
        };

        $categoryLabel = function ($notification) {
            $category = data_get($notification->data, 'category');

            return match ($category ?: $notification->type) {
                'report', 'institution_report_created', 'institution_damage_declared' => 'Signalement',
                'reparation_case', 'institution_reparation_case_opened', 'institution_reparation_case_updated', 'institution_reparation_case_step_added' => 'Dossier',
                default => 'Général',
            };
        };
    @endphp

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="stat-kicker">Notifications</div>
                <div class="stat-value">{{ number_format($totalCount, 0, ',', ' ') }}</div>
                <div class="text-secondary small">Total reçu par votre compte AI.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="stat-kicker">À traiter</div>
                <div class="stat-value">{{ number_format($unreadCount, 0, ',', ' ') }}</div>
                <div class="text-secondary small">Notifications non lues.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="stat-kicker">Lecture</div>
                    <div class="fw-bold text-dark mt-2">Nettoyer le compteur</div>
                    <div class="text-secondary small">Marquez tout comme lu après vérification.</div>
                </div>
                <form method="POST" action="{{ route('institution.notifications.read-all') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-dark w-100" @disabled($unreadCount === 0)>Tout marquer comme lu</button>
                </form>
            </div>
        </div>
    </div>

    <div class="panel-card mb-4">
        <form method="GET" action="{{ route('institution.notifications.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Catégorie</label>
                <select name="category" class="form-select">
                    <option value="">Toutes les catégories</option>
                    @foreach ($availableCategories as $category)
                        <option value="{{ $category['key'] }}" @selected($filters['category'] === $category['key'])>{{ $category['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Recherche</label>
                <input type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Titre, message, type...">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-dark">Filtrer</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div>
                <div class="fw-bold">Historique</div>
                <div class="text-secondary small">{{ $notifications->total() }} élément(s) dans la vue courante.</div>
            </div>
            <a href="{{ route('institution.notifications.index') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Notification</th>
                        <th>Catégorie</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notification)
                        <tr class="{{ $notification->read_at ? '' : 'table-light' }}">
                            <td style="min-width: 320px;">
                                <div class="fw-bold">{{ $notification->title }}</div>
                                <div class="text-secondary small">{{ $notification->body ?: 'Aucun détail complémentaire.' }}</div>
                                <div class="text-secondary small mt-1">{{ $notification->type }}</div>
                            </td>
                            <td>
                                <span class="status-chip">{{ $categoryLabel($notification) }}</span>
                            </td>
                            <td>
                                @if ($notification->read_at)
                                    <span class="status-chip chip-neutral">Lue</span>
                                @else
                                    <span class="status-chip chip-warning">Non lue</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $notification->created_at?->format('d/m/Y H:i') }}</div>
                                @if ($notification->read_at)
                                    <div class="text-secondary small">Lue le {{ $notification->read_at->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
                                    <a href="{{ $notificationUrl($notification) }}" class="btn btn-sm btn-outline-dark">Voir</a>
                                    @if (! $notification->read_at)
                                        <form method="POST" action="{{ route('institution.notifications.read', $notification) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-dark">Marquer lue</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">Aucune notification trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($notifications->hasPages())
            <div class="p-3 border-top">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
