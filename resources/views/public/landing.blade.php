<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MySignal - Plateforme de signalement consommateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --primary: #183447;
      --primary-dark: #102736;
      --primary-light: #256f8f;
      --accent: #ff0068;
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
        <li class="nav-item"><a class="nav-link" href="#features">Fonctionnalites</a></li>
        <li class="nav-item"><a class="nav-link" href="#screenshots">Apercus</a></li>
        <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="#news">Actualites</a></li>
        <li class="nav-item ms-2"><a class="nav-link btn-nav" href="{{ route('public.auth') }}">Se connecter et signaler maintenant</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 pb-5">
        <p class="badge-pill" style="background:rgba(255,255,255,.15);color:#fff;">Plateforme de protection consommateur</p>
        <h1 class="hero-title fade-up">Signalez, <span>suivez</span> et faites valoir vos droits</h1>
        <p class="hero-text fade-up delay-1">MySignal accompagne les consommateurs et les Unites Partenaires dans le suivi des signalements, des abonnements, des REX et des dossiers traites.</p>
        <div class="fade-up delay-2">
          <a href="#features" class="btn-hero-primary">Activer mon acces</a>
          <a href="#" class="btn-hero-outline">
            <i class="bi bi-play-circle-fill me-1"></i> Voir le parcours
          </a>
        </div>
        <div class="hero-stats d-flex align-items-center fade-up delay-3">
          <div>
            <div class="stat-num">573K+</div>
            <div class="stat-label">Utilisateurs actifs</div>
          </div>
          <div class="divider"></div>
          <div>
            <div class="stat-num">26,675</div>
            <div class="stat-label">Signalements suivis</div>
          </div>
          <div class="divider"></div>
          <div>
            <div class="stat-num">9.2K</div>
            <div class="stat-label">Retours collectes</div>
          </div>
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

<!-- ===== FEATURES STRIP ===== -->
<section class="features-strip">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-md-3">
        <div class="feature-card">
          <div class="feature-icon-wrap purple"><i class="bi bi-lightning-charge-fill"></i></div>
          <h6>Signalement rapide</h6>
          <p>Deposez un dommage ou une reclamation en quelques etapes claires.</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-card">
          <div class="feature-icon-wrap green"><i class="bi bi-shield-fill-check"></i></div>
          <h6>Espace securise</h6>
          <p>Vos dossiers, abonnements et retours restent accessibles depuis votre compte.</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-card">
          <div class="feature-icon-wrap orange"><i class="bi bi-bar-chart-fill"></i></div>
          <h6>Suivi lisible</h6>
          <p>Consultez l'etat de vos signalements, dossiers et traitements.</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-card">
          <div class="feature-icon-wrap blue"><i class="bi bi-people-fill"></i></div>
          <h6>Dialogue UP</h6>
          <p>Les Unites Partenaires disposent d'un espace pour traiter les demandes.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== MANAGE SECTION ===== -->
<section class="section-manage" id="features">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 order-lg-2">
        <span class="badge-pill">Pourquoi MySignal ?</span>
        <h2 class="section-title">Un seul espace pour suivre<br/>vos droits consommateur</h2>
        <p class="section-sub">MySignal centralise les signalements, les dossiers ouverts, les abonnements annuels, les notifications et les retours d'experience.</p>
        <ul class="check-list ps-0 mb-4">
          <li>Creation et suivi des signalements consommateurs</li>
          <li>Notifications avant expiration des abonnements</li>
          <li>Carte membre virtuelle avec QR code pour les abonnes actifs</li>
          <li>Historique des abonnements et des REX</li>
          <li>Parametrage par le Super Administrateur</li>
          <li>Tableau de bord clair pour les UP et les consommateurs</li>
        </ul>
        <a href="#features" class="btn-primary-custom">En savoir plus <i class="bi bi-arrow-right ms-1"></i></a>
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

