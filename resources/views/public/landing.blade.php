<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MySignal - Plateforme de signalement consommateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet" />
  @php
    $landingBlocks = $landingBlocks ?? collect();
    $landingBlock = fn (string $key) => $landingBlocks->get($key);
    $blockTitle = fn (string $key, string $default) => optional($landingBlock($key))->title ?: $default;
    $blockSubtitle = fn (string $key, ?string $default = null) => optional($landingBlock($key))->subtitle ?: $default;
    $blockBody = fn (string $key, string $default = '') => optional($landingBlock($key))->body ?: $default;
    $blockMeta = fn (string $key, string $field, string $default = '') => $landingBlock($key)?->meta[$field] ?? $default;
    $isVisible = fn (string $key) => ! $landingBlocks->has($key) || $landingBlocks->get($key)->status === 'active';
    $lines = function (?string $value): array {
      return collect(preg_split('/\r\n|\r|\n/', (string) $value))
        ->map(fn ($line) => trim($line))
        ->filter()
        ->values()
        ->all();
    };
    $parts = fn (string $line, int $limit = 3) => array_pad(array_map('trim', explode('|', $line, $limit)), $limit, '');
    $settings = $landingBlocks->get('settings');
    $settingsMeta = $settings?->meta ?? [];
    $primaryColor = $settingsMeta['primary_color'] ?? '#183447';
    $secondaryColor = $settingsMeta['secondary_color'] ?? '#256f8f';
    $accentColor = $settingsMeta['accent_color'] ?? '#ff0068';
  @endphp
  <style>
    :root {
      --primary: {{ $primaryColor }};
      --primary-dark: #102736;
      --primary-light: {{ $secondaryColor }};
      --accent: {{ $accentColor }};
      --yellow: #ffa117;
      --success: #5bebaf;
      --text-dark: #183447;
      --text-muted: #647887;
      --bg-light: #f4f9fb;
      --white: #ffffff;
      --gradient: linear-gradient(135deg, #183447 0%, #256f8f 100%);
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text-dark);
      overflow-x: hidden;
    }

    /* ===== NAVBAR ===== */
    .navbar {
      background: var(--white);
      box-shadow: 0 2px 20px rgba(24,52,71,.08);
      padding: 14px 0;
      position: sticky;
      top: 0;
      z-index: 999;
    }
    .navbar-brand {
      font-weight: 800;
      font-size: 1.4rem;
      color: var(--primary) !important;
      letter-spacing: -0.5px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }
    .navbar-brand span { color: var(--text-dark); }
    .navbar-brand img {
      height: 42px;
      width: auto;
      display: block;
    }
    .nav-link {
      color: var(--text-dark) !important;
      font-weight: 500;
      font-size: .88rem;
      padding: 6px 14px !important;
      transition: color .2s;
    }
    .nav-link:hover { color: var(--primary) !important; }
    .btn-nav {
      background: var(--gradient);
      color: #fff !important;
      border-radius: 30px;
      padding: 8px 24px !important;
      font-weight: 600;
      font-size: .85rem;
    }
    .btn-nav:hover { opacity: .88; }

    /* ===== HERO ===== */
    .hero {
      background: var(--gradient);
      min-height: 600px;
      padding: 90px 0 0;
      overflow: hidden;
      position: relative;
    }
    .hero::before {
      content: '';
      position: absolute;
      width: 500px; height: 500px;
      background: rgba(255,255,255,.06);
      border-radius: 50%;
      top: -120px; right: -100px;
    }
    .hero-title {
      font-size: 2.8rem;
      font-weight: 800;
      color: #fff;
      line-height: 1.2;
      margin-bottom: 18px;
    }
    .hero-title span { color: var(--yellow); }
    .hero-text { color: rgba(255,255,255,.82); font-size: .95rem; margin-bottom: 32px; }
    .btn-hero-primary {
      background: #fff;
      color: var(--primary);
      border-radius: 30px;
      padding: 12px 30px;
      font-weight: 700;
      font-size: .9rem;
      border: none;
      transition: all .2s;
    }
    .btn-hero-primary:hover { background: var(--yellow); color: #fff; }
    .btn-hero-outline {
      background: transparent;
      border: 2px solid rgba(255,255,255,.5);
      color: #fff;
      border-radius: 30px;
      padding: 10px 28px;
      font-weight: 600;
      font-size: .9rem;
      margin-left: 12px;
      transition: all .2s;
    }
    .btn-hero-outline:hover { background: rgba(255,255,255,.15); }

    .hero-stats { margin-top: 48px; }
    .hero-stats .stat-num {
      font-size: 1.5rem;
      font-weight: 800;
      color: #fff;
    }
    .hero-stats .stat-label {
      color: rgba(255,255,255,.7);
      font-size: .78rem;
    }
    .hero-stats .divider {
      width: 1px; height: 40px;
      background: rgba(255,255,255,.3);
      margin: 0 28px;
    }

    .hero-phone-wrap {
      position: relative;
      display: flex;
      justify-content: center;
      align-items: flex-end;
      height: 460px;
    }
    .phone-card {
      background: #fff;
      border-radius: 36px;
      box-shadow: 0 30px 80px rgba(0,0,0,.25);
      overflow: hidden;
      position: absolute;
    }
    .phone-main {
      width: 200px; height: 390px;
      bottom: 0; left: 50%;
      transform: translateX(-50%);
      z-index: 2;
    }
    .phone-secondary {
      width: 160px; height: 300px;
      bottom: 40px;
      right: 10px;
      z-index: 1;
      transform: rotate(8deg);
    }
    .phone-screen { width: 100%; height: 100%; background: var(--bg-light); display: flex; align-items: center; justify-content: center; }
    .phone-ui { padding: 16px; }
    .phone-ui .bar { height: 8px; border-radius: 4px; background: var(--primary); margin-bottom: 8px; }
    .phone-ui .bar.short { width: 60%; background: var(--primary-light); }
    .phone-ui .card-mini {
      background: #fff;
      border-radius: 12px;
      padding: 10px;
      margin-bottom: 8px;
      box-shadow: 0 4px 12px rgba(24,52,71,.1);
    }

    /* ===== FEATURES STRIP ===== */
    .features-strip { padding: 60px 0; background: #fff; }
    .feature-icon-wrap {
      width: 64px; height: 64px;
      border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem;
      margin: 0 auto 16px;
    }
    .feature-icon-wrap.purple { background: rgba(24,52,71,.12); color: var(--primary); }
    .feature-icon-wrap.green  { background: rgba(91,235,175,.18); color: #15955f; }
    .feature-icon-wrap.orange { background: rgba(255,0,104,.1); color: var(--accent); }
    .feature-icon-wrap.blue   { background: rgba(37,111,143,.12); color: var(--primary-light); }
    .feature-card h6 { font-weight: 700; font-size: .92rem; margin-bottom: 8px; }
    .feature-card p  { font-size: .8rem; color: var(--text-muted); line-height: 1.6; }

    /* ===== MANAGE SECTION ===== */
    .section-manage {
      padding: 80px 0;
      background: var(--bg-light);
    }
    .section-title { font-weight: 800; font-size: 1.8rem; margin-bottom: 16px; }
    .section-sub  { color: var(--text-muted); font-size: .9rem; margin-bottom: 40px; }
    .check-list li {
      list-style: none;
      padding: 6px 0;
      font-size: .88rem;
      color: var(--text-muted);
    }
    .check-list li::before {
      content: '✓';
      color: var(--primary);
      font-weight: 800;
      margin-right: 10px;
    }
    .phone-mockup-lg {
      width: 240px;
      border-radius: 40px;
      box-shadow: 0 30px 80px rgba(24,52,71,.2);
      overflow: hidden;
      background: #fff;
      margin: auto;
    }
    .phone-mockup-lg .screen { height: 460px; background: linear-gradient(180deg, #eef8fb 0%, #fff 100%); padding: 20px; }

    /* ===== SHARE SECTION ===== */
    .section-share { padding: 80px 0; background: #fff; }

    /* ===== DOWNLOAD BANNER ===== */
    .download-banner {
      background: var(--gradient);
      padding: 70px 0;
      text-align: center;
      color: #fff;
      position: relative;
      overflow: hidden;
    }
    .download-banner::before, .download-banner::after {
      content: '';
      position: absolute;
      width: 300px; height: 300px;
      background: rgba(255,255,255,.05);
      border-radius: 50%;
    }
    .download-banner::before { top: -100px; left: -80px; }
    .download-banner::after  { bottom: -100px; right: -80px; }
    .download-banner h2 { font-weight: 800; font-size: 2rem; margin-bottom: 12px; }
    .download-banner p  { opacity: .85; margin-bottom: 32px; font-size: .9rem; }
    .btn-store {
      display: inline-flex; align-items: center; gap: 10px;
      background: #fff;
      color: var(--primary);
      border-radius: 12px;
      padding: 12px 24px;
      font-weight: 700;
      text-decoration: none;
      margin: 6px;
      font-size: .88rem;
      transition: all .2s;
    }
    .btn-store:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.15); color: var(--primary); }
    .btn-store i { font-size: 1.4rem; }

    /* ===== APP FEATURES ===== */
    .section-app-features { padding: 80px 0; background: #fff; }
    .app-feature-item { margin-bottom: 32px; }
    .app-feature-item .icon-box {
      width: 48px; height: 48px;
      border-radius: 12px;
      background: rgba(24,52,71,.1);
      display: flex; align-items: center; justify-content: center;
      color: var(--primary);
      font-size: 1.2rem;
      flex-shrink: 0;
    }
    .app-feature-item h6 { font-weight: 700; margin-bottom: 4px; font-size: .9rem; }
    .app-feature-item p  { font-size: .8rem; color: var(--text-muted); margin: 0; }

    /* ===== SCREENSHOTS ===== */
    .section-screenshots { padding: 80px 0; background: var(--bg-light); }
    .screenshot-card {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 16px 40px rgba(24,52,71,.12);
      position: relative;
    }
    .screenshot-card .overlay {
      position: absolute; inset: 0;
      background: linear-gradient(180deg, transparent 40%, rgba(24,52,71,.72) 100%);
      display: flex; align-items: flex-end;
      padding: 20px;
    }
    .screenshot-card .overlay span { color: #fff; font-weight: 700; font-size: .85rem; }
    .screenshot-inner {
      height: 360px;
      background: linear-gradient(135deg, #eef8fb 0%, #d9edf3 100%);
      display: flex; align-items: center; justify-content: center;
    }
    .screenshot-inner .label {
      background: var(--primary);
      color: #fff;
      padding: 6px 16px;
      border-radius: 30px;
      font-size: .85rem;
      font-weight: 700;
    }

    /* ===== WORK PROCESS ===== */
    .section-process { padding: 80px 0; background: #fff; }
    .process-step { display: flex; align-items: flex-start; gap: 20px; margin-bottom: 36px; }
    .step-num {
      width: 44px; height: 44px; border-radius: 50%;
      background: var(--gradient);
      color: #fff; font-weight: 800;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; font-size: .9rem;
    }
    .process-chart {
      width: 260px; height: 260px;
      border-radius: 50%;
      background: conic-gradient(
        var(--primary)      0deg 90deg,
        var(--accent)     90deg 180deg,
        var(--yellow)     180deg 270deg,
        var(--success)    270deg 360deg
      );
      display: flex; align-items: center; justify-content: center;
      margin: auto;
      box-shadow: 0 20px 50px rgba(24,52,71,.24);
    }
    .chart-center {
      width: 120px; height: 120px;
      border-radius: 50%;
      background: #fff;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
    }
    .chart-center .c-label { font-weight: 800; font-size: .7rem; color: var(--text-muted); text-align: center; }
    .chart-center .c-title { font-weight: 800; color: var(--primary); font-size: .75rem; }

    /* ===== STATS BANNER ===== */
    .stats-banner { background: var(--gradient); padding: 60px 0; }
    .stat-item { text-align: center; }
    .stat-item .num { font-size: 2.2rem; font-weight: 800; color: #fff; }
    .stat-item .lbl { color: rgba(255,255,255,.75); font-size: .85rem; }
    .stat-divider { width: 1px; height: 60px; background: rgba(255,255,255,.2); margin: auto; }

    /* ===== PRICING ===== */
    .section-pricing { padding: 80px 0; background: var(--bg-light); }
    .pricing-card {
      background: #fff;
      border-radius: 24px;
      padding: 36px 30px;
      text-align: center;
      box-shadow: 0 8px 30px rgba(0,0,0,.06);
      transition: all .3s;
      border: 2px solid transparent;
    }
    .pricing-card:hover, .pricing-card.popular {
      border-color: var(--primary);
      transform: translateY(-8px);
      box-shadow: 0 20px 50px rgba(24,52,71,.16);
    }
    .pricing-card.popular { background: var(--gradient); color: #fff; }
    .pricing-card .plan-name { font-weight: 700; font-size: .9rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; }
    .pricing-card .price { font-size: 2.8rem; font-weight: 800; margin-bottom: 4px; }
    .pricing-card .price span { font-size: 1rem; font-weight: 400; }
    .pricing-card .per { font-size: .8rem; opacity: .7; margin-bottom: 28px; }
    .pricing-card ul { list-style: none; padding: 0; margin-bottom: 32px; text-align: left; }
    .pricing-card ul li { padding: 6px 0; font-size: .85rem; }
    .pricing-card ul li::before { content: '✓'; color: var(--primary); font-weight: 800; margin-right: 8px; }
    .pricing-card.popular ul li::before { color: #fff; }
    .btn-pricing {
      border-radius: 30px;
      padding: 10px 32px;
      font-weight: 700;
      font-size: .88rem;
      border: 2px solid var(--primary);
      color: var(--primary);
      background: transparent;
      transition: all .2s;
    }
    .btn-pricing:hover { background: var(--primary); color: #fff; }
    .pricing-card.popular .btn-pricing { border-color: #fff; color: #fff; }
    .pricing-card.popular .btn-pricing:hover { background: #fff; color: var(--primary); }

    /* ===== FAQ ===== */
    .section-faq { padding: 80px 0; background: #fff; }
    .accordion-button:not(.collapsed) { background: rgba(24,52,71,.07); color: var(--primary); box-shadow: none; }
    .accordion-button:focus { box-shadow: none; }
    .accordion-button { font-weight: 600; font-size: .9rem; }
    .faq-illustration { max-width: 320px; }
    .faq-illus-inner {
      width: 280px; height: 280px;
      background: var(--bg-light);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 8rem;
      margin: auto;
    }

    /* ===== TESTIMONIALS ===== */
    .section-testimonials { background: var(--gradient); padding: 80px 0; }
    .testimonial-card {
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 20px;
      padding: 30px;
      color: #fff;
      backdrop-filter: blur(10px);
    }
    .testimonial-card .quote { font-size: 2rem; opacity: .5; line-height: 1; }
    .testimonial-card p { font-size: .88rem; opacity: .9; margin: 12px 0 20px; }
    .testimonial-card .author { display: flex; align-items: center; gap: 12px; }
    .author-avatar {
      width: 44px; height: 44px;
      border-radius: 50%;
      background: rgba(255,255,255,.3);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem;
    }
    .author-name  { font-weight: 700; font-size: .88rem; }
    .author-role  { font-size: .75rem; opacity: .7; }
    .stars { color: var(--yellow); font-size: .8rem; }

    /* ===== TEAM ===== */
    .section-team { padding: 80px 0; background: var(--bg-light); }
    .team-card {
      text-align: center;
      background: #fff;
      border-radius: 20px;
      padding: 32px 24px;
      box-shadow: 0 8px 24px rgba(0,0,0,.06);
      transition: transform .3s;
    }
    .team-card:hover { transform: translateY(-6px); }
    .team-avatar {
      width: 80px; height: 80px;
      border-radius: 50%;
      background: var(--gradient);
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem;
      margin: 0 auto 16px;
      color: #fff;
    }
    .team-card h6 { font-weight: 700; margin-bottom: 4px; }
    .team-card .role { font-size: .8rem; color: var(--text-muted); margin-bottom: 16px; }
    .social-links a {
      display: inline-flex; align-items: center; justify-content: center;
      width: 32px; height: 32px;
      border-radius: 50%;
      background: var(--bg-light);
      color: var(--primary);
      font-size: .85rem;
      margin: 0 3px;
      text-decoration: none;
      transition: all .2s;
    }
    .social-links a:hover { background: var(--primary); color: #fff; }

    /* ===== CTA SECTION ===== */
    .section-cta { padding: 80px 0; background: #fff; }
    .cta-box {
      background: var(--bg-light);
      border-radius: 24px;
      padding: 60px 40px;
    }
    .cta-illustration { font-size: 6rem; }

    /* ===== NEWS ===== */
    .section-news { padding: 80px 0; background: var(--bg-light); }
    .news-card {
      border-radius: 20px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 8px 24px rgba(0,0,0,.06);
      transition: transform .3s;
    }
    .news-card:hover { transform: translateY(-6px); }
    .news-thumb {
      height: 200px;
      display: flex; align-items: center; justify-content: center;
      font-size: 4rem;
    }
    .news-thumb.c1 { background: linear-gradient(135deg, #eef8fb, #d9edf3); }
    .news-thumb.c2 { background: linear-gradient(135deg, #fff5e6, #ffe2ad); }
    .news-thumb.c3 { background: linear-gradient(135deg, #e9fff5, #c8f8df); }
    .news-body { padding: 20px; }
    .news-tag {
      display: inline-block;
      background: rgba(24,52,71,.1);
      color: var(--primary);
      border-radius: 20px;
      padding: 3px 12px;
      font-size: .72rem;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .news-body h6 { font-weight: 700; font-size: .9rem; margin-bottom: 8px; }
    .news-body p  { font-size: .8rem; color: var(--text-muted); }
    .news-meta { font-size: .75rem; color: var(--text-muted); }

    /* ===== CLIENTS ===== */
    .section-clients { padding: 60px 0; background: #fff; }
    .client-logo {
      display: flex; align-items: center; justify-content: center;
      height: 60px;
      opacity: .5;
      transition: opacity .2s;
      font-weight: 700;
      font-size: 1.2rem;
      color: var(--text-dark);
      letter-spacing: 1px;
    }
    .client-logo:hover { opacity: 1; }

    /* ===== FOOTER ===== */
    footer {
      background: #102736;
      color: rgba(255,255,255,.75);
      padding: 60px 0 0;
    }
    footer .brand { font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 16px; }
    footer .brand span { color: var(--primary-light); }
    footer p { font-size: .85rem; line-height: 1.7; margin-bottom: 20px; }
    footer h6 { color: #fff; font-weight: 700; margin-bottom: 20px; font-size: .9rem; }
    footer ul { list-style: none; padding: 0; }
    footer ul li { margin-bottom: 10px; }
    footer ul li a { color: rgba(255,255,255,.65); text-decoration: none; font-size: .85rem; transition: color .2s; }
    footer ul li a:hover { color: var(--primary-light); }
    .footer-social a {
      display: inline-flex; align-items: center; justify-content: center;
      width: 36px; height: 36px;
      border-radius: 50%;
      background: rgba(255,255,255,.1);
      color: #fff;
      margin-right: 8px;
      text-decoration: none;
      transition: background .2s;
    }
    .footer-social a:hover { background: var(--primary); }
    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,.08);
      padding: 20px 0;
      margin-top: 40px;
      font-size: .82rem;
      text-align: center;
    }
    .newsletter-form { display: flex; gap: 8px; margin-top: 16px; }
    .newsletter-form input {
      flex: 1;
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 30px;
      padding: 10px 18px;
      color: #fff;
      font-size: .85rem;
      outline: none;
    }
    .newsletter-form input::placeholder { color: rgba(255,255,255,.4); }
    .newsletter-form button {
      background: var(--gradient);
      border: none;
      border-radius: 30px;
      padding: 10px 20px;
      color: #fff;
      font-weight: 600;
      font-size: .82rem;
      cursor: pointer;
    }

    /* ===== UTILS ===== */
    .badge-pill {
      display: inline-block;
      background: rgba(24,52,71,.1);
      color: var(--primary);
      border-radius: 30px;
      padding: 6px 18px;
      font-size: .78rem;
      font-weight: 600;
      margin-bottom: 16px;
      letter-spacing: .5px;
    }
    .btn-primary-custom {
      background: var(--gradient);
      color: #fff;
      border-radius: 30px;
      padding: 12px 32px;
      border: none;
      font-weight: 700;
      font-size: .9rem;
      transition: all .2s;
      display: inline-block;
      text-decoration: none;
    }
    .btn-primary-custom:hover { opacity: .88; color: #fff; transform: translateY(-1px); }
    .btn-outline-custom {
      border: 2px solid var(--primary);
      color: var(--primary);
      border-radius: 30px;
      padding: 10px 30px;
      font-weight: 700;
      font-size: .9rem;
      background: transparent;
      display: inline-block;
      text-decoration: none;
      transition: all .2s;
    }
    .btn-outline-custom:hover { background: var(--primary); color: #fff; }

    /* animations */
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
    .float { animation: float 3s ease-in-out infinite; }
    @keyframes fadeUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
    .fade-up { animation: fadeUp .7s ease both; }
    .delay-1 { animation-delay: .15s; }
    .delay-2 { animation-delay: .3s; }
    .delay-3 { animation-delay: .45s; }
  </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="#">
      <img src="{{ asset('image/logo/logo-my-signal.png') }}" alt="MySignal">
        <span>MySignal</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        @foreach ($lines($blockBody('navigation', "Fonctionnalites | #features\nApercus | #screenshots\nFAQ | #faq\nActualites | #news")) as $navLine)
          @php
            [$navLabel, $navUrl] = $parts($navLine, 2);
          @endphp
          <li class="nav-item"><a class="nav-link" href="{{ $navUrl ?: '#' }}">{{ $navLabel }}</a></li>
        @endforeach
        <li class="nav-item ms-2"><a class="nav-link btn-nav" href="{{ route('public.auth') }}">{{ $blockMeta('navigation', 'cta_label', 'Se connecter et signaler maintenant') }}</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- ===== HERO ===== -->
@if ($isVisible('hero'))
<section class="hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 pb-5">
        @php
          $heroTitle = $blockTitle('hero', 'Signalez, suivez et faites valoir vos droits');
          $heroTitleParts = array_pad(explode(',', $heroTitle, 2), 2, '');
          $heroAccentParts = array_pad(preg_split('/\s+/', trim($heroTitleParts[1]), 2), 2, '');
        @endphp
        <p class="badge-pill" style="background:rgba(255,255,255,.15);color:#fff;">{{ $blockSubtitle('hero', 'Plateforme de protection consommateur') }}</p>
        <h1 class="hero-title fade-up">{{ $heroTitleParts[0] }}@if($heroTitleParts[1] !== ''), <span>{{ $heroAccentParts[0] }}</span> {{ $heroAccentParts[1] }}@endif</h1>
        <p class="hero-text fade-up delay-1">{{ $blockBody('hero', 'MySignal accompagne les consommateurs et les Unites Partenaires dans le suivi des signalements, des abonnements, des REX et des dossiers traites.') }}</p>
        <div class="fade-up delay-2">
          <a href="{{ route('public.auth') }}" class="btn-hero-primary">{{ $blockMeta('hero', 'primary_button', 'Activer mon acces') }}</a>
          <a href="#" class="btn-hero-outline">
            <i class="bi bi-play-circle-fill me-1"></i> {{ $blockMeta('hero', 'secondary_button', 'Voir le parcours') }}
          </a>
        </div>
        <div class="hero-stats d-flex align-items-center fade-up delay-3">
          @foreach ($lines($blockMeta('hero', 'stats', "573K+ | Utilisateurs actifs\n26,675 | Signalements suivis\n9.2K | Retours collectes")) as $statLine)
            @php
              [$statValue, $statLabel] = $parts($statLine, 2);
            @endphp
            @if (! $loop->first)<div class="divider"></div>@endif
            <div>
              <div class="stat-num">{{ $statValue }}</div>
              <div class="stat-label">{{ $statLabel }}</div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-phone-wrap float">
          <!-- Main Phone -->
          <div class="phone-card phone-main">
            <div class="phone-ui" style="padding:20px;height:100%;background:linear-gradient(180deg,#eef8fb 0%,#fff 60%)">
              <div style="text-align:center;padding:20px 0 10px">
                <div style="width:60px;height:60px;background:var(--gradient);border-radius:50%;margin:auto;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem"><i class="bi bi-heart-fill"></i></div>
              </div>
              <div style="background:#fff;border-radius:12px;padding:12px;margin-bottom:10px;box-shadow:0 4px 12px rgba(24,52,71,.1)">
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <span style="font-size:.7rem;font-weight:700;color:var(--text-dark)">Tableau de bord</span>
                  <span style="font-size:.7rem;color:var(--primary);font-weight:600">Voir tout</span>
                </div>
                <div style="margin-top:8px">
                  <div style="height:6px;border-radius:3px;background:var(--primary);width:80%;margin-bottom:5px"></div>
                  <div style="height:6px;border-radius:3px;background:var(--accent);width:60%;margin-bottom:5px"></div>
                  <div style="height:6px;border-radius:3px;background:var(--success);width:90%"></div>
                </div>
              </div>
              <div style="background:#fff;border-radius:12px;padding:12px;box-shadow:0 4px 12px rgba(24,52,71,.1)">
                <div style="font-size:.65rem;color:var(--text-muted);margin-bottom:6px">Statut abonnement</div>
                <div style="font-size:1.3rem;font-weight:800;color:var(--primary)">Actif</div>
                <div style="font-size:.65rem;color:#15955f;font-weight:600">Carte membre disponible</div>
              </div>
            </div>
          </div>
          <!-- Secondary Phone -->
          <div class="phone-card phone-secondary" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eef8fb,#fff)">
            <div style="padding:16px;text-align:center">
              <div style="font-size:2.5rem;margin-bottom:8px">📋</div>
              <div style="font-size:.7rem;font-weight:700;color:var(--primary)">Suivi</div>
              <div style="font-size:.6rem;color:var(--text-muted)">Dossiers en temps reel</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<!-- ===== FEATURES STRIP ===== -->
@if ($isVisible('feature_strip'))
<section class="features-strip">
  <div class="container">
    <div class="row g-4 text-center">
      @foreach ($lines($blockBody('feature_strip', "Signalement rapide | Deposez un dommage ou une reclamation en quelques etapes claires. | bi-lightning-charge-fill\nEspace securise | Vos dossiers, abonnements et retours restent accessibles depuis votre compte. | bi-shield-fill-check\nSuivi lisible | Consultez l'etat de vos signalements, dossiers et traitements. | bi-bar-chart-fill\nDialogue UP | Les Unites Partenaires disposent d'un espace pour traiter les demandes. | bi-people-fill")) as $featureLine)
        @php
          [$featureTitle, $featureText, $featureIcon] = $parts($featureLine, 3);
        @endphp
        <div class="col-6 col-md-3">
          <div class="feature-card">
            <div class="feature-icon-wrap {{ ['purple', 'green', 'orange', 'blue'][$loop->index % 4] }}"><i class="bi {{ $featureIcon ?: 'bi-check-circle-fill' }}"></i></div>
            <h6>{{ $featureTitle }}</h6>
            <p>{{ $featureText }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ===== MANAGE SECTION ===== -->
@if ($isVisible('manage'))
<section class="section-manage" id="features">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 order-lg-2">
        <span class="badge-pill">{{ $blockSubtitle('manage', 'Pourquoi MySignal ?') }}</span>
        <h2 class="section-title">{!! nl2br(e($blockTitle('manage', 'Un seul espace pour suivre vos droits consommateur'))) !!}</h2>
        <p class="section-sub">{{ $blockBody('manage', "MySignal centralise les signalements, les dossiers ouverts, les abonnements annuels, les notifications et les retours d'experience.") }}</p>
        <ul class="check-list ps-0 mb-4">
          @foreach ($lines($blockMeta('manage', 'items', "Creation et suivi des signalements consommateurs\nNotifications avant expiration des abonnements\nCarte membre virtuelle avec QR code pour les abonnes actifs\nHistorique des abonnements et des REX\nParametrage par le Super Administrateur\nTableau de bord clair pour les UP et les consommateurs")) as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
        <a href="#features" class="btn-primary-custom">{{ $blockMeta('manage', 'button', 'En savoir plus') }} <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
      <div class="col-lg-6 order-lg-1 text-center">
        <div class="phone-mockup-lg float">
          <div class="screen">
            <div style="background:var(--gradient);border-radius:12px;padding:14px;margin-bottom:12px">
              <div style="color:rgba(255,255,255,.7);font-size:.65rem">Signalements suivis</div>
              <div style="color:#fff;font-size:1.5rem;font-weight:800">48,295</div>
              <div style="color:rgba(255,255,255,.8);font-size:.65rem">+8.2% ce mois</div>
            </div>
            <div style="background:#fff;border-radius:12px;padding:12px;margin-bottom:10px;box-shadow:0 2px 10px rgba(0,0,0,.05)">
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(24,52,71,.1);display:flex;align-items:center;justify-content:center;color:var(--primary)">👤</div>
                <div>
                  <div style="font-size:.72rem;font-weight:700">Unite Partenaire</div>
                  <div style="font-size:.62rem;color:var(--text-muted)">Abonnement annuel</div>
                </div>
                <div style="margin-left:auto;background:rgba(91,235,175,.18);color:#15955f;border-radius:20px;padding:2px 8px;font-size:.62rem;font-weight:700">Actif</div>
              </div>
            </div>
            <div style="background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 10px rgba(0,0,0,.05)">
              <div style="font-size:.65rem;color:var(--text-muted);margin-bottom:8px">Activite hebdomadaire</div>
              <div style="display:flex;align-items:flex-end;gap:6px;height:50px">
                <div style="width:14px;background:var(--primary-light);border-radius:3px;height:30px"></div>
                <div style="width:14px;background:var(--primary);border-radius:3px;height:45px"></div>
                <div style="width:14px;background:var(--primary-light);border-radius:3px;height:25px"></div>
                <div style="width:14px;background:var(--primary);border-radius:3px;height:50px"></div>
                <div style="width:14px;background:var(--primary-light);border-radius:3px;height:35px"></div>
                <div style="width:14px;background:var(--primary);border-radius:3px;height:40px"></div>
                <div style="width:14px;background:var(--accent);border-radius:3px;height:28px"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<!-- ===== SHARE SECTION ===== -->
@if ($isVisible('share'))
<section class="section-share">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 text-center">
        <div style="font-size:8rem;animation:float 3s ease-in-out infinite">📱</div>
      </div>
      <div class="col-lg-7">
        <span class="badge-pill">{{ $blockSubtitle('share', 'Signalement guide') }}</span>
        <h2 class="section-title">{!! nl2br(e($blockTitle('share', 'Declarez un dommage et gardez la trace'))) !!}</h2>
        <p class="section-sub">{{ $blockBody('share', "Le consommateur peut suivre chaque etape: depot, traitement, resolution, dossier ouvert et retour d'experience apres la prise en charge.") }}</p>
        <div class="row g-3 mb-4">
          @foreach ($lines($blockMeta('share', 'cards', "Depot simplifie | Un parcours clair pour signaler | 🔗\nDossier protege | Acces depuis votre espace | 🔒")) as $shareLine)
            @php
              [$shareTitle, $shareText, $shareIcon] = $parts($shareLine, 3);
            @endphp
          <div class="col-6">
            <div style="background:var(--bg-light);border-radius:16px;padding:20px">
              <div style="font-size:1.5rem;margin-bottom:8px">{{ $shareIcon ?: '•' }}</div>
              <div style="font-weight:700;font-size:.85rem;margin-bottom:4px">{{ $shareTitle }}</div>
              <div style="font-size:.78rem;color:var(--text-muted)">{{ $shareText }}</div>
            </div>
          </div>
          @endforeach
        </div>
        <a href="{{ route('public.auth') }}" class="btn-primary-custom">{{ $blockMeta('share', 'button', 'Commencer') }} <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
</section>
@endif

<!-- ===== DOWNLOAD BANNER ===== -->
@if ($isVisible('access_banner'))
<section class="download-banner">
  <div class="container position-relative" style="z-index:1">
    <span class="badge-pill" style="background:rgba(255,255,255,.2);color:#fff;">{{ $blockSubtitle('access_banner', 'Disponible en ligne') }}</span>
    <h2>{{ $blockTitle('access_banner', 'Accedez a votre espace MySignal') }}</h2>
    <p>{!! nl2br(e($blockBody('access_banner', 'Activez votre abonnement, suivez vos signalements et retrouvez votre carte membre depuis votre profil.'))) !!}</p>
    <div>
      @foreach ($lines($blockMeta('access_banner', 'buttons', "Consommateur | Espace | bi-person\nUnite Partenaire | Espace | bi-building")) as $buttonLine)
        @php
          [$buttonTitle, $buttonSub, $buttonIcon] = $parts($buttonLine, 3);
        @endphp
        <a href="{{ route('public.auth') }}" class="btn-store">
          <i class="bi {{ $buttonIcon ?: 'bi-person' }}"></i>
          <div><div style="font-size:.65rem;opacity:.6">{{ $buttonSub }}</div><div style="font-weight:800;font-size:.9rem">{{ $buttonTitle }}</div></div>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ===== APP FEATURES ===== -->
@if ($isVisible('app_features'))
<section class="section-app-features">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">{{ $blockSubtitle('app_features', 'Ce que MySignal couvre') }}</span>
        <h2 class="section-title">{{ $blockTitle('app_features', 'Fonctionnalites MySignal') }}</h2>
        <p class="section-sub">{{ $blockBody('app_features', 'Un parcours pense pour signaler, suivre, renouveler son abonnement et donner un retour apres resolution.') }}</p>
      </div>
    </div>
    @php
      $appFeatureItems = $lines($blockMeta('app_features', 'items', "Signalements encadres | Les consommateurs declarent les dommages avec les informations utiles au traitement. | bi-people\nNotifications utiles | Les UP sont prevenues avant expiration et gardent la main sur leur renouvellement. | bi-headset\nHistorique complet | Abonnements, statuts et REX restent consultables dans les espaces dedies. | bi-graph-up-arrow\nRenouvellement manuel | Le statut d'abonnement reste visible, avec une periode de grace d'une journee. | bi-calendar-check\nCarte membre | Les membres actifs disposent d'une carte virtuelle avec QR code sur leur profil. | bi-cloud-check\nParametrage SA | Le Super Administrateur configure les plans, modules, historiques et acces. | bi-puzzle"));
    @endphp
    <div class="row align-items-center g-5">
      <div class="col-lg-4">
        @foreach (array_slice($appFeatureItems, 0, 3) as $featureLine)
          @php
            [$featureTitle, $featureText, $featureIcon] = $parts($featureLine, 3);
          @endphp
          <div class="app-feature-item d-flex gap-3">
            <div class="icon-box"><i class="bi {{ $featureIcon ?: 'bi-check2-circle' }}"></i></div>
            <div>
              <h6>{{ $featureTitle }}</h6>
              <p>{{ $featureText }}</p>
            </div>
          </div>
        @endforeach
      </div>
      <div class="col-lg-4 text-center">
        <div class="phone-mockup-lg float" style="width:200px;margin:auto">
          <div style="height:380px;background:linear-gradient(180deg,#eef8fb 0%,#fff 100%);padding:16px;display:flex;flex-direction:column;gap:10px">
            <div style="background:var(--gradient);border-radius:12px;padding:14px;color:#fff;text-align:center">
              <div style="font-size:.65rem;opacity:.8">Signalements actifs</div>
              <div style="font-size:1.8rem;font-weight:800">1,284</div>
            </div>
            <div style="background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 10px rgba(0,0,0,.06)">
              <div style="font-size:.65rem;color:var(--text-muted);margin-bottom:6px">Modules cles</div>
              <div style="display:flex;flex-direction:column;gap:5px">
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <span style="font-size:.65rem">Suivi</span>
                  <div style="width:70%;height:5px;border-radius:3px;background:#eee;overflow:hidden"><div style="width:80%;height:100%;background:var(--primary);border-radius:3px"></div></div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <span style="font-size:.65rem">REX</span>
                  <div style="width:70%;height:5px;border-radius:3px;background:#eee;overflow:hidden"><div style="width:65%;height:100%;background:var(--accent);border-radius:3px"></div></div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <span style="font-size:.65rem">Abonnement</span>
                  <div style="width:70%;height:5px;border-radius:3px;background:#eee;overflow:hidden"><div style="width:90%;height:100%;background:var(--success);border-radius:3px"></div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        @foreach (array_slice($appFeatureItems, 3, 3) as $featureLine)
          @php
            [$featureTitle, $featureText, $featureIcon] = $parts($featureLine, 3);
          @endphp
          <div class="app-feature-item d-flex gap-3">
            <div class="icon-box" style="background:rgba(255,0,104,.1);color:var(--accent)"><i class="bi {{ $featureIcon ?: 'bi-check2-circle' }}"></i></div>
            <div>
              <h6>{{ $featureTitle }}</h6>
              <p>{{ $featureText }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endif

<!-- ===== SCREENSHOTS ===== -->
@if ($isVisible('screenshots'))
<section class="section-screenshots" id="screenshots">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">{{ $blockSubtitle('screenshots', 'Apercu plateforme') }}</span>
        <h2 class="section-title">{{ $blockTitle('screenshots', 'Ecrans essentiels') }}</h2>
        <p class="section-sub">{{ $blockBody('screenshots', 'Un apercu des espaces utiles pour suivre les signalements, abonnements, REX et parametres.') }}</p>
      </div>
    </div>
    <div class="row g-4">
      @foreach ($lines($blockMeta('screenshots', 'items', "Tableau de bord | 📈\nAbonnements | 👥\nSignalements | 💬\nParametres | ⚙️")) as $screenLine)
        @php
          [$screenLabel, $screenIcon] = $parts($screenLine, 2);
        @endphp
      <div class="col-6 col-md-3">
        <div class="screenshot-card">
          <div class="screenshot-inner" style="background:linear-gradient(135deg,#eef8fb,#d9edf3)">
            <div style="text-align:center">
              <div style="font-size:3rem">{{ $screenIcon ?: '•' }}</div>
              <div class="label mt-2">{{ $screenLabel }}</div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ===== WORK PROCESS ===== -->
@if ($isVisible('process'))
<section class="section-process">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">{{ $blockSubtitle('process', 'Comment ca marche') }}</span>
        <h2 class="section-title">{{ $blockTitle('process', 'Parcours de traitement') }}</h2>
        <p class="section-sub">{{ $blockBody('process', "Un circuit simple pour declarer, suivre, resoudre et capitaliser les retours d'experience.") }}</p>
      </div>
    </div>
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        @foreach ($lines($blockMeta('process', 'steps', "Depot du signalement | Le consommateur renseigne son dommage et garde une trace dans son espace personnel.\nTraitement du dossier | L'UP suit les demandes, gere son abonnement et consulte les informations utiles.\nResolution et REX | Apres resolution ou traitement, le consommateur partage son retour d'experience.")) as $stepLine)
          @php
            [$stepTitle, $stepText] = $parts($stepLine, 2);
          @endphp
          <div class="process-step">
            <div class="step-num">{{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</div>
            <div>
              <h6 style="font-weight:700;margin-bottom:6px">{{ $stepTitle }}</h6>
              <p style="font-size:.85rem;color:var(--text-muted);margin:0">{{ $stepText }}</p>
            </div>
          </div>
        @endforeach
      </div>
      <div class="col-lg-6 text-center">
        <div class="process-chart">
          <div class="chart-center">
            <div class="c-label">PARCOURS<br/>MYSIGNAL</div>
            <div class="c-title">REX</div>
          </div>
        </div>
        <div class="d-flex justify-content-center gap-4 mt-4 flex-wrap">
          @foreach ($lines($blockMeta('process', 'legend', "Signalement\nAbonnement\nTraitement\nREX")) as $legend)
            <div class="d-flex align-items-center gap-2"><div style="width:12px;height:12px;border-radius:50%;background:{{ ['var(--primary)', 'var(--accent)', 'var(--yellow)', 'var(--success)'][$loop->index % 4] }}"></div><span style="font-size:.78rem">{{ $legend }}</span></div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<!-- ===== STATS BANNER ===== -->
@if ($isVisible('stats'))
<section class="stats-banner">
  <div class="container">
    <div class="row text-center g-4">
      @foreach ($lines($blockBody('stats', "10K+ | Consommateurs accompagnes\n245 | Dossiers traites\n45+ | UP abonnees\n12+ | Modules actifs")) as $statLine)
        @php
          [$statValue, $statLabel] = $parts($statLine, 2);
        @endphp
        <div class="col-6 col-md-3">
          <div class="stat-item">
            <div class="num">{{ $statValue }}</div>
            <div class="lbl">{{ $statLabel }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ===== FAQ ===== -->
@if ($isVisible('faq'))
<section class="section-faq" id="faq">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">{{ $blockSubtitle('faq', 'Questions frequentes') }}</span>
        <h2 class="section-title">{{ $blockTitle('faq', 'Comprendre MySignal') }}</h2>
        <p class="section-sub">{{ $blockBody('faq', "Les points essentiels sur l'abonnement, le signalement, la carte membre et les REX.") }}</p>
      </div>
    </div>
    <div class="row align-items-center g-5">
      <div class="col-lg-5 text-center">
        <div class="faq-illus-inner">🤔</div>
      </div>
      <div class="col-lg-7">
        <div class="accordion" id="faqAccordion">
          @foreach ($lines($blockMeta('faq', 'questions', "Comment activer mon espace MySignal ? | Creez votre compte, connectez-vous, puis suivez l'invitation d'abonnement. L'activation vous donne acces aux fonctions liees a votre profil.\nLe renouvellement est-il automatique ? | Non. Le renouvellement est manuel. Une notification est envoyee avant l'expiration, avec une periode de grace d'un jour.\nQuand puis-je faire un retour d'experience ? | Le REX est propose apres la resolution d'un dommage ou apres le traitement d'un dossier ouvert, si le module est autorise.\nQui peut obtenir la carte membre ? | Les membres eligibles avec un abonnement actif disposent d'une carte virtuelle visible dans leur profil, avec QR code.")) as $faqLine)
            @php
              [$question, $answer] = $parts($faqLine, 2);
            @endphp
          <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
            <h2 class="accordion-header">
              <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $loop->iteration }}">
                {{ $question }}
              </button>
            </h2>
            <div id="faq{{ $loop->iteration }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#faqAccordion">
              <div class="accordion-body" style="font-size:.87rem;color:var(--text-muted)">
                {{ $answer }}
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<!-- ===== TESTIMONIALS ===== -->
@if ($isVisible('testimonials'))
<section class="section-testimonials" id="testimonials">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill" style="background:rgba(255,255,255,.15);color:#fff;">{{ $blockSubtitle('testimonials', "Retours d'experience") }}</span>
        <h2 class="section-title" style="color:#fff">{!! nl2br(e($blockTitle('testimonials', 'Ce que les utilisateurs peuvent partager'))) !!}</h2>
      </div>
    </div>
    <div class="row g-4">
      @foreach ($lines($blockMeta('testimonials', 'items', "Le suivi m'a permis de savoir exactement ou en etait mon signalement et quand mon dossier a ete traite. | Consommateur | Signalement resolu | 👩\nLes notifications d'expiration et l'historique des abonnements rendent la gestion plus claire pour notre equipe. | Unite Partenaire | Abonnement actif | 👨\nApres traitement de mon dossier, j'ai pu laisser un REX simple sur le delai, la communication et la qualite de prise en charge. | Membre consommateur | REX apres dossier | 👩")) as $testimonialLine)
        @php
          [$testimonialText, $testimonialAuthor, $testimonialRole, $testimonialAvatar] = $parts($testimonialLine, 4);
        @endphp
      <div class="col-md-4">
        <div class="testimonial-card">
          <div class="quote">"</div>
          <p>{{ $testimonialText }}</p>
          <div class="stars mb-3">★★★★★</div>
          <div class="author">
            <div class="author-avatar">{{ $testimonialAvatar ?: '👤' }}</div>
            <div>
              <div class="author-name">{{ $testimonialAuthor }}</div>
              <div class="author-role">{{ $testimonialRole }}</div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ===== CTA SECTION ===== -->
@if ($isVisible('cta'))
<section class="section-cta">
  <div class="container">
    <div class="cta-box">
      <div class="row align-items-center g-4">
        <div class="col-md-3 text-center">
          <div class="cta-illustration">💡</div>
        </div>
        <div class="col-md-6">
          <h3 style="font-weight:800;margin-bottom:12px">{{ $blockTitle('cta', 'Pret a suivre vos signalements autrement ?') }}</h3>
          <p style="color:var(--text-muted);font-size:.88rem;margin:0">{{ $blockBody('cta', "MySignal rassemble le signalement, le suivi, l'abonnement annuel, la carte membre et les retours d'experience dans un meme parcours.") }}</p>
        </div>
        <div class="col-md-3 text-md-end">
          <a href="{{ route('public.auth') }}" class="btn-primary-custom">{{ $blockMeta('cta', 'button', 'Activer mon espace') }}</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<!-- ===== NEWS ===== -->
@if ($isVisible('news'))
<section class="section-news" id="news">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">{{ $blockSubtitle('news', 'Actualites') }}</span>
        <h2 class="section-title">{{ $blockTitle('news', 'Points forts MySignal') }}</h2>
        <p class="section-sub">{{ $blockBody('news', "Les modules importants pour la protection consommateur, les UP et l'administration.") }}</p>
      </div>
    </div>
    <div class="row g-4">
      @foreach ($lines($blockMeta('news', 'items', "Signalement | Un parcours clair pour declarer un dommage | Les consommateurs peuvent deposer un signalement et retrouver son evolution dans leur tableau de bord. | 📱 | 10 avril 2026\nAbonnement | Un plan annuel parametrable par le SA | Le Super Administrateur gere les plans, les statuts, les notifications et l'historique des UP. | 💡 | 5 avril 2026\nREX | Des retours apres resolution ou traitement | Les REX aident a mesurer le delai, la communication, la qualite et l'equite du traitement. | 🚀 | 28 mars 2026")) as $newsLine)
        @php
          [$newsTag, $newsTitle, $newsText, $newsIcon, $newsDate] = $parts($newsLine, 5);
        @endphp
      <div class="col-md-4">
        <div class="news-card">
          <div class="news-thumb c{{ ($loop->index % 3) + 1 }}">{{ $newsIcon ?: '•' }}</div>
          <div class="news-body">
            <span class="news-tag">{{ $newsTag }}</span>
            <h6>{{ $newsTitle }}</h6>
            <p>{{ $newsText }}</p>
            <div class="news-meta d-flex align-items-center gap-2">
              <i class="bi bi-calendar3"></i> {{ $newsDate }}
              <span>•</span> <i class="bi bi-clock"></i> 5 min read
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ===== CLIENTS ===== -->
@if ($isVisible('clients'))
<section class="section-clients">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">{{ $blockTitle('clients', 'Domaines couverts') }}</h2>
      <p class="section-sub">{{ $blockBody('clients', 'MySignal accompagne plusieurs univers de consommation et de services.') }}</p>
    </div>
    <div class="row align-items-center g-4">
      @foreach ($lines($blockMeta('clients', 'items', "COMMERCE\nSERVICES\nASSURANCE\nTRANSPORT\nSANTE\nENERGIE")) as $client)
        <div class="col-6 col-md-2">
          <div class="client-logo">{{ $client }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ===== FOOTER ===== -->
@if ($isVisible('footer'))
<footer>
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="brand">{{ $blockTitle('footer', 'My') }}<span></span></div>
        <p>{{ $blockBody('footer', "La plateforme qui facilite le signalement, le suivi des dossiers, l'abonnement annuel des UP et les retours d'experience.") }}</p>
        <div class="footer-social">
          <a href="#"><i class="bi bi-facebook"></i></a>
          <a href="#"><i class="bi bi-twitter-x"></i></a>
          <a href="#"><i class="bi bi-linkedin"></i></a>
          <a href="#"><i class="bi bi-instagram"></i></a>
          <a href="#"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6>{{ $blockMeta('footer', 'column_1_title', 'MySignal') }}</h6>
        <ul>
          @foreach ($lines($blockMeta('footer', 'column_1_links', "A propos | #\nProtection consommateur | #\nUnites Partenaires | #\nContact | #")) as $footerLine)
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
          @foreach ($lines($blockMeta('footer', 'column_2_links', "Fonctionnalites | #features\nFAQ | #faq\nREX | #testimonials\nCarte membre | #screenshots")) as $footerLine)
            @php
              [$footerLabel, $footerUrl] = $parts($footerLine, 2);
            @endphp
            <li><a href="{{ $footerUrl ?: '#' }}">{{ $footerLabel }}</a></li>
          @endforeach
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>{{ $blockMeta('footer', 'column_3_title', 'Legal') }}</h6>
        <ul>
          @foreach ($lines($blockMeta('footer', 'column_3_links', "Confidentialite | #\nConditions d'utilisation | #\nCookies | #\nDonnees personnelles | #")) as $footerLine)
            @php
              [$footerLabel, $footerUrl] = $parts($footerLine, 2);
            @endphp
            <li><a href="{{ $footerUrl ?: '#' }}">{{ $footerLabel }}</a></li>
          @endforeach
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>{{ $blockMeta('footer', 'newsletter_title', 'Alertes') }}</h6>
        <p style="font-size:.82rem">{{ $blockMeta('footer', 'newsletter_text', 'Recevez les informations importantes sur les modules MySignal.') }}</p>
        <div class="newsletter-form flex-column">
          <input type="email" placeholder="Votre adresse email" class="mb-2">
          <button>S'inscrire <i class="bi bi-send-fill ms-1"></i></button>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="row align-items-center">
        <div class="col-md-6 text-md-start">© 2026 MySignal. Tous droits reserves.</div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
          {{ $blockSubtitle('footer', 'Plateforme de protection consommateur') }}
        </div>
      </div>
    </div>
  </div>
</footer>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const href = a.getAttribute('href');
      if (!href || href === '#') {
        return;
      }
      const target = document.querySelector(href);
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

  // Navbar shrink on scroll
  window.addEventListener('scroll', () => {
    const nav = document.querySelector('.navbar');
    nav.style.boxShadow = window.scrollY > 40
      ? '0 4px 30px rgba(24,52,71,.15)'
      : '0 2px 20px rgba(24,52,71,.08)';
  });

  // Counter animation
  const counters = document.querySelectorAll('.stat-num, .num');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const text = el.textContent;
        const num = parseFloat(text.replace(/[^0-9.]/g, ''));
        const suffix = text.replace(/[0-9.]/g, '');
        let start = 0;
        const step = num / 40;
        const timer = setInterval(() => {
          start += step;
          if (start >= num) { el.textContent = text; clearInterval(timer); }
          else { el.textContent = (Number.isInteger(num) ? Math.floor(start) : start.toFixed(1)) + suffix; }
        }, 30);
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.5 });
  counters.forEach(c => observer.observe(c));
</script>
</body>
</html>
