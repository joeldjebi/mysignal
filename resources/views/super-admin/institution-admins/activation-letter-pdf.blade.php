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
                ! empty($settings['bold']) ? 'font-weight: bold' : 'font-weight: normal',
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
        body { margin: 0; background: #fff; font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; }
        .page { height: 265mm; page-break-after: always; position: relative; overflow: hidden; }
        .page:last-child { page-break-after: auto; }
        .page-content { height: 226mm; overflow: hidden; }
        .second-page-content { height: 226mm; overflow: hidden; }
        .header { border-bottom: 3px solid #ffa117; padding-bottom: 11px; margin-bottom: 19px; }
        .header-table, .footer-table, .activation-table { border-collapse: collapse; width: 100%; }
        .brand-logo { max-height: 66px; object-fit: contain; margin-right: 8px; }
        .header-title { line-height: 1.18; }
        .reference-wrap { text-align: right; padding-left: 12px; vertical-align: top; }
        .reference { display: inline-block; border: 1px solid #cbd8fb; background: #f4f7ff; border-radius: 12px; padding: 5px 8px; margin-bottom: 4px; color: #24426f; font-size: 8px; font-weight: bold; }
        .date { text-align: right; color: #475467; margin: 12px 0 24px; }
        .subject { font-weight: bold; margin: 18px 0; }
        .content { line-height: 1.55; font-size: 10.5px; color: #1f2937; }
        .content p { margin: 0 0 8px; }
        .content ul, .content ol { margin: 0 0 8px 18px; padding: 0; }
        .signature { width: 44%; margin: 28px 0 15px auto; text-align: right; }
        .signature-content { line-height: 1.55; margin-bottom: 14px; }
        .signature-image { max-width: 150px; max-height: 58px; object-fit: contain; margin-bottom: 8px; }
        .signature-space { height: 42px; border-bottom: 1px solid #98a2b3; margin: 0 0 8px auto; width: 160px; }
        .signature-name { font-weight: bold; }
        .signature-title { color: #667085; font-size: 9px; }
        .activation { margin-top: 15px; border: 1px solid #f5c676; background: #fffaf0; border-radius: 7px; padding: 9px; }
        .activation-label, .footer-label { color: #101828; font-size: 8px; font-weight: bold; margin-bottom: 4px; }
        .activation-code { color: #111827; font-size: 16px; font-weight: bold; margin: 2px 0 7px; }
        .activation-link { color: #344054; font-size: 8px; text-decoration: none; word-break: break-all; }
        .qr-code { width: 70px; height: 70px; }
        .footer { position: absolute; left: 0; right: 0; bottom: 0; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .footer-logo { max-width: 56px; max-height: 44px; object-fit: contain; }
        .footer-cell { width: 20%; padding-right: 7px; vertical-align: top; word-break: break-word; }
        .footer-cell-logo { width: 12%; padding-right: 7px; vertical-align: top; }
        .footer-value { line-height: 1.35; }
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
                                <td style="vertical-align:middle; width:{{ (int) $header['logo_width'] }}px"><img src="{{ $logoUrl }}" class="brand-logo" style="width:{{ (int) $header['logo_width'] }}px" alt="Logo"></td>
                            @endif
                            <td class="header-title" style="vertical-align:middle">
                                <div style="{{ $textStyle($header['title']) }}">{{ $header['title']['text'] }}</div>
                                <div style="{{ $textStyle($header['subtitle']) }}">{{ $header['subtitle']['text'] }}</div>
                                <div style="{{ $textStyle($header['description']) }}">{{ $header['description']['text'] }}</div>
                            </td>
                        </tr></table>
                    </td>
                    <td class="reference-wrap" style="width:165px">
                        <span class="reference">N° {{ $letter->letter_number ?: 'UFC/MS/'.now()->format('Y').'/000001' }}</span><br>
                        <span class="reference">Code {{ $letter->activation_code }}</span>
                    </td>
                </tr></table>
            </header>
            <div class="date">{{ $letter->issue_place ?: 'Abidjan' }}, le {{ ($letter->issue_date ?: now())->format('d/m/Y') }}</div>
            <p><strong>À l’attention de {{ $institutionAdmin?->organization?->name ?: 'l’institution' }}</strong></p>
            <p class="subject">Objet : {{ $letter->letter_subject }}</p>
            <div class="content">{!! $letter->safeHtml($mainLetterContent) !!}</div>
        </div>
        <footer class="footer">
            <table class="footer-table"><tr>
                <td class="footer-cell-logo">@if($footerLogoUrl)<img src="{{ $footerLogoUrl }}" class="footer-logo" alt="Logo">@endif</td>
                @foreach($footerColumns as $key => $column)
                    @php($value = (string) ($column['text'] ?? ''))
                    <td class="footer-cell" style="{{ $textStyle($column) }}">
                        <div class="footer-label">{{ $column['label'] ?? '' }}</div>
                        <div class="footer-value">
                            @if($key === 'email' && filled($value))<a href="mailto:{{ trim(strtok($value, "\n")) }}">{!! nl2br(e($value)) !!}</a>
                            @elseif($key === 'website' && filled($value))<a href="{{ str_starts_with(trim($value), 'http') ? trim($value) : 'https://'.trim($value) }}">{!! nl2br(e($value)) !!}</a>
                            @elseif($key === 'phone' && filled($value))<a href="tel:{{ preg_replace('/\s+/', '', trim(strtok($value, "\n"))) }}">{!! nl2br(e($value)) !!}</a>
                            @else{!! nl2br(e($value)) !!}@endif
                        </div>
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
                <td class="footer-cell-logo">@if($footerLogoUrl)<img src="{{ $footerLogoUrl }}" class="footer-logo" alt="Logo">@endif</td>
                @foreach($footerColumns as $key => $column)
                    @php($value = (string) ($column['text'] ?? ''))
                    <td class="footer-cell" style="{{ $textStyle($column) }}">
                        <div class="footer-label">{{ $column['label'] ?? '' }}</div>
                        <div class="footer-value">{!! nl2br(e($value)) !!}</div>
                    </td>
                @endforeach
            </tr></table>
        </footer>
    </section>
</body>
</html>