<!-- ===== SHARE SECTION ===== -->
<section class="section-share">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 text-center">
        <div style="font-size:8rem;animation:float 3s ease-in-out infinite">📱</div>
      </div>
      <div class="col-lg-7">
        <span class="badge-pill">Signalement guide</span>
        <h2 class="section-title">Declarez un dommage<br/>et gardez la trace</h2>
        <p class="section-sub">Le consommateur peut suivre chaque etape: depot, traitement, resolution, dossier ouvert et retour d'experience apres la prise en charge.</p>
        <div class="row g-3 mb-4">
          <div class="col-6">
            <div style="background:var(--bg-light);border-radius:16px;padding:20px">
              <div style="font-size:1.5rem;margin-bottom:8px">🔗</div>
              <div style="font-weight:700;font-size:.85rem;margin-bottom:4px">Depot simplifie</div>
              <div style="font-size:.78rem;color:var(--text-muted)">Un parcours clair pour signaler</div>
            </div>
          </div>
          <div class="col-6">
            <div style="background:var(--bg-light);border-radius:16px;padding:20px">
              <div style="font-size:1.5rem;margin-bottom:8px">🔒</div>
              <div style="font-weight:700;font-size:.85rem;margin-bottom:4px">Dossier protege</div>
              <div style="font-size:.78rem;color:var(--text-muted)">Acces depuis votre espace</div>
            </div>
          </div>
        </div>
        <a href="#features" class="btn-primary-custom">Commencer <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
</section>

<!-- ===== DOWNLOAD BANNER ===== -->
<section class="download-banner">
  <div class="container position-relative" style="z-index:1">
    <span class="badge-pill" style="background:rgba(255,255,255,.2);color:#fff;">Disponible en ligne</span>
    <h2>Accedez a votre espace MySignal</h2>
    <p>Activez votre abonnement, suivez vos signalements et retrouvez<br/>votre carte membre depuis votre profil.</p>
    <div>
      <a href="#" class="btn-store">
        <i class="bi bi-apple"></i>
        <div><div style="font-size:.65rem;opacity:.6">Espace</div><div style="font-weight:800;font-size:.9rem">Consommateur</div></div>
      </a>
      <a href="#" class="btn-store">
        <i class="bi bi-google-play"></i>
        <div><div style="font-size:.65rem;opacity:.6">Espace</div><div style="font-weight:800;font-size:.9rem">Unite Partenaire</div></div>
      </a>
    </div>
  </div>
</section>

<!-- ===== APP FEATURES ===== -->
<section class="section-app-features">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">Ce que MySignal couvre</span>
        <h2 class="section-title">Fonctionnalites MySignal</h2>
        <p class="section-sub">Un parcours pense pour signaler, suivre, renouveler son abonnement et donner un retour apres resolution.</p>
      </div>
    </div>
    <div class="row align-items-center g-5">
      <div class="col-lg-4">
        <div class="app-feature-item d-flex gap-3">
          <div class="icon-box"><i class="bi bi-people"></i></div>
          <div>
            <h6>Signalements encadres</h6>
            <p>Les consommateurs declarent les dommages avec les informations utiles au traitement.</p>
          </div>
        </div>
        <div class="app-feature-item d-flex gap-3">
          <div class="icon-box"><i class="bi bi-headset"></i></div>
          <div>
            <h6>Notifications utiles</h6>
            <p>Les UP sont prevenues avant expiration et gardent la main sur leur renouvellement.</p>
          </div>
        </div>
        <div class="app-feature-item d-flex gap-3">
          <div class="icon-box"><i class="bi bi-graph-up-arrow"></i></div>
          <div>
            <h6>Historique complet</h6>
            <p>Abonnements, statuts et REX restent consultables dans les espaces dedies.</p>
          </div>
        </div>
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
        <div class="app-feature-item d-flex gap-3">
          <div class="icon-box" style="background:rgba(255,0,104,.1);color:var(--accent)"><i class="bi bi-calendar-check"></i></div>
          <div>
            <h6>Renouvellement manuel</h6>
            <p>Le statut d'abonnement reste visible, avec une periode de grace d'une journee.</p>
          </div>
        </div>
        <div class="app-feature-item d-flex gap-3">
          <div class="icon-box" style="background:rgba(91,235,175,.18);color:#15955f"><i class="bi bi-cloud-check"></i></div>
          <div>
            <h6>Carte membre</h6>
            <p>Les membres actifs disposent d'une carte virtuelle avec QR code sur leur profil.</p>
          </div>
        </div>
        <div class="app-feature-item d-flex gap-3">
          <div class="icon-box" style="background:rgba(37,111,143,.12);color:var(--primary-light)"><i class="bi bi-puzzle"></i></div>
          <div>
            <h6>Parametrage SA</h6>
            <p>Le Super Administrateur configure les plans, modules, historiques et acces.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== SCREENSHOTS ===== -->
