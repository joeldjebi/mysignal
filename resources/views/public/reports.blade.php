<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Signalements - My-Signal</title>
  <link rel="icon" type="image/png" href="{{ asset('image/logo/logo-my-signal.png') }}" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  @php
    $landingBlocks = $landingBlocks ?? collect();
    $landingBlock = fn (string $key) => $landingBlocks->get($key);
    $blockBody = fn (string $key, string $default = '') => optional($landingBlock($key))->body ?: $default;
    $blockMeta = fn (string $key, string $field, $default = '') => $landingBlock($key)?->meta[$field] ?? $default;
    $lines = function (?string $value): array {
      return collect(preg_split('/\r\n|\r|\n/', (string) $value))
        ->map(fn ($line) => trim($line))
        ->filter()
        ->values()
        ->all();
    };
    $parts = fn (string $line, int $limit = 2) => array_pad(array_map('trim', explode('|', $line, $limit)), $limit, '');
    $settingsMeta = $landingBlocks->get('settings')?->meta ?? [];
    $primaryColor = $settingsMeta['primary_color'] ?? '#183447';
    $secondaryColor = $settingsMeta['secondary_color'] ?? '#256f8f';
    $accentColor = $settingsMeta['accent_color'] ?? '#ff0068';
    $navigationLines = $lines($blockBody('navigation', "Accueil | /\nQui sommes-nous ? | /qui-sommes-nous\nNos domaines | /#domains\nFonctionnalités | /#features\nMy-Signal TV | /my-signal-tv\nSignalements | /signalements\nFAQ | /faq\nContactez-nous | /contactez-nous"));

    if (! collect($navigationLines)->contains(fn ($line) => str_contains(strtolower($line), '/signalements'))) {
      $navigationLines[] = 'Signalements | /signalements';
    }

    $statusLabels = [
      'submitted' => 'Soumis',
      'in_progress' => 'En cours',
      'resolved' => 'Resolus',
      'rejected' => 'Rejete',
    ];
  @endphp
  <style>
    :root {
      --primary: {{ $primaryColor }};
      --secondary: {{ $secondaryColor }};
      --accent: {{ $accentColor }};
      --text: #183447;
      --muted: #647887;
      --soft: #f4f9fb;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      color: var(--text);
      background: var(--soft);
    }
    .navbar {
      background: #fff;
      box-shadow: 0 2px 20px rgba(24,52,71,.08);
      padding: 14px 0;
      position: sticky;
      top: 0;
      z-index: 999;
    }
    .navbar-brand img { height: 42px; width: auto; display: block; }
    .nav-link {
      color: var(--text) !important;
      font-weight: 500;
      font-size: .82rem;
      padding: 6px 8px !important;
      white-space: nowrap;
    }
    .nav-link:hover,
    .nav-link.active { color: var(--primary) !important; }
    .btn-nav {
      background: var(--accent);
      color: #fff !important;
      border-radius: 30px;
      padding: 8px 16px !important;
      font-weight: 700;
    }
    .page-hero {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: #fff;
      padding: 74px 0 62px;
      position: relative;
      overflow: hidden;
    }
    .page-hero::after {
      content: '';
      position: absolute;
      width: 360px;
      height: 360px;
      right: -120px;
      top: -120px;
      border-radius: 999px;
      background: rgba(255,255,255,.08);
    }
    .page-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(255,255,255,.12);
      color: rgba(255,255,255,.9);
      font-weight: 700;
      font-size: .82rem;
      margin-bottom: 18px;
    }
    h1 {
      font-size: clamp(2rem, 5vw, 4rem);
      font-weight: 800;
      line-height: 1.08;
      margin-bottom: 18px;
    }
    .lead { color: rgba(255,255,255,.82); max-width: 760px; }
    .reports-shell { padding: 58px 0 72px; }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 18px;
    }
    .stat-tile {
      border: 1px solid rgba(24,52,71,.08);
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 18px 45px rgba(24,52,71,.07);
      padding: 16px;
    }
    .stat-label {
      color: var(--muted);
      font-size: .72rem;
      font-weight: 800;
      text-transform: uppercase;
      margin-bottom: 8px;
    }
    .stat-value {
      color: var(--text);
      font-size: 1.55rem;
      font-weight: 800;
      line-height: 1;
    }
    .search-panel {
      border: 1px solid rgba(24,52,71,.08);
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 18px 45px rgba(24,52,71,.08);
      padding: 18px;
      margin-bottom: 18px;
    }
    .search-form {
      display: grid;
      grid-template-columns: 1.4fr repeat(5, minmax(140px, 1fr)) auto auto;
      gap: 10px;
      align-items: end;
    }
    .search-form label {
      display: block;
      color: var(--muted);
      font-size: .78rem;
      font-weight: 700;
      margin-bottom: 6px;
    }
    .search-form .form-control,
    .search-form .form-select {
      min-height: 44px;
      border-color: rgba(24,52,71,.14);
      font-size: .9rem;
    }
    .search-form .btn {
      min-height: 44px;
      border-radius: 8px;
      font-weight: 800;
      font-size: .86rem;
      padding-inline: 18px;
    }
    .btn-search {
      background: var(--accent);
      border-color: var(--accent);
      color: #fff;
    }
    .btn-search:hover {
      background: #e0005c;
      border-color: #e0005c;
      color: #fff;
    }
    .reports-panel {
      border: 1px solid rgba(24,52,71,.08);
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 18px 45px rgba(24,52,71,.08);
      overflow: hidden;
    }
    .reports-panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 18px 20px;
      border-bottom: 1px solid rgba(24,52,71,.08);
    }
    .reports-panel-title {
      font-weight: 800;
      color: var(--text);
    }
    .reports-panel-count {
      color: var(--muted);
      font-size: .82rem;
      font-weight: 700;
    }
    .reports-table {
      margin: 0;
      min-width: 900px;
    }
    .reports-table thead th {
      background: var(--soft);
      color: var(--muted);
      font-size: .74rem;
      font-weight: 800;
      padding: 12px 16px;
      text-transform: uppercase;
      vertical-align: middle;
      white-space: nowrap;
    }
    .reports-table tbody td {
      color: var(--text);
      font-size: .84rem;
      padding: 16px;
      vertical-align: top;
    }
    .reports-table tbody tr + tr td {
      border-top: 1px solid rgba(24,52,71,.08);
    }
    .report-reference {
      font-weight: 800;
      color: var(--primary);
      font-size: .98rem;
      word-break: break-word;
    }
    .report-date { color: var(--muted); font-size: .76rem; margin-top: 4px; }
    .report-subtitle {
      color: var(--muted);
      font-size: .78rem;
      margin-top: 4px;
    }
    .status-chip {
      display: inline-flex;
      align-items: center;
      width: fit-content;
      border-radius: 999px;
      padding: 6px 10px;
      background: rgba(37,111,143,.1);
      color: var(--secondary);
      font-size: .74rem;
      font-weight: 800;
      white-space: nowrap;
    }
    .cell-icon {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-width: 0;
    }
    .cell-icon i {
      color: var(--accent);
      font-size: .95rem;
    }
    .empty-state {
      padding: 42px;
      text-align: center;
      color: var(--muted);
    }
    .pagination-wrap {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 16px 20px;
      border-top: 1px solid rgba(24,52,71,.08);
    }
    .pagination { margin: 0; }
    .page-link {
      color: var(--primary);
      border-color: rgba(24,52,71,.12);
      font-size: .82rem;
      font-weight: 700;
    }
    .active > .page-link,
    .page-link.active {
      background: var(--accent);
      border-color: var(--accent);
    }
    footer {
      background: #102736;
      color: rgba(255,255,255,.74);
      padding: 28px 0;
      font-size: .82rem;
    }
    footer a { color: #fff; text-decoration: none; font-weight: 700; }
    @media (max-width: 1199.98px) {
      .navbar-collapse { padding-top: 16px; }
      .navbar-nav { align-items: stretch !important; gap: 8px !important; }
      .btn-nav { text-align: center; }
      .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .search-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
      .stats-grid,
      .search-form { grid-template-columns: 1fr; }
      .reports-shell { padding: 38px 0 54px; }
      .reports-panel-header { align-items: flex-start; flex-direction: column; }
      .pagination-wrap { align-items: flex-start; flex-direction: column; }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-xl">
    <div class="container">
      <a class="navbar-brand" href="{{ route('public.landing') }}">
        <img src="{{ asset('image/logo/logo-my-signal.png') }}" alt="My-Signal">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto align-items-center gap-1">
          @foreach ($navigationLines as $navLine)
            @php
              [$navLabel, $navUrl] = $parts($navLine, 2);
            @endphp
            <li class="nav-item">
              <a class="nav-link {{ $navUrl === '/signalements' ? 'active' : '' }}" href="{{ $navUrl ?: '#' }}">{{ $navLabel }}</a>
            </li>
          @endforeach
          <li class="nav-item ms-lg-2"><a class="nav-link btn-nav" href="{{ route('public.auth') }}">{{ $blockMeta('navigation', 'cta_label', 'Se connecter et signaler maintenant') }}</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main>
    <section class="page-hero">
      <div class="container position-relative">
        <div class="page-badge"><i class="bi bi-broadcast-pin"></i>Transparence</div>
        <h1>Derniers signalements</h1>
        <p class="lead">Consultez tous les signalements enregistres sur My-Signal durant les 30 derniers jours, sans donnees personnelles des usagers.</p>
      </div>
    </section>

    <section class="reports-shell">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-tile">
            <div class="stat-label">Signalements</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
          </div>
          <div class="stat-tile">
            <div class="stat-label">En cours</div>
            <div class="stat-value">{{ $stats['in_progress'] }}</div>
          </div>
          <div class="stat-tile">
            <div class="stat-label">Resolus</div>
            <div class="stat-value">{{ $stats['resolved'] }}</div>
          </div>
          <div class="stat-tile">
            <div class="stat-label">Avec dommage</div>
            <div class="stat-value">{{ $stats['damages'] }}</div>
          </div>
          <div class="stat-tile">
            <div class="stat-label">Communes</div>
            <div class="stat-value">{{ $stats['communes'] }}</div>
          </div>
        </div>

        <div class="search-panel">
          <form method="GET" action="{{ route('public.reports') }}" class="search-form">
            <div>
              <label for="search">Recherche</label>
              <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, type, statut, catégorie, institution, commune...">
            </div>
            <div>
              <label for="status">Statut</label>
              <select id="status" name="status" class="form-select">
                <option value="">Tous</option>
                @foreach ($statusLabels as $status => $label)
                  <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="application_id">Catégorie</label>
              <select id="application_id" name="application_id" class="form-select">
                <option value="">Toutes</option>
                @foreach ($applications as $application)
                  <option value="{{ $application->id }}" @selected((string) request('application_id') === (string) $application->id)>{{ $application->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="organization_id">Institution</label>
              <select id="organization_id" name="organization_id" class="form-select">
                <option value="">Toutes</option>
                @foreach ($organizations as $organization)
                  <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="commune_id">Commune</label>
              <select id="commune_id" name="commune_id" class="form-select">
                <option value="">Toutes</option>
                @foreach ($communes as $commune)
                  <option value="{{ $commune->id }}" @selected((string) request('commune_id') === (string) $commune->id)>{{ $commune->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="damage">Dommage</label>
              <select id="damage" name="damage" class="form-select">
                <option value="">Tous</option>
                <option value="with" @selected(request('damage') === 'with')>Avec</option>
                <option value="without" @selected(request('damage') === 'without')>Sans</option>
              </select>
            </div>
            <button type="submit" class="btn btn-search"><i class="bi bi-funnel-fill me-1"></i> Filtrer</button>
            @if (filled(request()->query()))
              <a href="{{ route('public.reports') }}" class="btn btn-outline-secondary">RAZ</a>
            @endif
          </form>
        </div>

        <div class="reports-panel">
          <div class="reports-panel-header">
            <div class="reports-panel-title">Liste des signalements</div>
            <div class="reports-panel-count">
              {{ $reports->total() }} resultat{{ $reports->total() > 1 ? 's' : '' }} depuis le {{ $stats['period_start']->format('d/m/Y') }}
            </div>
          </div>
          <div class="table-responsive">
            <table class="table reports-table align-middle">
              <thead>
                <tr>
                  <th>Signalement</th>
                  <th>Type</th>
                  <th>Catégorie</th>
                  <th>Institution</th>
                  <th>Commune</th>
                  <th>Statut</th>
                  <th>Dommage</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($reports as $report)
                  <tr>
                    <td>
                      <div class="report-reference">{{ $report->reference }}</div>
                      <div class="report-date">{{ $report->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                    </td>
                    <td>
                      <div>{{ $report->signal_label ?: $report->signal_code ?: $report->incident_type ?: 'Signalement' }}</div>
                      @if ($report->signal_code)
                        <div class="report-subtitle">{{ $report->signal_code }}</div>
                      @endif
                    </td>
                    <td><span class="cell-icon"><i class="bi bi-grid-1x2-fill"></i>{{ $report->application?->name ?: '-' }}</span></td>
                    <td><span class="cell-icon"><i class="bi bi-building-fill"></i>{{ $report->organization?->name ?: '-' }}</span></td>
                    <td><span class="cell-icon"><i class="bi bi-geo-alt-fill"></i>{{ $report->commune?->name ?: '-' }}</span></td>
                    <td><span class="status-chip">{{ $statusLabels[$report->status] ?? $report->status }}</span></td>
                    <td>{{ $report->damage_declared_at ? 'Declare' : 'Aucun' }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="empty-state">Aucun signalement ne correspond a votre recherche.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if ($reports->hasPages())
            <div class="pagination-wrap">
              <div class="reports-panel-count">Page {{ $reports->currentPage() }} sur {{ $reports->lastPage() }}</div>
              {{ $reports->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
          @endif
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
      <span>© {{ date('Y') }} My-Signal. Tous droits reserves.</span>
      <a href="{{ route('public.pages.contact') }}">Contactez-nous</a>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
