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

        $instructions = [
            ['fill', '1 0.63 0.09'],
            ['rect', 36, 748, 523, 58],
            ['fill'],
            ['text', 48, 785, 'F2', 18, '0.05 0.10 0.18', 'UNION FEDERALE DES CONSOMMATEURS'],
            ['text', 48, 766, 'F1', 11, '0.05 0.10 0.18', 'Programme My-Signal - Courrier officiel de designation du point focal'],
            ['text', 390, 784, 'F1', 10, '0.05 0.10 0.18', 'Ref. '.$letter->activation_code],

            ['text', 36, 720, 'F1', 10, '0.42 0.48 0.54', 'Institution'],
            ['text', 36, 702, 'F2', 14, '0.13 0.19 0.25', (string) ($organization?->name ?: '-')],
            ['text', 36, 684, 'F1', 11, '0.30 0.36 0.42', 'Admin de reference: '.($admin?->name ?: '-').' | '.($admin?->email ?: '-')],

            ['text', 36, 642, 'F1', 10, '0.42 0.48 0.54', 'Objet'],
            ['text', 36, 624, 'F2', 13, '0.13 0.19 0.25', (string) $letter->letter_subject],

            ['fill', '1 0.98 0.94'],
            ['rect', 36, 536, 523, 58],
            ['fill'],
            ['text', 48, 575, 'F1', 10, '0.42 0.48 0.54', 'Code officiel'],
            ['text', 48, 555, 'F2', 16, '0.13 0.19 0.25', (string) $letter->activation_code],
            ['text', 220, 575, 'F1', 10, '0.42 0.48 0.54', 'Lien du formulaire'],
            ['text', 220, 555, 'F1', 10, '0.13 0.19 0.25', (string) $letter->activation_url],
        ];

        $y = 500;
        foreach ($this->wrapLines($this->plainContent((string) $letter->letter_content), 92) as $line) {
            if ($y < 150) {
                break;
            }

            $instructions[] = ['text', 36, $y, 'F1', 10, '0.16 0.22 0.29', $line];
            $y -= 15;
        }

        $signatureY = max(90, $y - 25);
        foreach ($this->wrapLines($this->plainContent((string) ($letter->signature_content ?: 'Pour l’Union Fédérale des Consommateurs')), 42) as $line) {
            if ($signatureY < 72) {
                break;
            }

            $instructions[] = ['text', 330, $signatureY, 'F1', 10, '0.16 0.22 0.29', $line];
            $signatureY -= 15;
        }

        $instructions[] = ['text', 330, $signatureY - 8, 'F2', 10, '0.13 0.19 0.25', (string) ($letter->signature_name ?: 'Le Coordonnateur du programme My-Signal')];
        $instructions[] = ['text', 330, $signatureY - 24, 'F1', 9, '0.42 0.48 0.54', (string) ($letter->signature_title ?: 'Union Federale des Consommateurs')];
        $instructions[] = ['text', 36, 52, 'F1', 9, '0.42 0.48 0.54', 'Document officiel genere par My-Signal pour la designation du point focal institutionnel.'];

        return $this->buildPdf($this->buildContentStream($instructions));
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

    private function buildPdf(string $stream): string
    {
        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Count 1 /Kids [3 0 R] >>';
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>';
        $objects[] = "<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream";
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

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
