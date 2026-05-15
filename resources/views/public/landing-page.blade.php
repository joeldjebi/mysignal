<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $page->title }} - My-Signal</title>
  <link rel="icon" type="image/png" href="{{ asset('image/logo/logo-my-signal.png') }}" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  @php
    $landingBlocks = $landingBlocks ?? collect();
    $landingBlock = fn (string $key) => $landingBlocks->get($key);
    $blockTitle = fn (string $key, string $default) => optional($landingBlock($key))->title ?: $default;
    $blockSubtitle = fn (string $key, ?string $default = null) => optional($landingBlock($key))->subtitle ?: $default;
    $blockBody = fn (string $key, string $default = '') => optional($landingBlock($key))->body ?: $default;
    $blockMeta = fn (string $key, string $field, $default = '') => $landingBlock($key)?->meta[$field] ?? $default;
    $isVisible = fn (string $key) => ! $landingBlocks->has($key) || $landingBlocks->get($key)->status === 'active';
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
    $pageMeta = $page->meta ?? [];
    $pageIcon = $pageMeta['icon'] ?? 'bi-file-earmark-text-fill';
    $pageBody = html_entity_decode((string) $page->body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
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
      background: #fff;
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
    .nav-link:hover { color: var(--primary) !important; }
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
      padding: 84px 0 70px;
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
    .content-shell { padding: 64px 0; }
    .content-panel {
      max-width: 920px;
      font-size: 1rem;
      line-height: 1.85;
      color: #38566a;
    }
    .content-panel h2,
    .content-panel h3 {
      color: var(--text);
      font-weight: 800;
      margin-top: 28px;
    }
    .premium-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 18px;
      margin-top: 34px;
    }
    .premium-card {
      border: 1px solid rgba(24,52,71,.08);
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 18px 45px rgba(24,52,71,.08);
      overflow: hidden;
    }
    .premium-card-body { padding: 22px; }
    .premium-card h3 {
      font-size: 1.05rem;
      font-weight: 800;
      margin-bottom: 8px;
    }
    .category-pill {
      display: inline-flex;
      align-items: center;
      width: fit-content;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(37,111,143,.1);
      color: var(--secondary);
      font-size: .76rem;
      font-weight: 800;
      margin-bottom: 12px;
    }
    video {
      width: 100%;
      aspect-ratio: 16 / 9;
      background: #0f2533;
      display: block;
    }
    .faq-stack {
      display: grid;
      gap: 12px;
      margin-top: 34px;
      max-width: 920px;
    }
    .faq-item {
      border: 1px solid rgba(24,52,71,.08);
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 14px 35px rgba(24,52,71,.06);
      padding: 20px;
    }
    .faq-item h3 {
      font-size: 1rem;
      font-weight: 800;
      margin-bottom: 8px;
    }
    .contact-layout {
      display: grid;
      grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
      gap: 28px;
      align-items: start;
    }
    .contact-list {
      display: grid;
      gap: 12px;
      margin-top: 30px;
    }
    .contact-list a,
    .contact-list div {
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--text);
      text-decoration: none;
      background: var(--soft);
      border-radius: 8px;
      padding: 16px 18px;
      font-weight: 600;
    }
    .video-link {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-top: 28px;
      color: #fff;
      background: var(--accent);
      border-radius: 999px;
      padding: 12px 18px;
      text-decoration: none;
      font-weight: 700;
    }
    .contact-form {
      border: 1px solid rgba(24,52,71,.08);
      border-radius: 8px;
      box-shadow: 0 18px 45px rgba(24,52,71,.08);
      padding: 24px;
      background: #fff;
    }
    .contact-form .form-control {
      border-radius: 8px;
      border-color: rgba(24,52,71,.12);
      padding: 12px 14px;
    }
    .contact-form button {
      border: none;
      border-radius: 999px;
      background: var(--accent);
      color: #fff;
      font-weight: 800;
      padding: 12px 18px;
    }
    .alert-premium {
      border: 0;
      border-radius: 8px;
      background: rgba(91,235,175,.14);
      color: #176344;
      font-weight: 700;
    }
    footer {
      background: #102736;
      color: rgba(255,255,255,.7);
      padding: 64px 0 24px;
      font-size: .86rem;
    }
    footer .brand { font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 16px; }
    footer p { font-size: .85rem; line-height: 1.7; margin-bottom: 20px; }
    footer h6 { color: #fff; font-weight: 700; margin-bottom: 20px; font-size: .9rem; }
    footer ul { list-style: none; padding: 0; margin: 0; }
    footer ul li { margin-bottom: 10px; }
    footer a { color: rgba(255,255,255,.78); text-decoration: none; }
    footer a:hover { color: #fff; }
    .footer-social {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .footer-social a {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,.08);
    }
    .newsletter-form input {
      width: 100%;
      border: 0;
      border-radius: 8px;
      padding: 11px 12px;
      outline: 0;
    }
    .newsletter-form button {
      width: 100%;
      border: 0;
      border-radius: 8px;
      background: var(--accent);
      color: #fff;
      font-weight: 800;
      padding: 11px 12px;
    }
    .footer-bottom {
      margin-top: 34px;
      padding-top: 22px;
      border-top: 1px solid rgba(255,255,255,.1);
      font-size: .8rem;
    }
    @media (max-width: 1199.98px) {
      .navbar-collapse { padding-top: 16px; }
      .navbar-nav { align-items: stretch !important; gap: 8px !important; }
      .btn-nav { text-align: center; }
      .page-hero { padding: 64px 0 54px; }
    }
    @media (max-width: 991.98px) {
      .premium-grid,
      .contact-layout { grid-template-columns: 1fr; }
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
          @php
            $navigationLines = $lines($blockBody('navigation', "Accueil | /\nQui sommes-nous ? | /qui-sommes-nous\nNos domaines | /#domains\nFonctionnalités | /#features\nMy-Signal TV | /my-signal-tv\nSignalements | /signalements\nFAQ | /faq\nContactez-nous | /contactez-nous"));

            if (! collect($navigationLines)->contains(fn ($line) => str_contains(strtolower($line), '/signalements'))) {
              $navigationLines[] = 'Signalements | /signalements';
            }
          @endphp
          @foreach ($navigationLines as $navLine)
            @php
              [$navLabel, $navUrl] = $parts($navLine, 2);
            @endphp
            <li class="nav-item"><a class="nav-link" href="{{ $navUrl ?: '#' }}">{{ $navLabel }}</a></li>
          @endforeach
          <li class="nav-item ms-lg-2"><a class="nav-link btn-nav" href="{{ route('public.auth') }}">{{ $blockMeta('navigation', 'cta_label', 'Se connecter et signaler maintenant') }}</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main>
    <section class="page-hero">
      <div class="container position-relative">
        <div class="page-badge"><i class="bi {{ $pageIcon }}"></i>{{ $page->subtitle }}</div>
        <h1>{{ $page->title }}</h1>
        <p class="lead">{{ $page->subtitle }}</p>
      </div>
    </section>

    <section class="content-shell">
      <div class="container">
        @if (session('success'))
          <div class="alert alert-premium mb-4">{{ session('success') }}</div>
        @endif

        @if ($pageKey === 'page_tv')
          <div class="content-panel">{!! $pageBody !!}</div>
          @php
            $videos = collect($pageMeta['videos'] ?? [])->filter(fn ($video) => filled($video['video_url'] ?? null));
          @endphp
          <div class="premium-grid">
            @forelse ($videos as $video)
              <article class="premium-card">
                <video controls preload="metadata" src="{{ $video['video_url'] }}"></video>
                <div class="premium-card-body">
                  <div class="category-pill">{{ $video['category'] ?? 'General' }}</div>
                  <h3>{{ $video['title'] }}</h3>
                  <p class="mb-0 text-secondary">{{ $video['body'] }}</p>
                </div>
              </article>
            @empty
              <div class="content-panel">Aucune video n est disponible pour le moment.</div>
            @endforelse
          </div>
        @elseif ($pageKey === 'page_faq')
          <div class="content-panel">{!! $pageBody !!}</div>
          <div class="faq-stack">
            @forelse (($pageMeta['questions'] ?? []) as $question)
              <article class="faq-item">
                <h3>{{ $question['title'] }}</h3>
                <div class="text-secondary">{!! nl2br(e($question['body'] ?? '')) !!}</div>
              </article>
            @empty
              <div class="content-panel">Aucune question n est disponible pour le moment.</div>
            @endforelse
          </div>
        @elseif ($pageKey === 'page_contact')
          <div class="contact-layout">
            <div>
              <div class="content-panel">{!! $pageBody !!}</div>
              <div class="contact-list">
                @if (($pageMeta['email'] ?? '') !== '')
                  <a href="mailto:{{ $pageMeta['email'] }}"><i class="bi bi-envelope"></i>{{ $pageMeta['email'] }}</a>
                @endif
                @if (($pageMeta['phone'] ?? '') !== '')
                  <a href="tel:{{ $pageMeta['phone'] }}"><i class="bi bi-telephone"></i>{{ $pageMeta['phone'] }}</a>
                @endif
                @if (($pageMeta['address'] ?? '') !== '')
                  <div><i class="bi bi-geo-alt"></i>{{ $pageMeta['address'] }}</div>
                @endif
              </div>
            </div>
            <form method="POST" action="{{ route('public.pages.contact.store') }}" class="contact-form">
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nom complet</label>
                  <input class="form-control" name="name" value="{{ old('name') }}" required>
                  @error('name')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                  @error('email')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Telephone</label>
                  <input class="form-control" name="phone" value="{{ old('phone') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Sujet</label>
                  <input class="form-control" name="subject" value="{{ old('subject') }}">
                </div>
                <div class="col-12">
                  <label class="form-label">Message</label>
                  <textarea class="form-control" name="message" rows="6" required>{{ old('message') }}</textarea>
                  @error('message')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                  <button type="submit">Envoyer le message <i class="bi bi-send-fill ms-1"></i></button>
                </div>
              </div>
            </form>
          </div>
        @else
          <div class="content-panel">{!! $pageBody !!}</div>
        @endif
      </div>
    </section>
  </main>

  @if ($isVisible('footer'))
  <footer>
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-4">
          <div class="brand">{{ $blockTitle('footer', 'My-Signal') }}</div>
          <p>{{ $blockBody('footer', "La plateforme qui facilite le signalement, le suivi des dossiers, l'abonnement annuel des UP et les retours d’expérience.") }}</p>
          <div class="footer-social">
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-twitter-x"></i></a>
            <a href="#"><i class="bi bi-linkedin"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
            <a href="#"><i class="bi bi-youtube"></i></a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <h6>{{ $blockMeta('footer', 'column_1_title', 'My-Signal') }}</h6>
          <ul>
            @foreach ($lines($blockMeta('footer', 'column_1_links', "À propos | /qui-sommes-nous\nProtection consommateur | /\nUnités Partenaires | /\nContact | /contactez-nous")) as $footerLine)
              @php
                [$footerLabel, $footerUrl] = $parts($footerLine, 2);
              @endphp
              <li><a href="{{ $footerUrl ?: '#' }}">{{ $footerLabel }}</a></li>
            @endforeach
          </ul>
        </div>
        <div class="col-6 col-lg-2">
          <h6>{{ $blockMeta('footer', 'column_2_title', 'Modules') }}</h6>
          <ul>
            @foreach ($lines($blockMeta('footer', 'column_2_links', "Fonctionnalités | /#features\nFAQ | /faq\nREX | /#testimonials\nDomaines couverts | /#domains")) as $footerLine)
              @php
                [$footerLabel, $footerUrl] = $parts($footerLine, 2);
              @endphp
              <li><a href="{{ $footerUrl ?: '#' }}">{{ $footerLabel }}</a></li>
            @endforeach
          </ul>
        </div>
        <div class="col-6 col-lg-2">
          <h6>{{ $blockMeta('footer', 'column_3_title', 'Légal') }}</h6>
          <ul>
            @foreach ($lines($blockMeta('footer', 'column_3_links', "Conditions générales d'utilisation | /conditions-generales-utilisation\nPolitique de confidentialité | /politique-confidentialite\nContact | /contactez-nous")) as $footerLine)
              @php
                [$footerLabel, $footerUrl] = $parts($footerLine, 2);
              @endphp
              <li><a href="{{ $footerUrl ?: '#' }}">{{ $footerLabel }}</a></li>
            @endforeach
          </ul>
        </div>
        <div class="col-6 col-lg-2">
          <h6>{{ $blockMeta('footer', 'newsletter_title', 'Alertes') }}</h6>
          <p>{{ $blockMeta('footer', 'newsletter_text', 'Recevez les informations importantes sur les modules My-Signal.') }}</p>
          <div class="newsletter-form d-grid gap-2">
            <input type="email" placeholder="Votre adresse email">
            <button>S'inscrire <i class="bi bi-send-fill ms-1"></i></button>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <div class="row align-items-center">
          <div class="col-md-6 text-md-start">© {{ date('Y') }} My-Signal. Tous droits réservés.</div>
          <div class="col-md-6 text-md-end mt-2 mt-md-0">
            {{ $blockSubtitle('footer', 'Plateforme de protection consommateur') }}
          </div>
        </div>
      </div>
    </div>
  </footer>
  @endif

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