<section class="section-screenshots" id="screenshots">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">Apercu plateforme</span>
        <h2 class="section-title">Ecrans essentiels</h2>
        <p class="section-sub">Un apercu des espaces utiles pour suivre les signalements, abonnements, REX et parametres.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-6 col-md-3">
        <div class="screenshot-card">
          <div class="screenshot-inner" style="background:linear-gradient(135deg,#eef8fb,#d9edf3)">
            <div style="text-align:center">
              <div style="font-size:3rem">📈</div>
              <div class="label mt-2">Tableau de bord</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="screenshot-card">
          <div class="screenshot-inner" style="background:linear-gradient(135deg,#fff4e6,#ffe0b2)">
            <div style="text-align:center">
              <div style="font-size:3rem">👥</div>
              <div class="label mt-2" style="background:var(--accent)">Abonnements</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="screenshot-card">
          <div class="screenshot-inner" style="background:linear-gradient(135deg,#e8f5e9,#c8e6c9)">
            <div style="text-align:center">
              <div style="font-size:3rem">💬</div>
              <div class="label mt-2" style="background:#15955f">Signalements</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="screenshot-card">
          <div class="screenshot-inner" style="background:linear-gradient(135deg,#e3f2fd,#bbdefb)">
            <div style="text-align:center">
              <div style="font-size:3rem">⚙️</div>
              <div class="label mt-2" style="background:var(--primary-light)">Parametres</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== WORK PROCESS ===== -->
<section class="section-process">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">Comment ca marche</span>
        <h2 class="section-title">Parcours de traitement</h2>
        <p class="section-sub">Un circuit simple pour declarer, suivre, resoudre et capitaliser les retours d'experience.</p>
      </div>
    </div>
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="process-step">
          <div class="step-num">01</div>
          <div>
            <h6 style="font-weight:700;margin-bottom:6px">Depot du signalement</h6>
            <p style="font-size:.85rem;color:var(--text-muted);margin:0">Le consommateur renseigne son dommage et garde une trace dans son espace personnel.</p>
          </div>
        </div>
        <div class="process-step">
          <div class="step-num">02</div>
          <div>
            <h6 style="font-weight:700;margin-bottom:6px">Traitement du dossier</h6>
            <p style="font-size:.85rem;color:var(--text-muted);margin:0">L'UP suit les demandes, gere son abonnement et consulte les informations utiles.</p>
          </div>
        </div>
        <div class="process-step">
          <div class="step-num">03</div>
          <div>
            <h6 style="font-weight:700;margin-bottom:6px">Resolution et REX</h6>
            <p style="font-size:.85rem;color:var(--text-muted);margin:0">Apres resolution ou traitement, le consommateur partage son retour d'experience.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-6 text-center">
        <div class="process-chart">
          <div class="chart-center">
            <div class="c-label">PARCOURS<br/>MYSIGNAL</div>
            <div class="c-title">REX</div>
          </div>
        </div>
        <div class="d-flex justify-content-center gap-4 mt-4 flex-wrap">
          <div class="d-flex align-items-center gap-2"><div style="width:12px;height:12px;border-radius:50%;background:var(--primary)"></div><span style="font-size:.78rem">Signalement</span></div>
          <div class="d-flex align-items-center gap-2"><div style="width:12px;height:12px;border-radius:50%;background:var(--accent)"></div><span style="font-size:.78rem">Abonnement</span></div>
          <div class="d-flex align-items-center gap-2"><div style="width:12px;height:12px;border-radius:50%;background:var(--yellow)"></div><span style="font-size:.78rem">Traitement</span></div>
          <div class="d-flex align-items-center gap-2"><div style="width:12px;height:12px;border-radius:50%;background:var(--success)"></div><span style="font-size:.78rem">REX</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== STATS BANNER ===== -->
