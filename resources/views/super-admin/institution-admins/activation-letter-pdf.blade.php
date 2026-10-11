<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
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
            $firstLineBudget = 24;
            $usedLines = 0;
            $lineWidth = 110;

            foreach ($blocks as $block) {
                $blockText = trim(html_entity_decode(strip_tags($block), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $blockLines = max(1, (int) ceil(mb_strlen($blockText) / $lineWidth)) + 1;

                if ($first !== '' && ($usedLines + $blockLines) > $firstLineBudget) {
                    $remainingLines = max(0, $firstLineBudget - $usedLines);
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

                    if ($firstWords !== []) {
                        $first .= '<p>'.e(implode(' ', $firstWords)).'</p>';
                    }
                    if ($secondWords !== []) {
                        $second .= '<p>'.e(implode(' ', $secondWords)).'</p>';
                    }
                    $usedLines = $firstLineBudget;
                } else {
                    $first .= $block;
                    $usedLines += $blockLines;
                }
            }

            return [trim($first), trim($second)];
        };
        [$mainLetterContent, $secondPageLetterContent] = $splitForTwoPages((string) $letter->letter_content);
        $logoUrl = $letter->logoUrl();
        $footerLogoUrl = $letter->footerLogoUrl();
        $footerColumns = [
            'address' => $footer['address'] ?? [],
            'phone' => $footer['phone'] ?? [],
            'email' => $footer['email'] ?? [],
            'website' => $footer['website'] ?? [],
        ];
    @endphp
    <style>
        @page { margin: 16mm 18mm 16mm 18mm; size: A4; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 14px; }
        .page { height: 265mm; page-break-after: always; position: relative; overflow: hidden; }
        .page:last-child { page-break-after: auto; }
        .page-content { height: 226mm; overflow: hidden; }
        .second-page-content { height: 226mm; overflow: hidden; }
        .header { border-bottom: 3px solid #ffa117; padding-bottom: 16px; margin-bottom: 28px; }
        .header-table, .footer-table, .activation-table { border-collapse: collapse; width: 100%; }
        .brand-logo { display: block; height: auto; margin-right: 8px; }
        .header-title { line-height: 1.18; }
        .reference-wrap { margin-top: 12px; text-align: right; white-space: nowrap; }
        .reference { display: inline-block; border: 1px solid #cbd8fb; background: #f4f7ff; border-radius: 999px; padding: 7px 12px; color: #24426f; font-size: 12px; font-weight: 800; }
        .reference + .reference { margin-left: 6px; }
        .date { text-align: right; color: #475467; margin: 16px 0 36px; }
        .subject { font-weight: 800; margin: 28px 0; }
        .content { line-height: 1.55; font-size: 14px; color: #111827; }
        .content p { margin: 0 0 10px; }
        .content ul, .content ol { margin: 0 0 10px 18px; padding: 0; }
        .signature { width: 44%; margin: 42px 0 24px auto; text-align: right; }
        .signature-content { line-height: 1.6; margin-bottom: 22px; }
        .signature-image { max-width: 190px; max-height: 82px; margin-bottom: 10px; }
        .signature-space { height: 62px; border-bottom: 1px solid #98a2b3; margin: 0 0 12px auto; width: 220px; }
        .signature-name { font-weight: 800; }
        .signature-title { color: #667085; font-size: 13px; }
        .activation { margin-top: 22px; border: 1px solid #f5c676; background: #fffaf0; border-radius: 9px; padding: 9px 11px; font-size: 13px; }
        .activation-label { color: #101828; font-size: 13px; font-weight: 800; margin-bottom: 5px; }
        .activation-code { color: #111827; font-size: 20px; font-weight: 800; margin: 2px 0 9px; }
        .activation-link { color: #344054; font-size: 13px; text-decoration: none; word-break: break-all; }
        .qr-code { width: 88px; height: 88px; }
        .footer { position: absolute; left: 0; right: 0; bottom: 0; height: 30mm; overflow: hidden; border-top: 1px solid #e5e7eb; padding-top: 14px; }
        .footer-logo { display: block; height: auto; }
        .footer-cell { width: 20%; padding-right: 7px; vertical-align: top; word-break: break-word; }
        .footer-cell-logo { width: 20%; padding-right: 7px; vertical-align: top; }
        .footer-column-table { border-collapse: collapse; width: 100%; }
        .footer-column-table td { padding: 0; vertical-align: top; }
        .footer-label { color: #101828; font-size: 13px; font-weight: 800; line-height: 1.1; margin-bottom: 2px; }
        .footer-value { line-height: 1.2; margin: 0; white-space: pre-line; }
        .footer-value a { color: inherit; text-decoration: none; }
    </style>
</head>
<body>
    <section class="page">
        <div class="page-content">
            <header class="header">
                <table class="header-table"><tr>
                    <td>
                        <table><tr>
                            @if ($logoUrl && $letter->logo_position !== 'none')
                                <td style="vertical-align:middle; width:{{ (int) $header['logo_width'] }}px"><img src="{{ $logoUrl }}" class="brand-logo" style="width:{{ (int) $header['logo_width'] }}px; height:auto" alt="Logo"></td>
                            @endif
                            <td class="header-title" style="vertical-align:middle">
                                <div style="{{ $textStyle($header['title']) }}">{{ $header['title']['text'] }}</div>
                                <div style="{{ $textStyle($header['subtitle']) }}">{{ $header['subtitle']['text'] }}</div>
                                <div style="{{ $textStyle($header['description']) }}">{{ $header['description']['text'] }}</div>
                            </td>
                        </tr></table>
                    </td>
                </tr></table>
                <div class="reference-wrap">
                    <span class="reference">N° {{ $letter->letter_number ?: 'UFC/MS/'.now()->format('Y').'/000001' }}</span>
                    <span class="reference">Code {{ $letter->activation_code }}</span>
                </div>
            </header>
            <div class="date">{{ $letter->issue_place ?: 'Abidjan' }}, le {{ ($letter->issue_date ?: now())->format('d/m/Y') }}</div>
            <p><strong>À l’attention de {{ $institutionAdmin?->organization?->name ?: 'l’institution' }}</strong></p>
            <p class="subject">Objet : {{ $letter->letter_subject }}</p>
            <div class="content">{!! $letter->safeHtml($mainLetterContent) !!}</div>
        </div>
        <footer class="footer">
            <table class="footer-table"><tr>
                <td class="footer-cell-logo">@if($footerLogoUrl)<img src="{{ $footerLogoUrl }}" class="footer-logo" style="width:{{ (int) ($footer['logo']['size'] ?? 72) }}px; height:auto" alt="Logo">@endif</td>
                @foreach($footerColumns as $key => $column)
                    @php($value = (string) ($column['text'] ?? ''))
                    <td class="footer-cell" style="{{ $textStyle($column) }}">
                        <table class="footer-column-table"><tr><td class="footer-label">{{ $column['label'] ?? '' }}</td></tr><tr><td class="footer-value">
                            @if($key === 'email' && filled($value))<a href="mailto:{{ trim(strtok($value, "\n")) }}">{!! nl2br(e($value)) !!}</a>
                            @elseif($key === 'website' && filled($value))<a href="{{ str_starts_with(trim($value), 'http') ? trim($value) : 'https://'.trim($value) }}">{!! nl2br(e($value)) !!}</a>
                            @elseif($key === 'phone' && filled($value))<a href="tel:{{ preg_replace('/\s+/', '', trim(strtok($value, "\n"))) }}">{!! nl2br(e($value)) !!}</a>
                            @else{!! nl2br(e($value)) !!}@endif
                        </td></tr></table>
                    </td>
                @endforeach
            </tr></table>
        </footer>
    </section>

    <section class="page">
        <div class="second-page-content">
            @if (filled($secondPageLetterContent))
                <div class="content">{!! $letter->safeHtml($secondPageLetterContent) !!}</div>
            @endif
            <div class="signature">
                <div class="signature-content">Cordialement,<br><strong>L’équipe My-Signal</strong></div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $letter->signature_name ?: 'Le Coordonnateur du programme My-Signal' }}</div>
                <div class="signature-title">{{ $letter->signature_title ?: 'Union Fédérale des Consommateurs' }}</div>
            </div>
            <div class="activation">
                <table class="activation-table"><tr>
                    <td style="vertical-align:top; padding-right:12px">
                        <div class="activation-label">Code d’activation</div>
                        <div class="activation-code">{{ $letter->activation_code }}</div>
                        <div class="activation-label">Lien du formulaire</div>
                        <a class="activation-link" href="{{ $letter->activation_url }}">{{ $letter->activation_url }}</a>
                    </td>
                    <td style="width:76px; text-align:right"><img src="{{ $qrCodeDataUri }}" class="qr-code" alt="QR code"></td>
                </tr></table>
            </div>
        </div>
        <footer class="footer">
            <table class="footer-table"><tr>
                <td class="footer-cell-logo">@if($footerLogoUrl)<img src="{{ $footerLogoUrl }}" class="footer-logo" style="width:{{ (int) ($footer['logo']['size'] ?? 72) }}px; height:auto" alt="Logo">@endif</td>
                @foreach($footerColumns as $key => $column)
                    @php($value = (string) ($column['text'] ?? ''))
                    <td class="footer-cell" style="{{ $textStyle($column) }}">
                        <table class="footer-column-table"><tr><td class="footer-label">{{ $column['label'] ?? '' }}</td></tr><tr><td class="footer-value">{!! nl2br(e($value)) !!}</td></tr></table>
                    </td>
                @endforeach
            </tr></table>
        </footer>
    </section>
</body>
</html>
