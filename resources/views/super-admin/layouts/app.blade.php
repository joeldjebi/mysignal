<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name').' | Super Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --acepen-navy: #183447;
            --acepen-blue: #6791ff;
            --acepen-gold: #ffa117;
            --acepen-pink: #ff0068;
            --acepen-green: #5bebaf;
            --acepen-mist: #fff7ec;
            --acepen-card: rgba(255, 255, 255, .92);
            --acepen-ink: #1f2933;
            --acepen-muted: #6b7c93;
        }
        body {
            background: #f5f8ff;
            color: var(--acepen-ink);
        }
        .dashboard-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 272px minmax(0, 1fr);
            gap: 1.1rem;
            padding: 1.1rem;
        }
        .sidebar {
            position: sticky;
            top: 1.1rem;
            height: calc(100vh - 2.2rem);
            border-radius: 26px;
            padding: 1rem;
            background: var(--acepen-navy);
            color: rgba(255,255,255,.9);
            box-shadow: 0 28px 80px rgba(12,34,52,.28);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        /* .sidebar-brand {
            padding-bottom: 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.09);
            margin-bottom: 1.25rem;
        } */
        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--acepen-gold);
            color: var(--acepen-navy);
            font-weight: 800;
            box-shadow: 0 16px 32px rgba(255,161,23,.24);
        }
        .sidebar-brand .fw-bold,
        .sidebar .fw-bold,
        .sidebar .fw-semibold {
            font-weight: 400 !important;
        }
        .sidebar-brand > .small,
        .nav-pill .small {
            display: none !important;
        }
        .sidebar-label {
            color: rgba(255,255,255,.5);
            text-transform: uppercase;
            letter-spacing: .06em;
            font-size: .64rem;
            font-weight: 400;
            margin: .8rem 0 .42rem;
        }
        .nav-pill {
            display: flex;
            align-items: center;
            gap: .62rem;
            text-decoration: none;
            color: rgba(255,255,255,.84);
            padding: .54rem .68rem;
            border-radius: 14px;
            transition: .18s ease;
            margin-bottom: .16rem;
        }
        .nav-pill:hover { background: rgba(255,255,255,.08); color: #fff; }
        .nav-pill.active {
            background: rgba(255,161,23,.20);
            color: #fff;
            border: 1px solid rgba(255,161,23,.28);
        }
        .nav-dropdown {
            margin-bottom: .16rem;
        }
        .nav-dropdown summary {
            list-style: none;
            cursor: pointer;
        }
        .nav-dropdown summary::-webkit-details-marker {
            display: none;
        }
        .nav-dropdown .nav-pill {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
        }
        .nav-dropdown .nav-pill.active {
            background: rgba(255,161,23,.20);
            border: 1px solid rgba(255,161,23,.28);
        }
        .nav-submenu {
            display: grid;
            gap: .08rem;
            margin: .05rem 0 .32rem 2.05rem;
            padding-left: .55rem;
            border-left: 1px solid rgba(255,255,255,.12);
        }
        .nav-subitem {
            display: block;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            font-size: .84rem;
            padding: .36rem .5rem;
            border-radius: 10px;
            transition: .18s ease;
        }
        .nav-subitem:hover {
            color: #fff;
            background: rgba(255,255,255,.08);
        }
        .dropdown-caret {
            margin-left: auto;
            color: rgba(255,255,255,.52);
            font-size: .7rem;
        }
        .nav-icon {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.08);
            font-size: .68rem;
            font-weight: 500;
        }
        .sidebar-footer {
            margin-top: auto;
            padding-top: .9rem;
            border-top: 1px solid rgba(255,255,255,.09);
        }
        .sidebar-menu {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: .35rem;
        }
        .sidebar-menu::-webkit-scrollbar {
            width: 8px;
        }
        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255,255,255,.05);
            border-radius: 999px;
        }
        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.18);
            border-radius: 999px;
        }
        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,.28);
        }
        .sidebar-card {
            border-radius: 18px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.08);
            padding: .8rem;
        }
        .btn-sidebar {
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.16);
            color: #fff;
            min-height: 2.6rem;
        }
        .btn-sidebar:hover { background: rgba(255,255,255,.08); color: #fff; }
        .topbar, .panel-card, .stat-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 22px;
            background: var(--acepen-card);
            box-shadow: 0 24px 70px rgba(16,42,67,.08);
        }
        .topbar {
            padding: .85rem 1rem;
            position: sticky;
            top: 1.1rem;
            z-index: 30;
            backdrop-filter: blur(18px);
        }
        .panel-card { padding: 1.1rem; }
        .content-area { min-width: 0; }
        .sticky-form-card {
            position: sticky;
            top: 8.6rem;
        }
        .filter-bar {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 18px;
            background: rgba(255,255,255,.72);
            padding: .9rem;
            margin-bottom: 1rem;
        }
        .table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .85rem;
        }
        .table-meta {
            color: var(--acepen-muted);
            font-size: .82rem;
        }
        .actions-wrap {
            display: inline-flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: .4rem;
        }
        .pagination {
            margin-bottom: 0;
        }
        .page-link {
            color: var(--acepen-navy);
            border-color: rgba(16,42,67,.08);
        }
        .page-item.active .page-link {
            background: var(--acepen-navy);
            border-color: var(--acepen-navy);
        }
        .badge-soft {
            background: rgba(196,155,72,.14);
            color: #7a5c1d;
            border-radius: 999px;
            padding: .42rem .72rem;
            font-weight: 700;
            font-size: .78rem;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            padding: .34rem .6rem;
            font-size: .74rem;
            font-weight: 700;
            background: rgba(16,42,67,.06);
            color: var(--acepen-navy);
        }
        .table-modern thead th {
            color: var(--acepen-muted);
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom-color: rgba(16,42,67,.08);
        }
        .table-modern tbody td {
            border-bottom-color: rgba(16,42,67,.06);
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: .92rem;
            vertical-align: middle;
        }
        @media (max-width: 1199.98px) {
            .dashboard-shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
            .sticky-form-card { position: static; top: auto; }
        }
    </style>