<section class="stats-banner">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="num">10K+</div>
          <div class="lbl">Consommateurs accompagnes</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="num">245</div>
          <div class="lbl">Dossiers traites</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="num">45+</div>
          <div class="lbl">UP abonnees</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="num">12+</div>
          <div class="lbl">Modules actifs</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== FAQ ===== -->
<section class="section-faq" id="faq">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">Questions frequentes</span>
        <h2 class="section-title">Comprendre MySignal</h2>
        <p class="section-sub">Les points essentiels sur l'abonnement, le signalement, la carte membre et les REX.</p>
      </div>
    </div>
    <div class="row align-items-center g-5">
      <div class="col-lg-5 text-center">
        <div class="faq-illus-inner">🤔</div>
      </div>
      <div class="col-lg-7">
        <div class="accordion" id="faqAccordion">
          <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
            <h2 class="accordion-header">
              <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                Comment activer mon espace MySignal ?
              </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
              <div class="accordion-body" style="font-size:.87rem;color:var(--text-muted)">
                Creez votre compte, connectez-vous, puis suivez l'invitation d'abonnement. L'activation vous donne acces aux fonctions liees a votre profil.
              </div>
            </div>
          </div>
          <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                Le renouvellement est-il automatique ?
              </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body" style="font-size:.87rem;color:var(--text-muted)">
                Non. Le renouvellement est manuel. Une notification est envoyee avant l'expiration, avec une periode de grace d'un jour.
              </div>
            </div>
          </div>
          <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                Quand puis-je faire un retour d'experience ?
              </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body" style="font-size:.87rem;color:var(--text-muted)">
                Le REX est propose apres la resolution d'un dommage ou apres le traitement d'un dossier ouvert, si le module est autorise.
              </div>
            </div>
          </div>
          <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                Qui peut obtenir la carte membre ?
              </button>
            </h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body" style="font-size:.87rem;color:var(--text-muted)">
                Les membres eligibles avec un abonnement actif disposent d'une carte virtuelle visible dans leur profil, avec QR code.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="section-testimonials" id="testimonials">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill" style="background:rgba(255,255,255,.15);color:#fff;">Retours d'experience</span>
        <h2 class="section-title" style="color:#fff">Ce que les utilisateurs<br/>peuvent partager</h2>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="testimonial-card">
          <div class="quote">"</div>
          <p>Le suivi m'a permis de savoir exactement ou en etait mon signalement et quand mon dossier a ete traite.</p>
          <div class="stars mb-3">★★★★★</div>
          <div class="author">
            <div class="author-avatar">👩</div>
            <div>
              <div class="author-name">Consommateur</div>
              <div class="author-role">Signalement resolu</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial-card">
          <div class="quote">"</div>
          <p>Les notifications d'expiration et l'historique des abonnements rendent la gestion plus claire pour notre equipe.</p>
          <div class="stars mb-3">★★★★★</div>
          <div class="author">
            <div class="author-avatar">👨</div>
            <div>
              <div class="author-name">Unite Partenaire</div>
              <div class="author-role">Abonnement actif</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial-card">
          <div class="quote">"</div>
          <p>Apres traitement de mon dossier, j'ai pu laisser un REX simple sur le delai, la communication et la qualite de prise en charge.</p>
          <div class="stars mb-3">★★★★★</div>
          <div class="author">
            <div class="author-avatar">👩</div>
            <div>
              <div class="author-name">Membre consommateur</div>
              <div class="author-role">REX apres dossier</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== CTA SECTION ===== -->
<section class="section-cta">
  <div class="container">
    <div class="cta-box">
      <div class="row align-items-center g-4">
        <div class="col-md-3 text-center">
          <div class="cta-illustration">💡</div>
        </div>
        <div class="col-md-6">
          <h3 style="font-weight:800;margin-bottom:12px">Pret a suivre vos signalements autrement ?</h3>
          <p style="color:var(--text-muted);font-size:.88rem;margin:0">MySignal rassemble le signalement, le suivi, l'abonnement annuel, la carte membre et les retours d'experience dans un meme parcours.</p>
        </div>
        <div class="col-md-3 text-md-end">
          <a href="#features" class="btn-primary-custom">Activer mon espace</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== NEWS ===== -->
