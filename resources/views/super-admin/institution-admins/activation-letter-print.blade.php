<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Courrier point focal - {{ $letter->activation_code }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer referrerpolicy="no-referrer"></script>
    @php
        $header = $letter->headerSettings();
        $footer = $letter->footerSettings();
        $textStyle = function (array $settings): string {
            return collect([
                'font-size: '.(int) ($settings['size'] ?? 10).'px',
                'color: '.($settings['color'] ?? '#475467'),
                ! empty($settings['bold']) ? 'font-weight: 800' : 'font-weight: 400',
                ! empty($settings['italic']) ? 'font-style: italic' : 'font-style: normal',
            ])->implode('; ');
        };
        $splitForTwoPages = function (string $html): array {
            $blocks = preg_split('/(?=<p\b|<div\b|<h[2-4]\b|<ul\b|<ol\b)/i', trim($html), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if ($blocks === []) {
                return [$html, ''];
            }

            $first = '';
            $second = '';
            $firstLineBudget = 22;
            $usedLines = 0;
            $lineWidth = 92;

            foreach ($blocks as $block) {
                $blockText = trim(html_entity_decode(strip_tags($block), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $blockLength = mb_strlen($blockText);
                $blockLines = max(1, (int) ceil($blockLength / $lineWidth)) + 1;

                if ($first !== '' && ($usedLines + $blockLines) > $firstLineBudget) {
                    $remainingLines = max(0, $firstLineBudget - $usedLines);

                    if ($remainingLines > 1) {
                        $words = preg_split('/\s+/u', $blockText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        $firstWords = [];
                        $secondWords = [];
                        $lineCount = 1;
                        $lineLength = 0;

                        foreach ($words as $word) {
                            $wordLength = mb_strlen($word);
                            $nextLength = $lineLength + $wordLength + ($lineLength > 0 ? 1 : 0);

                            if ($nextLength > $lineWidth) {
                                $lineCount++;
                                $lineLength = $wordLength;
                            } else {
                                $lineLength = $nextLength;
                            }

                            if ($lineCount <= $remainingLines) {
                                $firstWords[] = $word;
                            } else {
                                $secondWords[] = $word;
                            }
                        }

                        $first .= '<p>'.e(implode(' ', $firstWords)).'</p>';
                        $second .= '<p>'.e(implode(' ', $secondWords)).'</p>';
                        $usedLines = $firstLineBudget;
                    } else {
                        $second .= $block;
                    }
                } elseif ($first === '' && $blockLines > $firstLineBudget) {
                    $words = preg_split('/\s+/u', $blockText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $firstWords = [];
                    $secondWords = [];
                    $lineCount = 1;
                    $lineLength = 0;

                    foreach ($words as $word) {
                        $wordLength = mb_strlen($word);
                        $nextLength = $lineLength + $wordLength + ($lineLength > 0 ? 1 : 0);

                        if ($nextLength > $lineWidth) {
                            $lineCount++;
                            $lineLength = $wordLength;
                        } else {
                            $lineLength = $nextLength;
                        }

                        if ($lineCount <= $firstLineBudget) {
                            $firstWords[] = $word;
                        } else {
                            $secondWords[] = $word;
                        }
                    }

                    $first .= '<p>'.e(implode(' ', $firstWords)).'</p>';
                    $second .= '<p>'.e(implode(' ', $secondWords)).'</p>';
                    $usedLines = $firstLineBudget;
                } else {
                    $first .= $block;
                    $usedLines += $blockLines;
                }
            }

            return [trim($first), trim($second)];
        };
        [$mainLetterContent, $secondPageLetterContent] = $splitForTwoPages((string) $letter->letter_content);
    @endphp
    <style>
        body { margin: 0; background: #eef2f7; color: #111827; font-family: Arial, sans-serif; }
        .toolbar { position: sticky; top: 0; display: flex; justify-content: flex-end; gap: 10px; padding: 12px 18px; background: #fff; border-bottom: 1px solid #e5e7eb; }
        .toolbar button { border: 0; border-radius: 8px; padding: 10px 14px; background: #111827; color: #fff; cursor: pointer; }
        .page { display: flex; flex-direction: column; width: 210mm; height: 297mm; margin: 18px auto; padding: 18mm 20mm; background: #fff; box-shadow: 0 20px 50px rgba(15, 23, 42, .12); box-sizing: border-box; overflow: hidden; }
        .page-content { flex: 1 1 auto; min-height: 0; overflow: hidden; }
        .second-page { display: flex; flex-direction: column; }
        .second-page-content { flex: 0 0 auto; }
        .official-header { border-bottom: 3px solid #ffa117; padding-bottom: 16px; margin-bottom: 28px; }
        .header-brand-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .header-main { display: flex; align-items: center; gap: 8px; min-width: 0; }
        .logo { height: auto; object-fit: contain; }
        .reference { display: inline-block; border: 1px solid rgba(103, 145, 255, .28); background: rgba(103, 145, 255, .08); border-radius: 999px; padding: 7px 12px; font-size: 12px; color: #24426f; font-weight: 700; margin-top: 12px; }
        .date { text-align: right; color: #475467; margin: 16px 0 36px; }
        .subject { font-weight: 700; margin: 28px 0; }
        .content { line-height: 1.72; font-size: 15px; }
        .page:first-of-type .content { max-height: 130mm; overflow: hidden; }
        .second-page-content .content { max-height: 70mm; overflow: hidden; }
        .content p { margin: 0 0 13px; }
        .activation { display: flex; justify-content: space-between; gap: 14px; align-items: center; margin-top: 22px; padding: 9px 11px; border: 1px solid rgba(255, 161, 23, .42); background: #fffaf0; border-radius: 9px; font-size: 13px; }
        .activation a, .footer a { color: inherit; text-decoration: none; }
        .signature { margin: 42px 0 24px auto; max-width: 280px; text-align: right; }
        .signature-content { line-height: 1.6; margin-bottom: 22px; }
        .signature-image { max-width: 190px; max-height: 82px; object-fit: contain; margin-bottom: 10px; }
        .signature-space { height: 62px; border-bottom: 1px solid #98a2b3; margin: 0 0 12px auto; max-width: 220px; }
        .signature-name { font-weight: 800; }
        .signature-title { color: #667085; font-size: 13px; }
        .footer { display: grid; grid-template-columns: .72fr repeat(4, minmax(0, 1fr)); gap: 14px; align-items: start; margin-top: auto; padding-top: 14px; border-top: 1px solid #e5e7eb; }
        .footer-label { font-weight: 800; margin-bottom: 5px; color: #101828; min-height: 18px; line-height: 18px; }
        .muted { color: #667085; font-size: 13px; }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: #fff; }
            .toolbar { display: none; }
            .page { width: 210mm; height: 297mm; margin: 0; padding: 16mm 18mm; box-shadow: none; page-break-after: always; break-after: page; }
            .page:last-child { page-break-after: auto; }
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
        <div class="page-content">
            <header class="official-header">
                <div class="header-brand-row">
                    <div class="header-main">
                        @if ($logoUrl && $letter->logo_position !== 'none')
                            <img src="{{ $logoUrl }}" class="logo" style="width: {{ (int) $header['logo_width'] }}px;" alt="Logo">
                        @endif
                        <div>
                            <div style="{{ $textStyle($header['title']) }}">{{ $header['title']['text'] }}</div>
                            <div style="{{ $textStyle($header['subtitle']) }}">{{ $header['subtitle']['text'] }}</div>
                            <div style="{{ $textStyle($header['description']) }}">{{ $header['description']['text'] }}</div>
                        </div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span class="reference">N° {{ $letter->letter_number ?: 'UFC/MS/'.now()->format('Y').'/000001' }}</span>
                    <span class="reference">Code {{ $letter->activation_code }}</span>
                </div>
            </header>
            <div class="date">{{ $letter->issue_place ?: 'Abidjan' }}, le {{ ($letter->issue_date ?: now())->format('d/m/Y') }}</div>
            <p><strong>À l’attention de {{ $institutionAdmin->organization?->name ?: 'l’institution' }}</strong></p>
            <p class="subject">Objet : {{ $letter->letter_subject }}</p>
            <div class="content">{!! $letter->safeHtml($mainLetterContent) !!}</div>
        </div>
        @include('super-admin.institution-admins.partials.activation-letter-footer', ['footerClass' => 'footer'])
    </main>
    <main class="page second-page">
        <div class="second-page-content">
        @if (filled($secondPageLetterContent))
            <div class="content">{!! $letter->safeHtml($secondPageLetterContent) !!}</div>
        @endif
        <div class="signature">
            <div class="signature-content">{!! $letter->signatureHtml() !!}</div>
            @if ($letter->signatureUrl())
                <img src="{{ $letter->signatureUrl() }}" class="signature-image" alt="Signature">
            @else
                <div class="signature-space"></div>
            @endif
            <div class="signature-name">{{ $letter->signature_name ?: 'Le Coordonnateur du programme My-Signal' }}</div>
            <div class="signature-title">{{ $letter->signature_title ?: 'Union Fédérale des Consommateurs' }}</div>
        </div>
        <div class="activation">
            <div>
                <div class="muted">Code d’activation</div>
                <h2>{{ $letter->activation_code }}</h2>
                <div class="muted">Lien du formulaire</div>
                <a href="{{ $letter->activation_url }}">{{ $letter->activation_url }}</a>
            </div>
            <div id="activationQr"></div>
        </div>
        </div>
        @include('super-admin.institution-admins.partials.activation-letter-footer', ['footerClass' => 'footer'])
    </main>
    <script>
        window.addEventListener('load', function () {
            const container = document.getElementById('activationQr');
            if (container && window.QRCode) {
                new QRCode(container, {
                    text: @json($letter->activation_url),
                    width: 88,
                    height: 88,
                    correctLevel: QRCode.CorrectLevel.M,
                });
            }
        });
    </script>
</body>
</html>
