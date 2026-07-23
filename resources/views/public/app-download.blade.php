<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Télécharger My-Signal</title>
  <link rel="icon" type="image/png" href="{{ asset('image/logo/logo-my-signal.png') }}" />
  <link rel="apple-touch-icon" href="{{ asset('image/logo/logo-my-signal.png') }}" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
  <style>
    :root {
      --ink: #142b36;
      --muted: #60717a;
      --surface: #ffffff;
      --soft: #f4f8fa;
      --line: #dbe6ea;
      --teal: #1f7a8c;
      --gold: #c9a227;
      --pink: #ff0068;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: var(--ink);
      background:
        linear-gradient(145deg, rgba(31, 122, 140, .10), rgba(255, 255, 255, 0) 38%),
        linear-gradient(35deg, rgba(201, 162, 39, .16), rgba(255, 255, 255, 0) 42%),
        var(--soft);
    }

    .page {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 32px 18px;
    }

    .shell {
      width: min(980px, 100%);
      display: grid;
      grid-template-columns: 1.05fr .95fr;
      gap: 42px;
      align-items: center;
    }

    .brand {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 34px;
      color: var(--ink);
      text-decoration: none;
      font-weight: 800;
      font-size: 1.05rem;
    }

    .brand img {
      width: 44px;
      height: 44px;
      object-fit: contain;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 16px;
      color: var(--teal);
      font-weight: 700;
      font-size: .78rem;
      text-transform: uppercase;
      letter-spacing: .08em;
    }

    h1 {
      margin: 0;
      max-width: 620px;
      font-size: clamp(2.25rem, 5vw, 4.8rem);
      line-height: .96;
      letter-spacing: 0;
    }

    .lead {
      margin: 22px 0 0;
      max-width: 560px;
      color: var(--muted);
      font-size: 1.08rem;
      line-height: 1.7;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 34px;
    }

    .store-button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      min-height: 52px;
      padding: 0 18px;
      border-radius: 8px;
      border: 1px solid transparent;
      text-decoration: none;
      font-weight: 750;
      color: #fff;
      background: var(--ink);
      box-shadow: 0 16px 34px rgba(20, 43, 54, .18);
    }

    .store-button.android {
      background: var(--teal);
    }

    .store-button.ios {
      background: var(--ink);
    }

    .store-button.secondary {
      color: var(--ink);
      background: var(--surface);
      border-color: var(--line);
      box-shadow: none;
    }

    .qr-panel {
      background: rgba(255, 255, 255, .82);
      border: 1px solid rgba(219, 230, 234, .9);
      border-radius: 8px;
      padding: 26px;
      box-shadow: 0 28px 80px rgba(20, 43, 54, .13);
      backdrop-filter: blur(14px);
    }

    .phone-frame {
      border-radius: 8px;
      border: 1px solid var(--line);
      background: linear-gradient(180deg, #fff, #f7fafb);
      padding: 22px;
    }

    .qr-box {
      aspect-ratio: 1 / 1;
      display: grid;
      place-items: center;
      width: min(100%, 310px);
      margin: 0 auto;
      border-radius: 8px;
      background: #fff;
      border: 1px solid #edf2f4;
      padding: 18px;
    }

    .qr-box canvas,
    .qr-box img {
      width: 100% !important;
      height: 100% !important;
      max-width: 250px;
      max-height: 250px;
    }

    .qr-title {
      margin: 20px 0 6px;
      text-align: center;
      font-size: 1.05rem;
      font-weight: 800;
    }

    .qr-copy {
      margin: 0 auto;
      max-width: 300px;
      text-align: center;
      color: var(--muted);
      font-size: .94rem;
      line-height: 1.6;
    }

    .notice {
      display: none;
      margin-top: 24px;
      padding: 14px 16px;
      border-radius: 8px;
      color: #6f4d00;
      background: rgba(201, 162, 39, .16);
      border: 1px solid rgba(201, 162, 39, .28);
      font-size: .94rem;
    }

    .mobile-hint {
      display: none;
    }

    @media (max-width: 820px) {
      .page {
        place-items: start center;
        padding-top: 28px;
      }

      .shell {
        grid-template-columns: 1fr;
        gap: 26px;
      }

      .brand {
        margin-bottom: 26px;
      }

      .qr-panel {
        display: none;
      }

      .mobile-hint {
        display: block;
      }

      .actions {
        display: grid;
      }
    }
  </style>
</head>
<body>
  <main class="page">
    <section class="shell">
      <div>
        <a class="brand" href="{{ route('public.landing') }}">
          <img src="{{ asset('image/logo/logo-my-signal.png') }}" alt="My-Signal" />
          <span>My-Signal</span>
        </a>

        <div class="eyebrow">
          <i class="bi bi-phone"></i>
          Application mobile
        </div>

        <h1>Télécharger My-Signal</h1>
        <p class="lead">
          Accédez rapidement à l’application depuis votre téléphone. Sur mobile,
          cette page ouvre automatiquement la boutique adaptée à votre appareil.
        </p>

        <div class="actions">
          <a class="store-button android" href="{{ $androidUrl }}">
            <i class="bi bi-google-play"></i>
            Google Play
          </a>
          <a class="store-button ios" href="{{ $iosUrl }}">
            <i class="bi bi-apple"></i>
            App Store
          </a>
          <a class="store-button secondary" href="{{ route('public.landing') }}">
            <i class="bi bi-house-door"></i>
            Accueil
          </a>
        </div>

        <div class="notice mobile-hint" id="mobileNotice">
          Redirection en cours. Si rien ne se passe, choisissez votre boutique ci-dessus.
        </div>
      </div>

      <aside class="qr-panel" aria-label="QR code de téléchargement">
        <div class="phone-frame">
          <div class="qr-box" id="downloadQr"></div>
          <h2 class="qr-title">Scannez avec votre téléphone</h2>
          <p class="qr-copy">
            Le QR code ouvre cette page, puis redirige automatiquement vers Google Play ou l’App Store.
          </p>
        </div>
      </aside>
    </section>
  </main>

  <script>
    window.addEventListener('DOMContentLoaded', function () {
      const androidUrl = @json($androidUrl);
      const iosUrl = @json($iosUrl);
      const downloadUrl = @json($downloadUrl);
      const userAgent = navigator.userAgent || navigator.vendor || '';
      const isAndroid = /android/i.test(userAgent);
      const isIos = /iPad|iPhone|iPod/.test(userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

      if (window.QRCode && document.getElementById('downloadQr')) {
        new QRCode(document.getElementById('downloadQr'), {
          text: downloadUrl,
          width: 250,
          height: 250,
          colorDark: '#142b36',
          colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.M,
        });
      }

      if (isAndroid || isIos) {
        document.getElementById('mobileNotice')?.style.setProperty('display', 'block');
        window.setTimeout(function () {
          window.location.href = isAndroid ? androidUrl : iosUrl;
        }, 450);
      }
    });
  </script>
</body>
</html>
