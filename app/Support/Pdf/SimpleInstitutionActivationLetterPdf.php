<?php

namespace App\Support\Pdf;

use App\Models\InstitutionActivationLetter;

class SimpleInstitutionActivationLetterPdf
{
    public function make(InstitutionActivationLetter $letter): string
    {
        $letter->loadMissing(['organization', 'institutionAdmin']);
        $organization = $letter->organization;
        $admin = $letter->institutionAdmin;
        $header = $letter->headerSettings();
        $footer = $letter->footerSettings();

        $firstPageInstructions = [
            ['fill', '1 0.63 0.09'],
            ['rect', 36, 748, 523, 58],
            ['fill'],
            ['text', 48, 786, 'F2', 16, '0.05 0.10 0.18', (string) ($header['title']['text'] ?? 'UNION FEDERALE DES CONSOMMATEURS')],
            ['text', 48, 770, 'F2', 14, '0.05 0.10 0.18', (string) ($header['subtitle']['text'] ?? "DE COTE D'IVOIRE")],
            ['text', 48, 754, 'F1', 9, '0.05 0.10 0.18', (string) ($header['description']['text'] ?? 'UFC - Cote d Ivoire - Association de defense des consommateurs')],
            ['text', 370, 784, 'F1', 10, '0.05 0.10 0.18', 'No '.$this->letterNumber($letter)],
            ['text', 370, 768, 'F1', 9, '0.05 0.10 0.18', 'Code '.$letter->activation_code],

            ['text', 410, 724, 'F1', 10, '0.42 0.48 0.54', ($letter->issue_place ?: 'Abidjan').', le '.($letter->issue_date ?: now())->format('d/m/Y')],
            ['text', 36, 720, 'F1', 10, '0.42 0.48 0.54', 'Institution'],
            ['text', 36, 702, 'F2', 14, '0.13 0.19 0.25', (string) ($organization?->name ?: '-')],
            ['text', 36, 684, 'F1', 11, '0.30 0.36 0.42', 'Admin de reference: '.($admin?->name ?: '-').' | '.($admin?->email ?: '-')],

            ['text', 36, 642, 'F1', 10, '0.42 0.48 0.54', 'Objet'],
            ['text', 36, 624, 'F2', 13, '0.13 0.19 0.25', (string) $letter->letter_subject],

        ];

        $contentLayout = $this->contentLayout((string) $letter->letter_content);
        $firstContentLines = $contentLayout['first_lines'];
        $secondContentLines = $contentLayout['second_lines'];
        $contentFontSize = $contentLayout['font_size'];
        $contentLineHeight = $contentLayout['line_height'];

        $y = 590;
        foreach ($firstContentLines as $line) {
            if ($y < $contentLayout['first_min_y']) {
                break;
            }

            $firstPageInstructions[] = ['text', 36, $y, 'F1', $contentFontSize, '0.16 0.22 0.29', $line];
            $y -= $contentLineHeight;
        }

        $firstPageAnnotations = [];
        $this->appendFooter($firstPageInstructions, $firstPageAnnotations, $footer);

        $secondPageInstructions = [];
        $secondPageY = 710;
        foreach ($secondContentLines as $line) {
            if ($secondPageY < $contentLayout['second_min_y']) {
                break;
            }

            $secondPageInstructions[] = ['text', 36, $secondPageY, 'F1', $contentFontSize, '0.16 0.22 0.29', $line];
            $secondPageY -= $contentLineHeight;
        }

        $signatureY = min(270, $secondPageY - 22);
        foreach ($this->wrapLines($this->plainContent((string) ($letter->signature_content ?: 'Pour l’Union Fédérale des Consommateurs')), 42) as $line) {
            if ($signatureY < 225) {
                break;
            }

            $secondPageInstructions[] = ['text', 330, $signatureY, 'F1', 10, '0.16 0.22 0.29', $line];
            $signatureY -= 15;
        }

        $secondPageInstructions[] = ['text', 330, $signatureY - 8, 'F2', 10, '0.13 0.19 0.25', (string) ($letter->signature_name ?: 'Le Coordonnateur du programme My-Signal')];
        $secondPageInstructions[] = ['text', 330, $signatureY - 24, 'F1', 9, '0.42 0.48 0.54', (string) ($letter->signature_title ?: 'Union Federale des Consommateurs')];

        $secondPageInstructions[] = ['fill', '1 0.98 0.94'];
        $secondPageInstructions[] = ['rect', 36, 155, 523, 52];
        $secondPageInstructions[] = ['fill'];
        $secondPageInstructions[] = ['text', 48, 190, 'F1', 9, '0.42 0.48 0.54', 'Code officiel'];
        $secondPageInstructions[] = ['text', 48, 171, 'F2', 14, '0.13 0.19 0.25', (string) $letter->activation_code];
        $secondPageInstructions[] = ['text', 220, 190, 'F1', 9, '0.42 0.48 0.54', 'Lien du formulaire'];
        $secondPageInstructions[] = ['text', 220, 171, 'F1', 9, '0.13 0.19 0.25', (string) $letter->activation_url];

        $secondPageAnnotations = [
            ['rect' => [220, 164, 559, 185], 'url' => (string) $letter->activation_url],
        ];

        $this->appendFooter($secondPageInstructions, $secondPageAnnotations, $footer);

        return $this->buildPdf([
            ['stream' => $this->buildContentStream($firstPageInstructions), 'annotations' => $firstPageAnnotations],
            ['stream' => $this->buildContentStream($secondPageInstructions), 'annotations' => $secondPageAnnotations],
        ]);
    }

