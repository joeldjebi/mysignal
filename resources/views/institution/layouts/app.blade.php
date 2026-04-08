<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name').' | Portail institutionnel')</title>
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
            background:
                linear-gradient(180deg, rgba(12,34,52,.98), rgba(22,63,92,.96)),
                var(--acepen-navy);
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
        .nav-pill:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }
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
        .btn-sidebar:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        .content-area { min-width: 0; }
        .topbar, .hero-card, .panel-card, .stat-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 22px;
            background: var(--acepen-card);
            box-shadow: 0 24px 70px rgba(16,42,67,.08);
        }
        .topbar {
            padding: .85rem 1rem;
            z-index: 30;
            backdrop-filter: blur(18px);
        }
        .hero-card {
            background: linear-gradient(145deg, var(--acepen-navy), var(--acepen-blue));
            color: #fff;
            overflow: hidden;
            position: relative;
        }
        .hero-card::after {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            top: -120px;
            right: -120px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,.18), transparent 62%);
        }
        .hero-strip {
            border-radius: 16px;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.12);
            padding: .85rem;
        }
        .panel-card, .stat-card { padding: 1.1rem; }
        .chart-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 24px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 22px 60px rgba(16,42,67,.08);
            padding: 1rem 1rem .75rem;
            height: 100%;
        }
        .chart-frame { min-height: 300px; }
        .map-frame {
            min-height: 360px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(16,42,67,.08);
        }
        .insight-list {
            display: grid;
            gap: .75rem;
        }
        .insight-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .8rem .9rem;
            border-radius: 16px;
            background: rgba(237,243,248,.85);
            border: 1px solid rgba(16,42,67,.05);
        }
        .progress-soft {
            height: 8px;
            border-radius: 999px;
            background: rgba(16,42,67,.08);
            overflow: hidden;
        }
        .progress-soft > span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--acepen-gold), var(--acepen-blue));
        }
        .stat-kicker {
            color: var(--acepen-muted);
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .stat-value {
            font-size: 1.6rem;
            line-height: 1;
            font-weight: 800;
            color: var(--acepen-navy);
            margin: .45rem 0 .25rem;
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
        .table-modern tbody tr:hover {
            background: rgba(237,243,248,.55);
        }
        .surface-soft {
            border: 1px solid rgba(16,42,67,.06);
            border-radius: 20px;
            background: rgba(255,255,255,.78);
            padding: .9rem;
        }
        .meta-stack {
            display: grid;
            gap: .2rem;
        }
        .meta-title {
            font-weight: 700;
            color: var(--acepen-navy);
        }
        .meta-subtitle {
            color: var(--acepen-muted);
            font-size: .8rem;
        }
        .chip-success {
            background: rgba(31,122,79,.12);
            color: #1f7a4f;
        }
        .chip-warning {
            background: rgba(196,155,72,.14);
            color: #8a671d;
        }
        .chip-danger {
            background: rgba(201,95,95,.14);
            color: #b03c3c;
        }
        .chip-neutral {
            background: rgba(107,124,147,.12);
            color: #556578;
        }
        .table-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 24px;
            background: rgba(255,255,255,.88);
            overflow: hidden;
        }
        .report-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: .45rem;
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
        .pagination { margin-bottom: 0; }
        .page-link {
            color: var(--acepen-navy);
            border-color: rgba(16,42,67,.08);
        }
        .page-item.active .page-link {
            background: var(--acepen-navy);
            border-color: var(--acepen-navy);
        }
        @media (max-width: 1199.98px) {
            .dashboard-shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
        }
    </style>
</head>
<body>
    @php
        $featureCodes = $features ?? [];
        $authUser = auth()->user()?->loadMissing(['creator', 'permissions', 'roles.permissions']);
        $application = $application ?? $organization?->application;
        $userPermissionCodes = collect($authUser?->permissions?->pluck('code')->all() ?? [])
            ->merge(collect($authUser?->roles ?? [])->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();
        $isInstitutionRootAdmin = (bool) ($authUser?->creator?->is_super_admin);
        $canViewMeters = in_array('PUBLIC_METERS', $featureCodes, true);
        $canViewReports = in_array('PUBLIC_REPORTS', $featureCodes, true);
        $canViewStatistics = in_array('PUBLIC_REPORT_STATISTICS', $featureCodes, true);
        $canViewReportUsers = in_array('PUBLIC_REPORT_USERS', $featureCodes, true);
        $canViewSla = in_array('INSTITUTION_SLA_ACCESS', $featureCodes, true);
        $canManageSignalTypes = in_array('INSTITUTION_SIGNAL_TYPES_ACCESS', $featureCodes, true);
        $canViewDamages = in_array('INSTITUTION_REPORT_DAMAGE_ACCESS', $featureCodes, true);
        $canManageInstitutionUsers = $isInstitutionRootAdmin || $userPermissionCodes->contains('INSTITUTION_MANAGE_USERS');
        $canManageInstitutionRoles = $isInstitutionRootAdmin || $userPermissionCodes->contains('INSTITUTION_MANAGE_ROLES');
        $canManageInstitutionPermissions = $isInstitutionRootAdmin || $userPermissionCodes->contains('INSTITUTION_MANAGE_PERMISSIONS');
        $activeNav = $activeNav ?? 'dashboard';
    @endphp
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="brand-mark">{{ strtoupper(substr((string) ($organization?->code ?? 'IN'), 0, 2)) }}</div>
                    <div>
                        <div class="small text-white-50 fw-semibold">{{ $application?->name ?? 'SIGNAL ALERTE' }}</div>
                        <div class="fw-bold fs-5">Portail institutionnel</div>
                    </div>
                </div>
            </div>

            <div class="sidebar-menu">
                <div class="sidebar-label">Pilotage</div>
                <a href="{{ route('institution.dashboard') }}" class="nav-pill {{ $activeNav === 'dashboard' ? 'active' : '' }}">
                    <span class="nav-icon">DB</span>
                    <span>
                        <span class="d-block fw-semibold">Dashboard</span>
                        <span class="small text-white-50">Vue d'ensemble</span>
                    </span>
                </a>

                @if ($canViewReports)
                    <div class="sidebar-label">Signalements</div>
                    <a href="{{ route('institution.reports.index') }}" class="nav-pill {{ $activeNav === 'reports' ? 'active' : '' }}">
                        <span class="nav-icon">FL</span>
                        <span>
                            <span class="d-block fw-semibold">File des signaux</span>
                            <span class="small text-white-50">Traitement chronologique</span>
                        </span>
                    </a>
                @endif

                @if ($canViewDamages)
                    <a href="{{ route('institution.damages.index') }}" class="nav-pill {{ $activeNav === 'damages' ? 'active' : '' }}">
                        <span class="nav-icon">DG</span>
                        <span>
                            <span class="d-block fw-semibold">Dommages</span>
                            <span class="small text-white-50">Declarations et traitement</span>
                        </span>
                    </a>
                @endif

                @if ($canViewReportUsers)
                    <div class="sidebar-label">Usagers</div>
                    <a href="{{ route('institution.report-users.index') }}" class="nav-pill {{ $activeNav === 'report-users' ? 'active' : '' }}">
                        <span class="nav-icon">US</span>
                        <span>
                            <span class="d-block fw-semibold">Usagers publics</span>
                            <span class="small text-white-50">Avec ou sans signalement</span>
                        </span>
                    </a>
                @endif

                @if ($canViewMeters)
                    <div class="sidebar-label">Compteurs</div>
                    <a href="{{ route('institution.meters.index') }}" class="nav-pill {{ $activeNav === 'meters' ? 'active' : '' }}">
                        <span class="nav-icon">CP</span>
                        <span>
                            <span class="d-block fw-semibold">Compteurs</span>
                            <span class="small text-white-50">Suivi des compteurs publics</span>
                        </span>
                    </a>
                @endif

                @if ($canViewStatistics)
                    <div class="sidebar-label">Analyse</div>
                    <a href="{{ route('institution.statistics.index') }}" class="nav-pill {{ $activeNav === 'statistics' ? 'active' : '' }}">
                        <span class="nav-icon">ST</span>
                        <span>
                            <span class="d-block fw-semibold">Statistiques</span>
                            <span class="small text-white-50">Indicateurs et vue qualite</span>
                        </span>
                    </a>
                @endif

                @if ($canViewSla || $canManageSignalTypes)
                    <div class="sidebar-label">Gouvernance</div>
                    @if ($canManageSignalTypes)
                    <a href="{{ route('institution.signal-types.index') }}" class="nav-pill {{ $activeNav === 'signal-types' ? 'active' : '' }}">
                        <span class="nav-icon">SG</span>
                        <span>
                            <span class="d-block fw-semibold">Types de signaux</span>
                            <span class="small text-white-50">Catalogue du reseau</span>
                        </span>
                    </a>
                    @endif
                    @if ($canViewSla)
                    <a href="{{ route('institution.sla.index') }}" class="nav-pill {{ $activeNav === 'sla' ? 'active' : '' }}">
                        <span class="nav-icon">SL</span>
                        <span>
                            <span class="d-block fw-semibold">SLA cibles</span>
                            <span class="small text-white-50">Referentiel programme</span>
                        </span>
                    </a>
                    @endif
                @endif

                @if ($canManageInstitutionUsers || $canManageInstitutionRoles || $canManageInstitutionPermissions)
                    <div class="sidebar-label">Administration</div>
                    @if ($canManageInstitutionUsers)
                        <a href="{{ route('institution.users.index') }}" class="nav-pill {{ $activeNav === 'users' ? 'active' : '' }}">
                            <span class="nav-icon">UT</span>
                            <span>
                                <span class="d-block fw-semibold">Users</span>
                                <span class="small text-white-50">Collaborateurs du portail</span>
                            </span>
                        </a>
                    @endif
                    @if ($canManageInstitutionRoles)
                        <a href="{{ route('institution.roles.index') }}" class="nav-pill {{ $activeNav === 'roles' ? 'active' : '' }}">
                            <span class="nav-icon">RL</span>
                            <span>
                                <span class="d-block fw-semibold">Roles</span>
                                <span class="small text-white-50">Profils et habilitations</span>
                            </span>
                        </a>
                    @endif
                    @if ($canManageInstitutionPermissions)
                        <a href="{{ route('institution.permissions.index') }}" class="nav-pill {{ $activeNav === 'permissions' ? 'active' : '' }}">
                            <span class="nav-icon">PM</span>
                            <span>
                                <span class="d-block fw-semibold">Permissions</span>
                                <span class="small text-white-50">Fonctions autorisees par le SA</span>
                            </span>
                        </a>
                    @endif
                @endif

                <div class="sidebar-label">Compte</div>
                <a href="{{ route('institution.profile.edit') }}" class="nav-pill {{ $activeNav === 'profile' ? 'active' : '' }}">
                    <span class="nav-icon">PR</span>
                    <span>
                        <span class="d-block fw-semibold">Mon profil</span>
                        <span class="small text-white-50">Infos personnelles et acces</span>
                    </span>
                </a>
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-card mb-3">
                    <div class="fw-semibold">{{ auth()->user()?->name }}</div>
                    <div class="small text-white-50 mb-2">{{ auth()->user()?->email }}</div>
                    <div class="small text-white-50 mt-2">{{ count($featureCodes) }} fonctionnalite(s) active(s)</div>
                </div>
                <form method="POST" action="{{ route('institution.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sidebar w-100">Se deconnecter</button>
                </form>
            </div>
        </aside>

        <main class="content-area">
            <header class="topbar mb-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="small text-secondary fw-semibold mb-1">Portail institutionnel</div>
                    <div class="h5 mb-1 fw-bold">@yield('page-title', $organization?->name ?? 'Organisation')</div>
                    <div class="text-secondary small">@yield('page-description', 'Le portail affiche automatiquement le bon univers selon l institution du compte connecte.')</div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if ($organization?->portal_key)
                        <span class="badge-soft">{{ $organization->portal_key }}</span>
                    @endif
                    @if ($application?->name)
                        <span class="badge-soft">{{ $application->name }}</span>
                    @endif
                    <span class="badge-soft">{{ $organization?->code }}</span>
                    @yield('header-badges')
                </div>
            </header>

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
</body>
</html>
