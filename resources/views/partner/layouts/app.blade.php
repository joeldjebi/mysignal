<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name').' | Portail partenaire')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --partner-deep: #16362f;
            --partner-green: #2d6a5a;
            --partner-gold: #d8b36a;
            --partner-mist: #eef3ef;
            --partner-card: rgba(255,255,255,.93);
            --partner-ink: #20312d;
            --partner-muted: #667b74;
        }
        body {
            background:
                radial-gradient(circle at top left, rgba(216,179,106,.16), transparent 24%),
                linear-gradient(180deg, var(--partner-mist) 0%, #f7f3ea 100%);
            color: var(--partner-ink);
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
            background: linear-gradient(180deg, rgba(12,41,35,.98), rgba(39,93,79,.96)), var(--partner-deep);
            color: rgba(255,255,255,.92);
            box-shadow: 0 28px 80px rgba(12,41,35,.24);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--partner-gold), #b88834);
            color: #fff;
            font-weight: 800;
        }
        .sidebar-label {
            color: rgba(255,255,255,.52);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .66rem;
            font-weight: 700;
            margin: .95rem 0 .55rem;
        }
        .sidebar-menu {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: .35rem;
        }
        .nav-pill {
            display: flex;
            align-items: center;
            gap: .7rem;
            text-decoration: none;
            color: rgba(255,255,255,.85);
            padding: .68rem .78rem;
            border-radius: 16px;
            transition: .18s ease;
            margin-bottom: .22rem;
        }
        .nav-pill:hover { background: rgba(255,255,255,.08); color: #fff; }
        .nav-pill.active {
            background: linear-gradient(135deg, rgba(216,179,106,.24), rgba(216,179,106,.10));
            color: #fff;
            border: 1px solid rgba(216,179,106,.24);
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
        .btn-sidebar:hover { background: rgba(255,255,255,.08); color: #fff; }
        .content-area { min-width: 0; }
        .topbar, .panel-card, .stat-card, .table-card {
            border: 1px solid rgba(22,54,47,.08);
            border-radius: 22px;
            background: var(--partner-card);
            box-shadow: 0 24px 70px rgba(22,54,47,.08);
        }
        .topbar { padding: .85rem 1rem; }
        .panel-card, .stat-card { padding: 1.1rem; }
        .badge-soft {
            background: rgba(216,179,106,.14);
            color: #7e5e1f;
            border-radius: 999px;
            padding: .42rem .72rem;
            font-weight: 700;
            font-size: .78rem;
        }
        .table-modern thead th {
            color: var(--partner-muted);
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom-color: rgba(22,54,47,.08);
        }
        .table-modern tbody td {
            border-bottom-color: rgba(22,54,47,.06);
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: .92rem;
            vertical-align: middle;
        }
        .meta-stack { display: grid; gap: .2rem; }
        .meta-title { font-weight: 700; color: var(--partner-deep); }
        .meta-subtitle { color: var(--partner-muted); font-size: .82rem; }
        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            padding: .34rem .6rem;
            font-size: .74rem;
            font-weight: 700;
            background: rgba(22,54,47,.06);
            color: var(--partner-deep);
        }
        .filter-bar {
            border: 1px solid rgba(22,54,47,.08);
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
        .table-meta { color: var(--partner-muted); font-size: .82rem; }
        .report-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .45rem; }
        .pagination { margin-bottom: 0; }
        .page-link { color: var(--partner-deep); border-color: rgba(22,54,47,.08); }
        .page-item.active .page-link { background: var(--partner-deep); border-color: var(--partner-deep); }
        @media (max-width: 1199.98px) {
            .dashboard-shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
        }
    </style>
</head>
<body>
    @php
        $authUser = auth()->user()?->loadMissing(['creator', 'permissions', 'roles.permissions']);
        $permissionCodes = collect($authUser?->permissions?->pluck('code')->all() ?? [])
            ->merge(collect($authUser?->roles ?? [])->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();
        $isRoot = (bool) ($authUser?->creator?->is_super_admin);
        $canManageOffers = $isRoot || $permissionCodes->contains('PARTNER_DISCOUNT_OFFERS_MANAGE');
        $canManageUsers = $isRoot || $permissionCodes->contains('PARTNER_USERS_MANAGE');
        $canViewHistory = $isRoot || $permissionCodes->contains('PARTNER_DISCOUNT_HISTORY_VIEW');
        $activeNav = $activeNav ?? 'dashboard';
    @endphp
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="brand-mark">{{ strtoupper(substr((string) ($organization?->code ?? 'PT'), 0, 2)) }}</div>
                <div>
                    <div class="small text-white-50 fw-semibold">SIGNAL ALERTE</div>
                    <div class="fw-bold fs-5">Portail partenaire</div>
                </div>
            </div>

            <div class="sidebar-menu">
                <div class="sidebar-label">Pilotage</div>
                <a href="{{ route('partner.dashboard') }}" class="nav-pill {{ $activeNav === 'dashboard' ? 'active' : '' }}">
                    <span class="nav-icon">DB</span>
                    <span>
                        <span class="d-block fw-semibold">Dashboard</span>
                        <span class="small text-white-50">Vue reductions</span>
                    </span>
                </a>

                <div class="sidebar-label">Reduction</div>
                @if ($canViewHistory)
                    <a href="{{ route('partner.discount-transactions.index') }}" class="nav-pill {{ $activeNav === 'transactions' ? 'active' : '' }}">
                        <span class="nav-icon">HI</span>
                        <span>
                            <span class="d-block fw-semibold">Historique</span>
                            <span class="small text-white-50">Scans et reductions</span>
                        </span>
                    </a>
                @endif
                <a href="{{ route('partner.offers.index') }}" class="nav-pill {{ $activeNav === 'offers' ? 'active' : '' }}">
                    <span class="nav-icon">OF</span>
                    <span>
                        <span class="d-block fw-semibold">Offres</span>
                        <span class="small text-white-50">Catalogue partenaire</span>
                    </span>
                </a>

                @if ($canManageUsers)
                    <div class="sidebar-label">Equipe mobile</div>
                    <a href="{{ route('partner.users.index') }}" class="nav-pill {{ $activeNav === 'users' ? 'active' : '' }}">
                        <span class="nav-icon">US</span>
                        <span>
                            <span class="d-block fw-semibold">Utilisateurs</span>
                            <span class="small text-white-50">Agents et admins</span>
                        </span>
                    </a>
                @endif
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-card mb-3">
                    <div class="fw-semibold">{{ auth()->user()?->name }}</div>
                    <div class="small text-white-50 mb-2">{{ auth()->user()?->email }}</div>
                    <div class="small text-white-50">{{ $organization?->name }}</div>
                </div>
                <form method="POST" action="{{ route('partner.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sidebar w-100">Se deconnecter</button>
                </form>
            </div>
        </aside>

        <main class="content-area">
            <header class="topbar mb-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="small text-secondary fw-semibold mb-1">Portail partenaire</div>
                    <div class="h5 mb-1 fw-bold">@yield('page-title', $organization?->name ?? 'Etablissement partenaire')</div>
                    <div class="text-secondary small">@yield('page-description', 'Gestion des offres, des reductions appliquees et des utilisateurs mobiles.') </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-soft">{{ $organization?->code }}</span>
                    @yield('header-badges')
                </div>
            </header>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
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
</body>
</html>
