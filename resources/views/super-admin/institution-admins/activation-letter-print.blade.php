<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Courrier point focal - {{ $letter->activation_code }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer referrerpolicy="no-referrer"></script>
    <style>
        body { margin: 0; background: #eef2f7; color: #111827; font-family: Arial, sans-serif; }
        .toolbar { position: sticky; top: 0; display: flex; justify-content: flex-end; gap: 10px; padding: 12px 18px; background: #fff; border-bottom: 1px solid #e5e7eb; }
        .toolbar button { border: 0; border-radius: 8px; padding: 10px 14px; background: #111827; color: #fff; cursor: pointer; }
        .page { width: 210mm; min-height: 297mm; margin: 18px auto; padding: 20mm 22mm; background: #fff; box-shadow: 0 20px 50px rgba(15, 23, 42, .12); box-sizing: border-box; }
        .official-header { border-bottom: 3px solid #ffa117; padding-bottom: 16px; margin-bottom: 28px; }
        .header-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 18px; }
        .brand-title { font-size: 18px; font-weight: 800; margin: 0 0 4px; }
        .brand-subtitle { margin: 0; color: #667085; }
        .reference { display: inline-block; border: 1px solid rgba(103, 145, 255, .28); background: rgba(103, 145, 255, .08); border-radius: 999px; padding: 7px 12px; font-size: 12px; color: #24426f; font-weight: 700; }
        .logo { max-width: 130px; max-height: 95px; object-fit: contain; margin-bottom: 14px; }
        .logo-left { text-align: left; }
        .logo-center { text-align: center; }
        .logo-right { text-align: right; }
        .date { text-align: right; color: #475467; margin: 16px 0 36px; }
        .subject { font-weight: 700; margin: 28px 0; }
        .content { line-height: 1.72; font-size: 15px; }
        .content p { margin: 0 0 13px; }
        .activation { display: flex; justify-content: space-between; gap: 24px; align-items: center; margin-top: 34px; padding: 18px; border: 1px solid rgba(255, 161, 23, .5); background: #fffaf0; border-radius: 12px; }
        .signature { margin: 42px 0 24px auto; max-width: 280px; text-align: right; }
        .signature-content { line-height: 1.6; margin-bottom: 22px; }
        .signature-name { font-weight: 800; }
        .signature-title { color: #667085; font-size: 13px; }
        .footer { margin-top: 42px; padding-top: 16px; border-top: 1px solid #e5e7eb; color: #667085; font-size: 12px; }
        .muted { color: #667085; font-size: 13px; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { width: auto; min-height: auto; margin: 0; padding: 18mm; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Imprimer ou enregistrer en PDF</button>
    </div>
    <main class="page">
        @php
            $logoUrl = $letter->logoUrl();
        @endphp
        <header class="official-header">
            @if ($logoUrl && $letter->logo_position !== 'none')
                <div class="logo-{{ $letter->logo_position }}">
                    <img src="{{ $logoUrl }}" class="logo" alt="Logo">
                </div>
            @endif
            <div class="header-row">
                <div>
                    <p class="brand-title">Union Fédérale des Consommateurs</p>
                    <p class="brand-subtitle">Programme My-Signal</p>
                </div>
                <div class="reference">Réf. {{ $letter->activation_code }}</div>
            </div>
        </header>
        <div class="date">Abidjan, le {{ now()->format('d/m/Y') }}</div>
        <p><strong>À l’attention de {{ $institutionAdmin->organization?->name ?: 'l’institution' }}</strong></p>
        <p class="subject">Objet : {{ $letter->letter_subject }}</p>
        <div class="content">{!! $letter->contentHtml() !!}</div>
        <div class="signature">
            <div class="signature-content">{!! $letter->signatureHtml() !!}</div>
            <div class="signature-name">{{ $letter->signature_name ?: 'Le Coordonnateur du programme My-Signal' }}</div>
            <div class="signature-title">{{ $letter->signature_title ?: 'Union Fédérale des Consommateurs' }}</div>
        </div>
        <div class="activation">
            <div>
                <div class="muted">Code d’activation</div>
                <h2>{{ $letter->activation_code }}</h2>
                <div class="muted">Lien du formulaire</div>
                <div>{{ $letter->activation_url }}</div>
            </div>
            <div id="activationQr"></div>
        </div>
        <div class="footer">
            Document officiel généré par My-Signal pour la désignation du point focal institutionnel.
        </div>
    </main>
    <script>
        window.addEventListener('load', function () {
            const container = document.getElementById('activationQr');
            if (container && window.QRCode) {
                new QRCode(container, {
                    text: @json($letter->activation_url),
                    width: 142,
                    height: 142,
                    correctLevel: QRCode.CorrectLevel.M,
                });
            }
        });
    </script>
</body>
</html>