    private function appendFooter(array &$instructions, array &$annotations, array $footer): void
    {
        $footerY = 120;
        $x = 36;
        foreach ([
            'logo' => ['label' => '', 'text' => ''],
            'address' => $footer['address'] ?? [],
            'phone' => $footer['phone'] ?? [],
            'email' => $footer['email'] ?? [],
            'website' => $footer['website'] ?? [],
        ] as $key => $column) {
            $instructions[] = ['text', $x, $footerY, 'F2', 8, '0.13 0.19 0.25', (string) ($column['label'] ?? '')];
            $text = mb_substr(str_replace(["\r", "\n"], ' / ', (string) ($column['text'] ?? '-')), 0, 24);
            $instructions[] = ['text', $x, $footerY - 12, 'F1', 7, '0.42 0.48 0.54', $text];

            $url = $this->footerLinkUrl((string) $key, (string) ($column['text'] ?? ''));
            if ($url !== null) {
                $annotations[] = ['rect' => [$x, $footerY - 18, $x + 95, $footerY - 4], 'url' => $url];
            }

            $x += 105;
        }
    }

    private function wrapLines(string $text, int $width): array
    {
        $lines = [];
        foreach (preg_split("/\r\n|\n|\r/", $text) ?: [] as $paragraph) {
            $wrapped = wordwrap(trim($paragraph), $width, "\n", false);
            foreach (explode("\n", $wrapped) as $line) {
                $lines[] = $line;
            }
            $lines[] = '';
        }

        return $lines;
    }

    private function plainContent(string $html): string
    {
        $html = preg_replace('/<\/(p|div|h2|h3|h4|li)>/i', "\n", $html) ?? $html;
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $html = preg_replace('/<li>/i', '- ', $html) ?? $html;

        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function contentLayout(string $html): array
    {
        $plainText = $this->plainContent($html);
        $layouts = [
            ['font_size' => 10, 'line_height' => 15, 'line_width' => 92, 'first_min_y' => 175, 'second_min_y' => 300],
            ['font_size' => 9, 'line_height' => 12, 'line_width' => 108, 'first_min_y' => 172, 'second_min_y' => 295],
            ['font_size' => 8, 'line_height' => 10, 'line_width' => 124, 'first_min_y' => 170, 'second_min_y' => 290],
            ['font_size' => 7, 'line_height' => 8, 'line_width' => 148, 'first_min_y' => 168, 'second_min_y' => 285],
        ];

        foreach ($layouts as $layout) {
            $lines = $this->wrapLines($plainText, $layout['line_width']);
            $firstPageCapacity = $this->lineCapacity(590, $layout['first_min_y'], $layout['line_height']);
            $secondPageCapacity = $this->lineCapacity(710, $layout['second_min_y'], $layout['line_height']);

            if (count($lines) <= ($firstPageCapacity + $secondPageCapacity)) {
                return $layout + [
                    'first_lines' => array_slice($lines, 0, $firstPageCapacity),
                    'second_lines' => array_slice($lines, $firstPageCapacity),
                ];
            }
        }

        $layout = end($layouts);
        $lines = $this->wrapLines($plainText, $layout['line_width']);
        $firstPageCapacity = $this->lineCapacity(590, $layout['first_min_y'], $layout['line_height']);
        $secondPageCapacity = $this->lineCapacity(710, $layout['second_min_y'], $layout['line_height']);
        $maxLines = $firstPageCapacity + $secondPageCapacity;

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = rtrim((string) $lines[$maxLines - 1], '. ').'...';
        }

        return $layout + [
            'first_lines' => array_slice($lines, 0, $firstPageCapacity),
            'second_lines' => array_slice($lines, $firstPageCapacity, $secondPageCapacity),
        ];
    }