</head>
<body>
    @php
        $authUser = request()->user();
        $activeAccess = request()->attributes->get('super_admin_access') ?: $authUser?->getRelationValue('activeAccess');

        if ($authUser && $activeAccess instanceof \App\Models\UserAccess) {
            $authUser->setRelation('activeAccess', $activeAccess);
        }

        $activePortal = app(\App\Support\Auth\SuperAdminAccessResolver::class)->resolveLegalPortal($authUser, $activeAccess)
            ?: $activeAccess?->portal;
        $internalPortalLabels = [
            'backoffice' => 'Backoffice',
            'callcenter' => 'Centre d’appels',
            'huissier' => 'Huissier',
            'aoda' => 'Ordre des avocats',
            'avocat' => 'Avocat',
        ];
        $isInternalPortalUser = $authUser && ! $authUser->is_super_admin;
        $portalInitials = $isInternalPortalUser
            ? collect(preg_split('/\s+/', (string) ($internalPortalLabels[$activePortal] ?? 'Backoffice')))
                ->filter()
                ->map(fn ($word) => mb_strtoupper(mb_substr((string) $word, 0, 1)))
                ->take(4)
                ->implode('')
            : 'SA';
        $portalInitials = $portalInitials ?: 'BO';
        $portalTitle = 'Portail '.$portalInitials;
        $portalDescription = $isInternalPortalUser
            ? 'Espace opérationnel réservé aux utilisateurs internes autorisés.'
            : 'Paramétrage global, gouvernance et référentiels de la plateforme.';
        $logoutRoute = $activePortal === 'callcenter'
            ? 'callcenter.logout'
            : ($isInternalPortalUser ? 'backoffice.logout' : 'super-admin.logout');
        $layoutPermissionCodes = $authUser?->effectivePermissionCodes() ?? collect();
        $canAccess = fn (string $permissionCode): bool => (bool) ($authUser?->is_super_admin || $layoutPermissionCodes->contains($permissionCode));
    @endphp
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="brand-mark">{{ $portalInitials }}</div>
                    <div>
                        <div class="small text-white-50 fw-semibold">My-Signal by UFC</div>
                        <div class="fw-bold fs-5">{{ $portalTitle }}</div>
                    </div>
                </div>
                <div class="small text-white-50">{{ $portalDescription }}</div>
            </div>

            <div class="sidebar-menu">
                <div class="sidebar-label">Pilotage</div>
                @if ($layoutPermissionCodes->contains('BO_REPARATION_CASES_HUISSIER') || $layoutPermissionCodes->contains('BO_REPARATION_CASES_AODA') || $layoutPermissionCodes->contains('BO_REPARATION_CASES_AVOCAT'))
                    <a href="{{ route('backoffice.dashboard') }}" class="nav-pill {{ request()->routeIs('backoffice.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">TB</span>
                        <span><span class="d-block fw-semibold">Tableau de bord</span><span class="small text-white-50">Stats et rapports</span></span>
                    </a>
                    <a href="{{ route('backoffice.legal-cases.index') }}" class="nav-pill {{ request()->routeIs('backoffice.legal-cases.*') ? 'active' : '' }}">
                        <span class="nav-icon">CT</span>
                        <span><span class="d-block fw-semibold">Dossiers contentieux</span><span class="small text-white-50">Traitement opérationnel</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_DASHBOARD_VIEW'))
                    <a href="{{ route('super-admin.dashboard') }}" class="nav-pill {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">DB</span>
                        <span><span class="d-block fw-semibold">Tableau de bord</span><span class="small text-white-50">Vue d'ensemble</span></span>
                    </a>
                    <a href="{{ route('super-admin.reports-builder.index') }}" class="nav-pill {{ request()->routeIs('super-admin.reports-builder.*') ? 'active' : '' }}">
                        <span class="nav-icon">RP</span>
                        <span><span class="d-block fw-semibold">Rapports à la demande</span><span class="small text-white-50">Exports et analyses</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_LANDING_PAGE_MANAGE'))
                    <a href="{{ route('super-admin.landing-page.edit') }}" class="nav-pill {{ request()->routeIs('super-admin.landing-page.*') ? 'active' : '' }}">
                        <span class="nav-icon">LP</span>
                        <span><span class="d-block fw-semibold">Pages publiques</span><span class="small text-white-50">Accueil et contenu</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_MAINTENANCE_CLEANUP'))
                    <a href="{{ route('super-admin.maintenance.cleanup.index') }}" class="nav-pill {{ request()->routeIs('super-admin.maintenance.*') ? 'active' : '' }}">
                        <span class="nav-icon">MT</span>
                        <span><span class="d-block fw-semibold">Maintenance</span><span class="small text-white-50">Nettoyage controle</span></span>
                    </a>
                @endif

                <div class="sidebar-label">Référentiels</div>
                @if ($canAccess('SA_COUNTRIES_VIEW') || $canAccess('SA_COUNTRIES_MANAGE'))
                    <a href="{{ route('super-admin.countries.index') }}" class="nav-pill {{ request()->routeIs('super-admin.countries.*') ? 'active' : '' }}">
                        <span class="nav-icon">PY</span>
                        <span><span class="d-block fw-semibold">Pays</span><span class="small text-white-50">Referentiel pays</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_CITIES_VIEW') || $canAccess('SA_CITIES_MANAGE'))
                    <a href="{{ route('super-admin.cities.index') }}" class="nav-pill {{ request()->routeIs('super-admin.cities.*') ? 'active' : '' }}">
                        <span class="nav-icon">VL</span>
                        <span><span class="d-block fw-semibold">Villes</span><span class="small text-white-50">Referentiel villes</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_COMMUNES_VIEW') || $canAccess('SA_COMMUNES_MANAGE'))
                    <a href="{{ route('super-admin.communes.index') }}" class="nav-pill {{ request()->routeIs('super-admin.communes.*') ? 'active' : '' }}">
                        <span class="nav-icon">CM</span>
                        <span><span class="d-block fw-semibold">Communes</span><span class="small text-white-50">Referentiel communes</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_NEIGHBORHOODS_VIEW') || $canAccess('SA_NEIGHBORHOODS_MANAGE'))
                    <a href="{{ route('super-admin.neighborhoods.index') }}" class="nav-pill {{ request()->routeIs('super-admin.neighborhoods.*') ? 'active' : '' }}">
                        <span class="nav-icon">QT</span>
                        <span><span class="d-block fw-semibold">Quartiers</span><span class="small text-white-50">Referentiel quartiers</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_SUB_NEIGHBORHOODS_VIEW') || $canAccess('SA_SUB_NEIGHBORHOODS_MANAGE'))
                    <a href="{{ route('super-admin.sub-neighborhoods.index') }}" class="nav-pill {{ request()->routeIs('super-admin.sub-neighborhoods.*') ? 'active' : '' }}">
                        <span class="nav-icon">SQ</span>
                        <span><span class="d-block fw-semibold">Sous-quartiers</span><span class="small text-white-50">Referentiel sous-quartiers</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_BUSINESS_SECTORS_VIEW') || $canAccess('SA_BUSINESS_SECTORS_MANAGE'))
                    <a href="{{ route('super-admin.business-sectors.index') }}" class="nav-pill {{ request()->routeIs('super-admin.business-sectors.*') ? 'active' : '' }}">
                        <span class="nav-icon">SC</span>
                        <span><span class="d-block fw-semibold">Secteurs</span><span class="small text-white-50">Secteurs d’activité</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_ORGANIZATION_TYPES_VIEW') || $canAccess('SA_ORGANIZATION_TYPES_MANAGE'))
                    <a href="{{ route('super-admin.client-types.index') }}" class="nav-pill {{ request()->routeIs('super-admin.client-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">TC</span>
                        <span><span class="d-block fw-semibold">Sous Catégorie</span><span class="small text-white-50">Classification métier</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_FEATURES_VIEW') || $canAccess('SA_FEATURES_MANAGE'))
                    <a href="{{ route('super-admin.features.index') }}" class="nav-pill {{ request()->routeIs('super-admin.features.*') ? 'active' : '' }}">
                        <span class="nav-icon">FN</span>
                        <span><span class="d-block fw-semibold">Fonctionnalités</span><span class="small text-white-50">Modules activables</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_APPLICATIONS_VIEW') || $canAccess('SA_APPLICATIONS_MANAGE'))
                    <a href="{{ route('super-admin.applications.index') }}" class="nav-pill {{ request()->routeIs('super-admin.applications.*') ? 'active' : '' }}">
                        <span class="nav-icon">CAT</span>
                        <span><span class="d-block fw-semibold">Catégorie</span><span class="small text-white-50">MON NRJ, MON EAU, etc.</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_SIGNAL_TYPES_VIEW') || $canAccess('SA_SIGNAL_TYPES_MANAGE'))
                    <a href="{{ route('super-admin.signal-types.index') }}" class="nav-pill {{ request()->routeIs('super-admin.signal-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">SG</span>
                        <span><span class="d-block fw-semibold">Types de signaux</span><span class="small text-white-50">Catalogue public editable</span></span>
                    </a>
                    <a href="{{ route('super-admin.signal-sub-types.index') }}" class="nav-pill {{ request()->routeIs('super-admin.signal-sub-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">ST</span>
                        <span><span class="d-block fw-semibold">Sous-types de signal</span><span class="small text-white-50">Motifs proposés aux usagers</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_SLA_POLICIES_VIEW') || $canAccess('SA_SLA_POLICIES_MANAGE'))
                    <a href="{{ route('super-admin.sla-policies.index') }}" class="nav-pill {{ request()->routeIs('super-admin.sla-policies.*') ? 'active' : '' }}">
                        <span class="nav-icon">SL</span>
                        <span><span class="d-block fw-semibold">Délais cibles</span><span class="small text-white-50">Par sous-catégorie</span></span>
                    </a>
                @endif

                <div class="sidebar-label">Paiements & cartes</div>
                @if ($canAccess('SA_PRICING_MANAGE'))
                    <a href="{{ route('super-admin.pricing.edit') }}" class="nav-pill {{ request()->routeIs('super-admin.pricing.*') ? 'active' : '' }}">
                        <span class="nav-icon">TR</span>
                        <span><span class="d-block fw-semibold">Tarification</span><span class="small text-white-50">Montants et regles</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_SUBSCRIPTION_PLANS_VIEW') || $canAccess('SA_SUBSCRIPTION_PLANS_MANAGE'))
                    <a href="{{ route('super-admin.subscription-plans.index') }}" class="nav-pill {{ request()->routeIs('super-admin.subscription-plans.*') ? 'active' : '' }}">
                        <span class="nav-icon">AB</span>
                        <span><span class="d-block fw-semibold">Offres d’abonnement</span><span class="small text-white-50">Abonnement annuel usager</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_PRIVILEGE_CARD_TYPES_VIEW') || $canAccess('SA_PRIVILEGE_CARD_TYPES_MANAGE') || $canAccess('SA_SUBSCRIPTION_PLANS_VIEW') || $canAccess('SA_SUBSCRIPTION_PLANS_MANAGE'))
                    <details class="nav-dropdown" {{ request()->routeIs('super-admin.privilege-card-types.*') ? 'open' : '' }}>
                        <summary class="nav-pill {{ request()->routeIs('super-admin.privilege-card-types.*') ? 'active' : '' }}">
                            <span class="nav-icon">PR</span>
                            <span><span class="d-block fw-semibold">Cartes privilèges</span><span class="small text-white-50">Gestion des cartes</span></span>
                            <span class="dropdown-caret">▼</span>
                        </summary>
                        <div class="nav-submenu">
                            <a href="{{ route('super-admin.privilege-card-types.index') }}" class="nav-subitem">Types de cartes</a>
                            <a href="{{ route('super-admin.privilege-card-types.issued-cards') }}" class="nav-subitem">Cartes émises</a>
                            <a href="{{ route('super-admin.privilege-card-types.purchases') }}" class="nav-subitem">Historique des achats</a>
                            <a href="{{ route('super-admin.privilege-card-types.scans') }}" class="nav-subitem">Historique des scans</a>
                        </div>
                    </details>
                @endif
                @if ($canAccess('SA_UP_SUBSCRIPTIONS_VIEW'))
                    <a href="{{ route('super-admin.up-subscriptions.index') }}" class="nav-pill {{ request()->routeIs('super-admin.up-subscriptions.*') ? 'active' : '' }}">
                        <span class="nav-icon">HU</span>
                        <span><span class="d-block fw-semibold">Abonnements usagers</span><span class="small text-white-50">Historique et états</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_PAYMENTS_VIEW'))
                    <a href="{{ route('super-admin.payments.index') }}" class="nav-pill {{ request()->routeIs('super-admin.payments.*') ? 'active' : '' }}">
                        <span class="nav-icon">PY</span>
                        <span><span class="d-block fw-semibold">Paiements</span><span class="small text-white-50">Historique des transactions</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_DISCOUNT_CARDS_VIEW'))
                    <a href="{{ route('super-admin.discount-cards.index') }}" class="nav-pill {{ request()->routeIs('super-admin.discount-cards.*') ? 'active' : '' }}">
                        <span class="nav-icon">CR</span>
                        <span><span class="d-block fw-semibold">Cartes de réduction</span><span class="small text-white-50">Cartes usagers générées</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_DISCOUNT_TRANSACTIONS_VIEW'))
                    <a href="{{ route('super-admin.discount-transactions.index') }}" class="nav-pill {{ request()->routeIs('super-admin.discount-transactions.*') ? 'active' : '' }}">
                        <span class="nav-icon">RD</span>
                        <span><span class="d-block fw-semibold">Réductions appliquées</span><span class="small text-white-50">Historique partenaire</span></span>
                    </a>
                @endif

                <div class="sidebar-label">Usagers & signalements</div>
                @if ($canAccess('SA_PUBLIC_USER_TYPES_VIEW') || $canAccess('SA_PUBLIC_USER_TYPES_MANAGE'))
                    <a href="{{ route('super-admin.public-user-types.index') }}" class="nav-pill {{ request()->routeIs('super-admin.public-user-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">US</span>
                        <span><span class="d-block fw-semibold">Types d’usagers</span><span class="small text-white-50">Profils publics</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_PUBLIC_USERS_VIEW') || $canAccess('SA_PUBLIC_USERS_MANAGE'))
                    <a href="{{ route('super-admin.public-users.index') }}" class="nav-pill {{ request()->routeIs('super-admin.public-users.*') ? 'active' : '' }}">
                        <span class="nav-icon">PU</span>
                        <span><span class="d-block fw-semibold">Usagers publics</span><span class="small text-white-50">Comptes publics</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_PUBLIC_REPORTS_VIEW'))
                    <a href="{{ route('super-admin.public-reports.index') }}" class="nav-pill {{ request()->routeIs('super-admin.public-reports.*') ? 'active' : '' }}">
                        <span class="nav-icon">SR</span>
                        <span><span class="d-block fw-semibold">Signalements publics</span><span class="small text-white-50">Liste des signalements usagers</span></span>
                    </a>
                @endif
                @if ($authUser?->is_super_admin || $layoutPermissionCodes->contains('SA_ACTIVITY_LOGS_VIEW_SELF') || $layoutPermissionCodes->contains('SA_ACTIVITY_LOGS_VIEW_INSTITUTION') || $layoutPermissionCodes->contains('SA_ACTIVITY_LOGS_VIEW_PUBLIC') || $layoutPermissionCodes->contains('SA_ACTIVITY_LOGS_VIEW_INTERNAL'))
                    <a href="{{ route('super-admin.activity-logs.index') }}" class="nav-pill {{ request()->routeIs('super-admin.activity-logs.*') ? 'active' : '' }}">
                        <span class="nav-icon">LG</span>
                        <span><span class="d-block fw-semibold">Journaux d’activité</span><span class="small text-white-50">Historique des actions</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_REX_FEEDBACKS_VIEW'))
                    <a href="{{ route('super-admin.rex-feedbacks.index') }}" class="nav-pill {{ request()->routeIs('super-admin.rex-feedbacks.*') ? 'active' : '' }}">
                        <span class="nav-icon">RX</span>
                        <span><span class="d-block fw-semibold">Avis des usagers</span><span class="small text-white-50">Retours d’expérience</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_REPARATION_CASES_MANAGE'))
                    <a href="{{ route('super-admin.reparation-damages.index') }}" class="nav-pill {{ request()->routeIs('super-admin.reparation-damages.*') ? 'active' : '' }}">
                        <span class="nav-icon">DM</span>
                        <span><span class="d-block fw-semibold">Dommages à ouvrir</span><span class="small text-white-50">Signalements à passer en contentieux</span></span>
                    </a>
                    <a href="{{ route('super-admin.reparation-cases.index') }}" class="nav-pill {{ request()->routeIs('super-admin.reparation-cases.*') ? 'active' : '' }}">
                        <span class="nav-icon">DC</span>
                        <span><span class="d-block fw-semibold">Dossiers contentieux</span><span class="small text-white-50">Dossiers ouverts contre institutions</span></span>
                    </a>
                @endif
                <div class="sidebar-label">Portails & accès</div>
                @if ($canAccess('SA_ORGANIZATIONS_VIEW') || $canAccess('SA_ORGANIZATIONS_MANAGE'))
                    <a href="{{ route('super-admin.organizations.index') }}" class="nav-pill {{ request()->routeIs('super-admin.organizations.*') ? 'active' : '' }}">
                        <span class="nav-icon">IN</span>
                        <span><span class="d-block fw-semibold">Institution</span><span class="small text-white-50">CIE, SODECI, autres</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_INSTITUTION_ADMINS_VIEW') || $canAccess('SA_INSTITUTION_ADMINS_MANAGE'))
                    <a href="{{ route('super-admin.institution-admins.index') }}" class="nav-pill {{ request()->routeIs('super-admin.institution-admins.*') ? 'active' : '' }}">
                        <span class="nav-icon">AI</span>
                        <span><span class="d-block fw-semibold">Admins institutionnels</span><span class="small text-white-50">Admins racine des portails</span></span>
                    </a>
                @endif
                <div class="sidebar-label">Administration</div>
                @if ($canAccess('SA_SCOPED_USERS_MANAGE'))
                    <a href="{{ route('super-admin.scoped-users.index') }}" class="nav-pill {{ request()->routeIs('super-admin.scoped-users.*') ? 'active' : '' }}">
                        <span class="nav-icon">SA</span>
                        <span><span class="d-block fw-semibold">Utilisateurs SA</span><span class="small text-white-50">Comptes du back-office global</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_SCOPED_ROLES_MANAGE'))
                    <a href="{{ route('super-admin.scoped-roles.index') }}" class="nav-pill {{ request()->routeIs('super-admin.scoped-roles.*') ? 'active' : '' }}">
                        <span class="nav-icon">RS</span>
                        <span><span class="d-block fw-semibold">Rôles SA</span><span class="small text-white-50">Rôles du back-office global</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_SYSTEM_USERS_VIEW') || $canAccess('SA_SYSTEM_USERS_MANAGE'))
                    <a href="{{ route('super-admin.system-users.index') }}" class="nav-pill {{ request()->routeIs('super-admin.system-users.*') ? 'active' : '' }}">
                        <span class="nav-icon">AI</span>
                        <span><span class="d-block fw-semibold">Utilisateurs AI</span><span class="small text-white-50">Comptes rattaches aux institutions</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_ROLES_VIEW') || $canAccess('SA_ROLES_MANAGE'))
                    <a href="{{ route('super-admin.roles.index') }}" class="nav-pill {{ request()->routeIs('super-admin.roles.*') ? 'active' : '' }}">
                        <span class="nav-icon">RA</span>
                        <span><span class="d-block fw-semibold">Rôles AI</span><span class="small text-white-50">Profils et droits des institutions</span></span>
                    </a>
                @endif
                @if ($canAccess('SA_PERMISSIONS_VIEW') || $canAccess('SA_PERMISSIONS_MANAGE'))
                    <a href="{{ route('super-admin.permissions.index') }}" class="nav-pill {{ request()->routeIs('super-admin.permissions.*') ? 'active' : '' }}">
                        <span class="nav-icon">PM</span>
                        <span><span class="d-block fw-semibold">Permissions</span><span class="small text-white-50">Droits unitaires</span></span>
                    </a>
                @endif
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-card mb-3">
                    <div class="small text-white-50 mb-1">Session active</div>
                    <div class="fw-semibold">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route($logoutRoute) }}">
                    @csrf
                    <button type="submit" class="btn btn-sidebar w-100">Se déconnecter</button>
                </form>
            </div>
        </aside>

        <main class="content-area">
            <header class="topbar mb-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="small text-secondary fw-semibold mb-1">{{ $isInternalPortalUser ? 'Portail interne' : 'Back office central' }}</div>
                    <div class="h5 mb-1 fw-bold">@yield('page-title', $portalTitle)</div>
                    <div class="text-secondary small">@yield('page-description', $isInternalPortalUser ? 'Suivi opérationnel et traitement des dossiers My-Signal by UFC' : 'Paramétrage global My-Signal by UFC')</div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @yield('header-badges')
                </div>
            </header>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @php
                $firstPageError = null;

                if (($errors ?? null) instanceof \Illuminate\Support\ViewErrorBag && $errors->any()) {
                    $firstPageError = $errors->first();
                } elseif (is_array($errors ?? null) && $errors !== []) {
                    $firstErrorEntry = reset($errors);
                    $firstPageError = is_array($firstErrorEntry)
                        ? (string) (reset($firstErrorEntry) ?: 'Une erreur est survenue.')
                        : (string) $firstErrorEntry;
                }
            @endphp

            @if ($firstPageError)
                <div class="alert alert-danger">{{ $firstPageError }}</div>
            @endif

            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const firebaseWebConfig = @json(array_filter(config('services.firebase.web.config', []), fn ($value) => filled($value)));
            const firebaseWebVapidKey = @json(config('services.firebase.web.vapid_key'));
            const firebasePushEnabled = @json((bool) config('services.firebase.enabled'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const pushTokenStoreUrl = @json(route('super-admin.push-tokens.store'));

            document.querySelectorAll('form').forEach((form) => {
                const phoneFields = form.querySelectorAll('[data-phone-field]');

                if (!phoneFields.length) {
                    return;
                }

                const syncPhoneFields = () => {
                    phoneFields.forEach((field) => {
                        const dialCodeSelect = field.querySelector('[data-dial-code-select]');
                        const localPhoneInput = field.querySelector('input[name$="_local"]');
                        const hiddenPhoneInput = field.querySelector('input[type="hidden"][name]');

                        if (!dialCodeSelect || !localPhoneInput || !hiddenPhoneInput) {
                            return;
                        }

                        const localValue = String(localPhoneInput.value || '').replace(/\D+/g, '');
                        hiddenPhoneInput.value = localValue ? `${dialCodeSelect.value}${localValue}` : '';
                        localPhoneInput.value = localValue;
                    });
                };

                syncPhoneFields();
                form.addEventListener('submit', syncPhoneFields);
            });

            const sidebarMenu = document.querySelector('.sidebar-menu');
            const activeSidebarItem = sidebarMenu?.querySelector('.nav-pill.active');

            if (sidebarMenu && activeSidebarItem) {
                const offset = activeSidebarItem.offsetTop - (sidebarMenu.clientHeight / 2) + (activeSidebarItem.clientHeight / 2);

                sidebarMenu.scrollTo({
                    top: Math.max(0, offset),
                    behavior: 'instant',
                });
            }

            function hasFirebaseWebConfig() {
                return firebasePushEnabled
                    && firebaseWebVapidKey
                    && firebaseWebConfig.apiKey
                    && firebaseWebConfig.messagingSenderId
                    && firebaseWebConfig.appId;
            }

            function isPushSupportedInThisContext() {
                return 'Notification' in window
                    && 'serviceWorker' in navigator
                    && window.isSecureContext;
            }

            function getWebPushDeviceName() {
                const browserData = navigator.userAgentData?.brands
                    ?.map((brand) => `${brand.brand} ${brand.version}`)
                    .join(', ');
                const browserName = browserData || navigator.userAgent || 'Navigateur';

                return `${navigator.platform || 'Web'} - ${browserName}`.slice(0, 120);
            }

            async function registerBackofficeWebPushToken() {
                if (!hasFirebaseWebConfig() || !isPushSupportedInThisContext()) {
                    return;
                }

                try {
                    const permission = await Notification.requestPermission();

                    if (permission !== 'granted') {
                        console.info('[MYSIGNAL_BACKOFFICE_PUSH] permission', permission);
                        return;
                    }

                    const [{ initializeApp, getApp, getApps }, { getMessaging, getToken, isSupported, onMessage }] = await Promise.all([
                        import('https://www.gstatic.com/firebasejs/10.12.5/firebase-app.js'),
                        import('https://www.gstatic.com/firebasejs/10.12.5/firebase-messaging.js'),
                    ]);

                    if (!(await isSupported())) {
                        console.info('[MYSIGNAL_BACKOFFICE_PUSH] Firebase Messaging non supporte par ce navigateur.');
                        return;
                    }

                    const firebaseApp = getApps().length ? getApp() : initializeApp(firebaseWebConfig);
                    const messaging = getMessaging(firebaseApp);

                    onMessage(messaging, (messagePayload) => {
                        console.log('[MYSIGNAL_BACKOFFICE_PUSH] foreground payload', messagePayload);
                        const notification = messagePayload.notification || {};
                        const data = messagePayload.data || {};
                        const title = notification.title || data.title || 'MYSIGNAL';
                        const body = notification.body || data.body || '';

                        if (Notification.permission === 'granted') {
                            new Notification(title, {
                                body,
                                icon: '/favicon.ico',
                                badge: '/favicon.ico',
                                data,
                            });
                        }
                    });

                    const serviceWorkerRegistration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    const token = await getToken(messaging, {
                        vapidKey: firebaseWebVapidKey,
                        serviceWorkerRegistration,
                    });

                    if (!token) {
                        return;
                    }

                    const payload = {
                        token,
                        platform: 'web',
                        device_name: getWebPushDeviceName(),
                        app_version: 'backoffice-web',
                    };

                    const response = await fetch(pushTokenStoreUrl, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    console.log('[MYSIGNAL_BACKOFFICE_PUSH] token_save_response', await response.json().catch(() => ({})));
                } catch (error) {
                    console.error('[MYSIGNAL_BACKOFFICE_PUSH] configuration_error', error);
                }
            }

            void registerBackofficeWebPushToken();
        })();
    </script>
    <script>
        (() => {
            const permissionCodes = new Set(@json($layoutPermissionCodes->values()));
            const isSuperAdmin = @json((bool) ($authUser?->is_super_admin));

            if (isSuperAdmin) {
                return;
            }

            const modules = [
                ['/sa/countries', 'SA_COUNTRIES'],
                ['/sa/cities', 'SA_CITIES'],
                ['/sa/communes', 'SA_COMMUNES'],
                ['/sa/business-sectors', 'SA_BUSINESS_SECTORS'],
                ['/sa/client-types', 'SA_ORGANIZATION_TYPES'],
                ['/sa/features', 'SA_FEATURES'],
                ['/sa/applications', 'SA_APPLICATIONS'],
                ['/sa/signal-types', 'SA_SIGNAL_TYPES'],
                ['/sa/signal-sub-types', 'SA_SIGNAL_TYPES'],
                ['/sa/sla-policies', 'SA_SLA_POLICIES'],
                ['/sa/subscription-plans', 'SA_SUBSCRIPTION_PLANS'],
                ['/sa/privilege-card-types', 'SA_PRIVILEGE_CARD_TYPES'],
                ['/sa/public-user-types', 'SA_PUBLIC_USER_TYPES'],
                ['/sa/organizations', 'SA_ORGANIZATIONS'],
                ['/sa/institution-admins', 'SA_INSTITUTION_ADMINS'],
            ];

            const hasAny = (...codes) => codes.some((code) => permissionCodes.has(code));
            const normalizePath = (value) => {
                try {
                    return new URL(value, window.location.origin).pathname.replace(/\/+$/, '');
                } catch (error) {
                    return '';
                }
            };
            const moduleForPath = (path) => modules.find(([prefix]) => path === prefix || path.startsWith(`${prefix}/`));
            const removeModalAndTriggers = (modal) => {
                if (!modal?.id) {
                    return;
                }

                document.querySelectorAll(`[data-bs-target="#${modal.id}"], [href="#${modal.id}"]`).forEach((trigger) => trigger.remove());
                modal.remove();
            };
            const removeElement = (element) => {
                const modal = element.closest('.modal');

                if (modal) {
                    removeModalAndTriggers(modal);
                    return;
                }

                const formCard = element.closest('.sticky-form-card');

                if (formCard && element.tagName === 'FORM') {
                    formCard.innerHTML = '<div class="fw-bold mb-2">Creation</div><div class="text-secondary small">Lecture seule pour ce module.</div>';
                    return;
                }

                element.remove();
            };
            const methodOf = (form) => (form.querySelector('input[name="_method"]')?.value || form.method || 'GET').toUpperCase();
            const actionForForm = (path, method) => {
                if (path.endsWith('/toggle-status')) {
                    return 'TOGGLE_STATUS';
                }

                if (method === 'DELETE') {
                    return 'DELETE';
                }

                if (method === 'PUT' || method === 'PATCH') {
                    return 'UPDATE';
                }

                if (method === 'POST') {
                    return 'CREATE';
                }

                return null;
            };

            document.querySelectorAll('a[href]').forEach((link) => {
                const path = normalizePath(link.href);
                const moduleEntry = moduleForPath(path);

                if (!moduleEntry) {
                    return;
                }

                const [, prefix] = moduleEntry;
                const action = path.endsWith('/edit') ? 'UPDATE' : (path.endsWith('/create') ? 'CREATE' : null);

                if (action && !hasAny(`${prefix}_${action}`, `${prefix}_MANAGE`)) {
                    link.remove();
                }
            });

            document.querySelectorAll('form[action]').forEach((form) => {
                const path = normalizePath(form.action);
                const moduleEntry = moduleForPath(path);

                if (!moduleEntry) {
                    return;
                }

                const [, prefix] = moduleEntry;
                const action = actionForForm(path, methodOf(form));

                if (action && !hasAny(`${prefix}_${action}`, `${prefix}_MANAGE`)) {
                    removeElement(form);
                }
            });
        })();
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
