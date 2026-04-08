@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Detail organisation')
@section('page-title', $organization->name)
@section('page-description', 'Vue de synthese premium de l organisation, de ses acces et de son activite.')

@section('header-badges')
    <span class="badge-soft">{{ $organization->application?->name ?: 'Sans application' }}</span>
    <span class="badge-soft">{{ $organization->organizationType?->name ?: 'Sans type' }}</span>
    <a href="{{ route('super-admin.organizations.edit', $organization) }}" class="btn btn-dark">Modifier l organisation</a>
@endsection

@section('content')
    <style>
        .org-hero {
            border-radius: 30px;
            padding: 1.35rem;
            background:
                radial-gradient(circle at top right, rgba(196,155,72,.24), transparent 28%),
                linear-gradient(145deg, #0f2738, #1f4f70);
            color: #fff;
            box-shadow: 0 24px 48px rgba(16,42,67,.16);
        }
        .org-hero-grid {
            display: grid;
            grid-template-columns: 1.6fr .9fr;
            gap: 1rem;
        }
        .org-hero-code {
            display: inline-flex;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            padding: .35rem .75rem;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .04em;
        }
        .org-hero-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
            margin-top: 1rem;
        }
        .org-hero-card {
            border-radius: 20px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.12);
            padding: .9rem;
        }
        .org-hero-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: rgba(255,255,255,.68);
            font-weight: 800;
            margin-bottom: .25rem;
        }
        .org-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }
        .org-kpi-card {
            border-radius: 24px;
            border: 1px solid rgba(16,42,67,.08);
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(246,249,252,.92));
            box-shadow: 0 18px 42px rgba(16,42,67,.07);
            padding: 1rem;
        }
        .org-kpi-label {
            color: var(--acepen-muted);
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 800;
            margin-bottom: .45rem;
        }
        .org-kpi-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
            color: #12354d;
        }
        .org-panel {
            border-radius: 26px;
            border: 1px solid rgba(16,42,67,.08);
            background: rgba(255,255,255,.96);
            box-shadow: 0 18px 42px rgba(16,42,67,.06);
            padding: 1.1rem;
            height: 100%;
        }
        .org-panel-title {
            font-weight: 800;
            margin-bottom: .9rem;
        }
        .feature-group-stack {
            display: grid;
            gap: .85rem;
        }
        .feature-group-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 20px;
            background: rgba(248,250,252,.92);
            padding: .9rem;
        }
        .feature-chip-grid {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: .6rem;
        }
        .feature-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .35rem .65rem;
            background: rgba(25,75,112,.08);
            color: var(--acepen-blue);
            font-size: .75rem;
            font-weight: 700;
        }
        .mini-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .8rem;
        }
        .mini-stat-card {
            border-radius: 18px;
            background: rgba(248,250,252,.95);
            border: 1px solid rgba(16,42,67,.07);
            padding: .85rem;
        }
        .breakdown-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            padding: .65rem .8rem;
            border-radius: 16px;
            background: rgba(248,250,252,.92);
            border: 1px solid rgba(16,42,67,.06);
        }
        .recent-list {
            display: grid;
            gap: .75rem;
        }
        .recent-card {
            border-radius: 18px;
            background: rgba(248,250,252,.95);
            border: 1px solid rgba(16,42,67,.07);
            padding: .9rem;
        }
        @media (max-width: 1199.98px) {
            .org-hero-grid,
            .org-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767.98px) {
            .org-hero-grid,
            .org-hero-meta,
            .org-kpi-grid,
            .mini-stat-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @php
        $featureGroups = $organization->resolvedFeatures()->groupBy(function ($feature) {
            return match (true) {
                str_starts_with($feature->code, 'INSTITUTION_DASHBOARD_') => 'Dashboard',
                str_starts_with($feature->code, 'INSTITUTION_') => 'Acces institutionnels',
                str_starts_with($feature->code, 'PUBLIC_') => 'Modules publics',
                default => 'Autres',
            };
        });
        $reportStatusLabels = [
            'submitted' => 'Soumis',
            'in_progress' => 'En cours',
            'resolved' => 'Resolus',
            'rejected' => 'Rejetes',
        ];
        $damageStatusLabels = [
            'non_soumis' => 'Aucun dommage',
            'submitted' => 'Soumis',
            'in_progress' => 'En cours',
            'resolved' => 'Resolus',
            'rejected' => 'Rejetes',
        ];
    @endphp

    <section class="org-hero mb-4">
        <div class="org-hero-grid">
            <div>
                <div class="org-hero-code">{{ $organization->code }}</div>
                <div class="display-6 fw-bold mt-3 mb-2">{{ $organization->name }}</div>
                <div class="text-white-50" style="max-width: 52rem;">
                    {{ $organization->description ?: 'Cette institution dispose de son propre portail, de ses admins racine et de ses fonctionnalites metier configurees par le super admin.' }}
                </div>

                <div class="org-hero-meta">
                    <div class="org-hero-card">
                        <div class="org-hero-label">Application</div>
                        <div class="fw-semibold">{{ $organization->application?->name ?: '-' }}</div>
                    </div>
                    <div class="org-hero-card">
                        <div class="org-hero-label">Type de client</div>
                        <div class="fw-semibold">{{ $organization->organizationType?->name ?: '-' }}</div>
                    </div>
                    <div class="org-hero-card">
                        <div class="org-hero-label">Portail</div>
                        <div class="fw-semibold">{{ $organization->portal_key ?: '-' }}</div>
                    </div>
                    <div class="org-hero-card">
                        <div class="org-hero-label">Contact</div>
                        <div class="fw-semibold">{{ $organization->email ?: ($organization->phone ?: '-') }}</div>
                    </div>
                </div>
            </div>

            <div class="org-panel" style="background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.12); color: #fff;">
                <div class="org-panel-title">Vue rapide</div>
                <div class="mini-stat-grid">
                    <div class="mini-stat-card" style="background: rgba(255,255,255,.10); border-color: rgba(255,255,255,.10);">
                        <div class="org-hero-label">Statut</div>
                        <div class="fw-semibold">{{ $organization->status }}</div>
                    </div>
                    <div class="mini-stat-card" style="background: rgba(255,255,255,.10); border-color: rgba(255,255,255,.10);">
                        <div class="org-hero-label">Fonctionnalites</div>
                        <div class="fw-semibold">{{ $organization->resolvedFeatures()->count() }}</div>
                    </div>
                    <div class="mini-stat-card" style="background: rgba(255,255,255,.10); border-color: rgba(255,255,255,.10);">
                        <div class="org-hero-label">Signalements</div>
                        <div class="fw-semibold">{{ $stats['reports_count'] }}</div>
                    </div>
                    <div class="mini-stat-card" style="background: rgba(255,255,255,.10); border-color: rgba(255,255,255,.10);">
                        <div class="org-hero-label">Dommages</div>
                        <div class="fw-semibold">{{ $stats['damages_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="org-kpi-grid mb-4">
        <article class="org-kpi-card">
            <div class="org-kpi-label">Admins institutionnels</div>
            <div class="org-kpi-value">{{ $stats['admins_count'] }}</div>
        </article>
        <article class="org-kpi-card">
            <div class="org-kpi-label">Compteurs</div>
            <div class="org-kpi-value">{{ $stats['meters_count'] }}</div>
        </article>
        <article class="org-kpi-card">
            <div class="org-kpi-label">Signalements ouverts</div>
            <div class="org-kpi-value">{{ $stats['open_reports_count'] }}</div>
        </article>
        <article class="org-kpi-card">
            <div class="org-kpi-label">Montant collecte</div>
            <div class="org-kpi-value">{{ number_format($stats['payments_total'], 0, ',', ' ') }}</div>
        </article>
    </section>

    <section class="row g-4">
        <div class="col-xl-5">
            <article class="org-panel">
                <div class="org-panel-title">Fonctionnalites actives</div>
                @if ($featureGroups->isEmpty())
                    <div class="text-secondary small">Aucune fonctionnalite active pour cette organisation.</div>
                @else
                    <div class="feature-group-stack">
                        @foreach ($featureGroups as $groupLabel => $features)
                            <div class="feature-group-card">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div class="fw-semibold">{{ $groupLabel }}</div>
                                    <div class="small text-secondary">{{ $features->count() }}</div>
                                </div>
                                <div class="feature-chip-grid">
                                    @foreach ($features as $feature)
                                        <span class="feature-chip">{{ $feature->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        </div>
        <div class="col-xl-7">
            <div class="row g-4">
                <div class="col-md-6">
                    <article class="org-panel">
                        <div class="org-panel-title">Statuts des signalements</div>
                        <div class="recent-list">
                            @forelse ($reportStatusLabels as $status => $label)
                                <div class="breakdown-row">
                                    <span>{{ $label }}</span>
                                    <span class="fw-bold">{{ $reportStatusBreakdown[$status] ?? 0 }}</span>
                                </div>
                            @empty
                                <div class="text-secondary small">Aucune donnee.</div>
                            @endforelse
                        </div>
                    </article>
                </div>
                <div class="col-md-6">
                    <article class="org-panel">
                        <div class="org-panel-title">Statuts des dommages</div>
                        <div class="recent-list">
                            @foreach ($damageStatusLabels as $status => $label)
                                <div class="breakdown-row">
                                    <span>{{ $label }}</span>
                                    <span class="fw-bold">{{ $damageStatusBreakdown[$status] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                </div>
                <div class="col-12">
                    <article class="org-panel">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <div class="org-panel-title mb-0">Derniers admins institutionnels</div>
                            <span class="small text-secondary">{{ $recentAdmins->count() }} affiche(s)</span>
                        </div>
                        @if ($recentAdmins->isEmpty())
                            <div class="text-secondary small">Aucun admin institutionnel rattache.</div>
                        @else
                            <div class="recent-list">
                                @foreach ($recentAdmins as $admin)
                                    <div class="recent-card d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                        <div>
                                            <div class="fw-semibold">{{ $admin->name }}</div>
                                            <div class="small text-secondary">{{ $admin->email ?: ($admin->phone ?: 'Sans contact') }}</div>
                                        </div>
                                        <span class="status-chip">{{ $admin->status }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mt-1">
        <div class="col-12">
            <article class="org-panel">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div class="org-panel-title mb-0">Derniers signalements</div>
                    <span class="small text-secondary">{{ $recentReports->count() }} affiche(s)</span>
                </div>
                @if ($recentReports->isEmpty())
                    <div class="text-secondary small">Aucun signalement pour cette organisation.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Signal</th>
                                    <th>Usager</th>
                                    <th>Compteur</th>
                                    <th>Commune</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentReports as $report)
                                    <tr>
                                        <td class="fw-semibold">{{ $report->reference }}</td>
                                        <td>{{ $report->signal_label ?: $report->signal_code }}</td>
                                        <td>{{ trim(($report->publicUser?->first_name ?? '').' '.($report->publicUser?->last_name ?? '')) ?: ($report->publicUser?->phone ?: '-') }}</td>
                                        <td>{{ $report->meter?->meter_number ?: '-' }}</td>
                                        <td>{{ $report->commune?->name ?: '-' }}</td>
                                        <td><span class="status-chip">{{ $report->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </div>
    </section>
@endsection