    private function lineCapacity(int $startY, int $minY, int $lineHeight): int
    {
        return (int) floor(($startY - $minY) / $lineHeight) + 1;
    }

    private function footerLinkUrl(string $key, string $value): ?string
    {
        $firstLine = trim((string) preg_split('/\r\n|\r|\n/', $value)[0]);

        if ($firstLine === '') {
            return null;
        }

        return match ($key) {
            'phone' => 'tel:'.preg_replace('/\s+/', '', $firstLine),
            'email' => 'mailto:'.$firstLine,
            'website' => str_starts_with($firstLine, 'http') ? $firstLine : 'https://'.$firstLine,
            default => null,
        };
    }

    private function letterNumber(InstitutionActivationLetter $letter): string
    {
        return (string) ($letter->letter_number ?: 'UFC/MS/'.now()->format('Y').'/000001');
    }

    private function buildPdf(array $pages): string
    {
        $objects = [];

        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        $pageIds = [];
        foreach ($pages as $page) {
            $pageId = count($objects) + 1;
            $contentId = $pageId + 1;
            $annotationIds = [];

            foreach ($page['annotations'] ?? [] as $annotation) {
                $annotationIds[] = $contentId + count($annotationIds) + 1;
            }

            $annots = $annotationIds !== []
                ? ' /Annots ['.implode(' ', array_map(fn (int $id) => $id.' 0 R', $annotationIds)).']'
                : '';

            $stream = (string) ($page['stream'] ?? '');
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents '.$contentId.' 0 R'.$annots.' >>';
            $objects[] = "<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream";

            foreach ($page['annotations'] ?? [] as $annotation) {
                [$x1, $y1, $x2, $y2] = $annotation['rect'];
                $url = $this->escapeText((string) $annotation['url']);
                $objects[] = "<< /Type /Annot /Subtype /Link /Rect [{$x1} {$y1} {$x2} {$y2}] /Border [0 0 0] /A << /S /URI /URI ({$url}) >> >>";
            }

            $pageIds[] = $pageId.' 0 R';
        }

        $objects[1] = '<< /Type /Pages /Count '.count($pages).' /Kids ['.implode(' ', $pageIds).'] >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i])."\n";
        }

        return $pdf."trailer << /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }

    private function buildContentStream(array $instructions): string
    {
        $stream = [];

        foreach ($instructions as $instruction) {
            $type = array_shift($instruction);

            if ($type === 'fill') {
                $stream[] = $instruction === [] ? 'f' : $instruction[0].' rg';
            } elseif ($type === 'rect') {
                $stream[] = implode(' ', $instruction).' re';
            } elseif ($type === 'text') {
                [$x, $y, $font, $size, $color, $text] = $instruction;
                $stream[] = 'BT '.$color.' rg /'.$font.' '.$size.' Tf 1 0 0 1 '.$x.' '.$y.' Tm ('.$this->escapeText($text).') Tj ET';
            }
        }

        return implode("\n", $stream);
    }

    private function escapeText(string $text): string
    {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = $converted === false ? preg_replace('/[^\x20-\x7E]/', '', $text) ?? '' : $converted;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
