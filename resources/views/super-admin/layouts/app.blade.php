<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name').' | Super Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --acepen-navy: #0f2940;
            --acepen-blue: #194b70;
            --acepen-gold: #c49b48;
            --acepen-mist: #edf3f8;
            --acepen-card: rgba(255, 255, 255, .92);
            --acepen-ink: #1f2933;
            --acepen-muted: #6b7c93;
        }
        body {
            background:
                radial-gradient(circle at top right, rgba(196,155,72,.14), transparent 24%),
                linear-gradient(180deg, var(--acepen-mist) 0%, #f7f2e8 100%);
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
            background: linear-gradient(180deg, rgba(12,34,52,.98), rgba(22,63,92,.96)), var(--acepen-navy);
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
            background: linear-gradient(135deg, var(--acepen-gold), #a97824);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 16px 32px rgba(196,155,72,.28);
        }
        .sidebar-label {
            color: rgba(255,255,255,.5);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .66rem;
            font-weight: 700;
            margin: .95rem 0 .55rem;
        }
        .nav-pill {
            display: flex;
            align-items: center;
            gap: .7rem;
            text-decoration: none;
            color: rgba(255,255,255,.84);
            padding: .68rem .78rem;
            border-radius: 16px;
            transition: .18s ease;
            margin-bottom: .22rem;
        }
        .nav-pill:hover { background: rgba(255,255,255,.08); color: #fff; }
        .nav-pill.active {
            background: linear-gradient(135deg, rgba(196,155,72,.26), rgba(196,155,72,.12));
            color: #fff;
            border: 1px solid rgba(196,155,72,.24);
        }
        .nav-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.08);
            font-size: .72rem;
            font-weight: 800;
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
        $authUser = auth()->user();
        $isInternalPortalUser = $authUser && ! $authUser->is_super_admin;
        $portalTitle = $isInternalPortalUser ? 'Backoffice' : 'Super Admin';
        $portalDescription = $isInternalPortalUser
            ? 'Espace operationnel reserve aux utilisateurs internes autorises.'
            : 'Parametrage global, gouvernance et referentiels de la plateforme.';
        $logoutRoute = $isInternalPortalUser ? 'backoffice.logout' : 'super-admin.logout';
    @endphp
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="brand-mark">SA</div>
                    <div>
                        <div class="small text-white-50 fw-semibold">ACEPEN ALERTE</div>
                        <div class="fw-bold fs-5">{{ $portalTitle }}</div>
                    </div>
                </div>
                <div class="small text-white-50">{{ $portalDescription }}</div>
            </div>

            <div class="sidebar-menu">
                <div class="sidebar-label">Pilotage</div>
                @if ($authUser?->hasPermissionCode('SA_DASHBOARD_VIEW'))
                    <a href="{{ route('super-admin.dashboard') }}" class="nav-pill {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">DB</span>
                        <span><span class="d-block fw-semibold">Dashboard</span><span class="small text-white-50">Vue d'ensemble</span></span>
                    </a>
                @endif

                <div class="sidebar-label">Geographie</div>
                @if ($authUser?->hasPermissionCode('SA_COUNTRIES_MANAGE'))
                    <a href="{{ route('super-admin.countries.index') }}" class="nav-pill {{ request()->routeIs('super-admin.countries.*') ? 'active' : '' }}">
                        <span class="nav-icon">PY</span>
                        <span><span class="d-block fw-semibold">Pays</span><span class="small text-white-50">Referentiel pays</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_CITIES_MANAGE'))
                    <a href="{{ route('super-admin.cities.index') }}" class="nav-pill {{ request()->routeIs('super-admin.cities.*') ? 'active' : '' }}">
                        <span class="nav-icon">VL</span>
                        <span><span class="d-block fw-semibold">Villes</span><span class="small text-white-50">Referentiel villes</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_COMMUNES_MANAGE'))
                    <a href="{{ route('super-admin.communes.index') }}" class="nav-pill {{ request()->routeIs('super-admin.communes.*') ? 'active' : '' }}">
                        <span class="nav-icon">CM</span>
                        <span><span class="d-block fw-semibold">Communes</span><span class="small text-white-50">Referentiel communes</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_BUSINESS_SECTORS_MANAGE'))
                    <a href="{{ route('super-admin.business-sectors.index') }}" class="nav-pill {{ request()->routeIs('super-admin.business-sectors.*') ? 'active' : '' }}">
                        <span class="nav-icon">SC</span>
                        <span><span class="d-block fw-semibold">Secteurs</span><span class="small text-white-50">Secteurs d activite</span></span>
                    </a>
                @endif

                <div class="sidebar-label">Metier</div>
                @if ($authUser?->hasPermissionCode('SA_ORGANIZATION_TYPES_MANAGE'))
                    <a href="{{ route('super-admin.client-types.index') }}" class="nav-pill {{ request()->routeIs('super-admin.client-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">TC</span>
                        <span><span class="d-block fw-semibold">Types d'organisation</span><span class="small text-white-50">Classes d'institutions</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_FEATURES_MANAGE'))
                    <a href="{{ route('super-admin.features.index') }}" class="nav-pill {{ request()->routeIs('super-admin.features.*') ? 'active' : '' }}">
                        <span class="nav-icon">FN</span>
                        <span><span class="d-block fw-semibold">Fonctionnalites</span><span class="small text-white-50">Modules activables</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_APPLICATIONS_MANAGE'))
                    <a href="{{ route('super-admin.applications.index') }}" class="nav-pill {{ request()->routeIs('super-admin.applications.*') ? 'active' : '' }}">
                        <span class="nav-icon">AP</span>
                        <span><span class="d-block fw-semibold">Applications</span><span class="small text-white-50">MON NRJ, MON EAU, etc.</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_SIGNAL_TYPES_MANAGE'))
                    <a href="{{ route('super-admin.signal-types.index') }}" class="nav-pill {{ request()->routeIs('super-admin.signal-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">SG</span>
                        <span><span class="d-block fw-semibold">Types de signaux</span><span class="small text-white-50">Catalogue public editable</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_SLA_POLICIES_MANAGE'))
                    <a href="{{ route('super-admin.sla-policies.index') }}" class="nav-pill {{ request()->routeIs('super-admin.sla-policies.*') ? 'active' : '' }}">
                        <span class="nav-icon">SL</span>
                        <span><span class="d-block fw-semibold">SLA cibles</span><span class="small text-white-50">Par type d'organisation</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_PRICING_MANAGE'))
                    <a href="{{ route('super-admin.pricing.edit') }}" class="nav-pill {{ request()->routeIs('super-admin.pricing.*') ? 'active' : '' }}">
                        <span class="nav-icon">TR</span>
                        <span><span class="d-block fw-semibold">Tarification</span><span class="small text-white-50">Montants et regles</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_SUBSCRIPTION_PLANS_MANAGE'))
                    <a href="{{ route('super-admin.subscription-plans.index') }}" class="nav-pill {{ request()->routeIs('super-admin.subscription-plans.*') ? 'active' : '' }}">
                        <span class="nav-icon">AB</span>
                        <span><span class="d-block fw-semibold">Plans abonnements</span><span class="small text-white-50">Abonnement annuel UP</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_UP_SUBSCRIPTIONS_VIEW'))
                    <a href="{{ route('super-admin.up-subscriptions.index') }}" class="nav-pill {{ request()->routeIs('super-admin.up-subscriptions.*') ? 'active' : '' }}">
                        <span class="nav-icon">HU</span>
                        <span><span class="d-block fw-semibold">Abonnements UP</span><span class="small text-white-50">Historique et statuts</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_PUBLIC_USER_TYPES_MANAGE'))
                    <a href="{{ route('super-admin.public-user-types.index') }}" class="nav-pill {{ request()->routeIs('super-admin.public-user-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">UP</span>
                        <span><span class="d-block fw-semibold">Types usagers publics</span><span class="small text-white-50">UP, UPE et futurs profils</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_PUBLIC_USERS_MANAGE'))
                    <a href="{{ route('super-admin.public-users.index') }}" class="nav-pill {{ request()->routeIs('super-admin.public-users.*') ? 'active' : '' }}">
                        <span class="nav-icon">PU</span>
                        <span><span class="d-block fw-semibold">Usagers publics</span><span class="small text-white-50">UP et UPE</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_PUBLIC_REPORTS_VIEW'))
                    <a href="{{ route('super-admin.public-reports.index') }}" class="nav-pill {{ request()->routeIs('super-admin.public-reports.*') ? 'active' : '' }}">
                        <span class="nav-icon">SR</span>
                        <span><span class="d-block fw-semibold">Signalements publics</span><span class="small text-white-50">Liste des signalements UP</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_PAYMENTS_VIEW'))
                    <a href="{{ route('super-admin.payments.index') }}" class="nav-pill {{ request()->routeIs('super-admin.payments.*') ? 'active' : '' }}">
                        <span class="nav-icon">PY</span>
                        <span><span class="d-block fw-semibold">Paiements</span><span class="small text-white-50">Historique des transactions</span></span>
                    </a>
                @endif
                @if ($authUser?->is_super_admin || $authUser?->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_SELF') || $authUser?->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_INSTITUTION') || $authUser?->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_PUBLIC') || $authUser?->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_INTERNAL'))
                    <a href="{{ route('super-admin.activity-logs.index') }}" class="nav-pill {{ request()->routeIs('super-admin.activity-logs.*') ? 'active' : '' }}">
                        <span class="nav-icon">LG</span>
                        <span><span class="d-block fw-semibold">Journaux d activite</span><span class="small text-white-50">Historique des actions</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_REPARATION_CASES_MANAGE'))
                    <a href="{{ route('super-admin.reparation-cases.index') }}" class="nav-pill {{ request()->routeIs('super-admin.reparation-cases.*') ? 'active' : '' }}">
                        <span class="nav-icon">RP</span>
                        <span><span class="d-block fw-semibold">Reparations</span><span class="small text-white-50">Dossiers ouverts contre organisations</span></span>
                    </a>
                @endif

                <div class="sidebar-label">Portails</div>
                @if ($authUser?->hasPermissionCode('SA_ORGANIZATIONS_MANAGE'))
                    <a href="{{ route('super-admin.organizations.index') }}" class="nav-pill {{ request()->routeIs('super-admin.organizations.*') ? 'active' : '' }}">
                        <span class="nav-icon">OR</span>
                        <span><span class="d-block fw-semibold">Organisations</span><span class="small text-white-50">CIE, SODECI, autres</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_INSTITUTION_ADMINS_MANAGE'))
                    <a href="{{ route('super-admin.institution-admins.index') }}" class="nav-pill {{ request()->routeIs('super-admin.institution-admins.*') ? 'active' : '' }}">
                        <span class="nav-icon">AI</span>
                        <span><span class="d-block fw-semibold">Admins institutionnels</span><span class="small text-white-50">Admins racine des portails</span></span>
                    </a>
                @endif

                <div class="sidebar-label">Acces</div>
                @if ($authUser?->hasPermissionCode('SA_SYSTEM_USERS_MANAGE'))
                    <a href="{{ route('super-admin.system-users.index') }}" class="nav-pill {{ request()->routeIs('super-admin.system-users.*') ? 'active' : '' }}">
                        <span class="nav-icon">US</span>
                        <span><span class="d-block fw-semibold">Utilisateurs internes</span><span class="small text-white-50">Huissiers, avocats et autres profils</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_ROLES_MANAGE'))
                    <a href="{{ route('super-admin.roles.index') }}" class="nav-pill {{ request()->routeIs('super-admin.roles.*') ? 'active' : '' }}">
                        <span class="nav-icon">RL</span>
                        <span><span class="d-block fw-semibold">Roles</span><span class="small text-white-50">Profils et droits groupés</span></span>
                    </a>
                @endif
                @if ($authUser?->hasPermissionCode('SA_PERMISSIONS_MANAGE'))
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
                    <button type="submit" class="btn btn-sidebar w-100">Se deconnecter</button>
                </form>
            </div>
        </aside>

        <main class="content-area">
            <header class="topbar mb-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="small text-secondary fw-semibold mb-1">{{ $isInternalPortalUser ? 'Portail interne' : 'Back office central' }}</div>
                    <div class="h5 mb-1 fw-bold">@yield('page-title', $portalTitle)</div>
                    <div class="text-secondary small">@yield('page-description', $isInternalPortalUser ? 'Suivi operationnel et traitement des dossiers ACEPEN ALERTE' : 'Parametrage global ACEPEN ALERTE')</div>
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
        })();
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