<section class="section-news" id="news">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-lg-6 mx-auto">
        <span class="badge-pill">Actualites</span>
        <h2 class="section-title">Points forts MySignal</h2>
        <p class="section-sub">Les modules importants pour la protection consommateur, les UP et l'administration.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="news-card">
          <div class="news-thumb c1">📱</div>
          <div class="news-body">
            <span class="news-tag">Signalement</span>
            <h6>Un parcours clair pour declarer un dommage</h6>
            <p>Les consommateurs peuvent deposer un signalement et retrouver son evolution dans leur tableau de bord.</p>
            <div class="news-meta d-flex align-items-center gap-2">
              <i class="bi bi-calendar3"></i> 10 avril 2026
              <span>•</span> <i class="bi bi-clock"></i> 5 min read
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="news-card">
          <div class="news-thumb c2">💡</div>
          <div class="news-body">
            <span class="news-tag" style="background:rgba(255,0,104,.1);color:var(--accent)">Abonnement</span>
            <h6>Un plan annuel parametrable par le SA</h6>
            <p>Le Super Administrateur gere les plans, les statuts, les notifications et l'historique des UP.</p>
            <div class="news-meta d-flex align-items-center gap-2">
              <i class="bi bi-calendar3"></i> 5 avril 2026
              <span>•</span> <i class="bi bi-clock"></i> 8 min read
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="news-card">
          <div class="news-thumb c3">🚀</div>
          <div class="news-body">
            <span class="news-tag" style="background:rgba(91,235,175,.18);color:#15955f">REX</span>
            <h6>Des retours apres resolution ou traitement</h6>
            <p>Les REX aident a mesurer le delai, la communication, la qualite et l'equite du traitement.</p>
            <div class="news-meta d-flex align-items-center gap-2">
              <i class="bi bi-calendar3"></i> 28 mars 2026
              <span>•</span> <i class="bi bi-clock"></i> 12 min read
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== CLIENTS ===== -->
<section class="section-clients">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">Domaines couverts</h2>
      <p class="section-sub">MySignal accompagne plusieurs univers de consommation et de services.</p>
    </div>
    <div class="row align-items-center g-4">
      <div class="col-6 col-md-2">
        <div class="client-logo">COMMERCE</div>
      </div>
      <div class="col-6 col-md-2">
        <div class="client-logo">SERVICES</div>
      </div>
      <div class="col-6 col-md-2">
        <div class="client-logo">ASSURANCE</div>
      </div>
      <div class="col-6 col-md-2">
        <div class="client-logo">TRANSPORT</div>
      </div>
      <div class="col-6 col-md-2">
        <div class="client-logo">SANTE</div>
      </div>
      <div class="col-6 col-md-2">
        <div class="client-logo">ENERGIE</div>
      </div>
    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="brand">My<span>Signal</span></div>
        <p>La plateforme qui facilite le signalement, le suivi des dossiers, l'abonnement annuel des UP et les retours d'experience.</p>
        <div class="footer-social">
          <a href="#"><i class="bi bi-facebook"></i></a>
          <a href="#"><i class="bi bi-twitter-x"></i></a>
          <a href="#"><i class="bi bi-linkedin"></i></a>
          <a href="#"><i class="bi bi-instagram"></i></a>
          <a href="#"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6>MySignal</h6>
        <ul>
          <li><a href="#">A propos</a></li>
          <li><a href="#">Protection consommateur</a></li>
          <li><a href="#">Unites Partenaires</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>Modules</h6>
        <ul>
          <li><a href="#features">Fonctionnalites</a></li>
          <li><a href="#faq">FAQ</a></li>
          <li><a href="#testimonials">REX</a></li>
          <li><a href="#screenshots">Carte membre</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>Legal</h6>
        <ul>
          <li><a href="#">Confidentialite</a></li>
          <li><a href="#">Conditions d'utilisation</a></li>
          <li><a href="#">Cookies</a></li>
          <li><a href="#">Donnees personnelles</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>Alertes</h6>
        <p style="font-size:.82rem">Recevez les informations importantes sur les modules MySignal.</p>
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
          Plateforme de protection consommateur
        </div>
      </div>
    </div>
  </div>
</footer>

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
