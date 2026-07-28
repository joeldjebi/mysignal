<?php

namespace App\Services\Reports;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use ZipArchive;

class ReportExportService
{
    public function download(array $report, string $format, ?array $analysis = null): Response
    {
        $filename = Str::slug($report['title'] ?? 'rapport').'-'.now()->format('Ymd-His');

        return match ($format) {
            'csv' => $this->response($this->csv($report, $analysis), 'text/csv; charset=UTF-8', $filename.'.csv'),
            'xls' => $this->response($this->xls($report, $analysis), 'application/vnd.ms-excel; charset=UTF-8', $filename.'.xls'),
            'pdf' => $this->response($this->pdf($report, $analysis), 'application/pdf', $filename.'.pdf'),
            'pptx' => $this->response($this->pptx($report, $analysis), 'application/vnd.openxmlformats-officedocument.presentationml.presentation', $filename.'.pptx'),
            default => $this->response($this->csv($report), 'text/csv; charset=UTF-8', $filename.'.csv'),
        };
    }

    private function response(string $content, string $type, string $filename): Response
    {
        return response($content, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function csv(array $report, ?array $analysis): string
    {
        $rows = $report['rows'] ?? [];
        $headers = $this->headers($rows);
        $lines = [
            "\xEF\xBB\xBF".implode(';', array_map([$this, 'csvCell'], ['Section', 'Libellé', 'Valeur'])),
            implode(';', array_map([$this, 'csvCell'], ['Rapport', 'Titre', (string) ($report['title'] ?? 'Rapport')])),
            implode(';', array_map([$this, 'csvCell'], ['Rapport', 'Généré le', now()->format('d/m/Y H:i')])),
            '',
            implode(';', array_map([$this, 'csvCell'], ['Indicateurs', 'Libellé', 'Valeur'])),
        ];

        foreach (($report['summary'] ?? []) as $label => $value) {
            $lines[] = implode(';', array_map([$this, 'csvCell'], ['Indicateurs', (string) $label, $this->formatValue($value)]));
        }

        if (filled($analysis['text'] ?? null)) {
            $lines[] = '';
            foreach (preg_split('/\r\n|\r|\n/', (string) $analysis['text']) as $line) {
                if (filled(trim($line))) {
                    $lines[] = implode(';', array_map([$this, 'csvCell'], ['Analyse', 'Commentaire', trim($line)]));
                }
            }
        }

        $lines[] = '';
        $lines[] = implode(';', array_map([$this, 'csvCell'], $headers));

        foreach ($rows as $row) {
            $lines[] = implode(';', array_map(fn ($header) => $this->csvCell($this->formatValue($row[$header] ?? '')), $headers));
        }

        return implode("\n", $lines);
    }

    private function xls(array $report, ?array $analysis): string
    {
        $rows = $report['rows'] ?? [];
        $headers = $this->headers($rows);
        $html = '<html><head><meta charset="UTF-8"><style>table{border-collapse:collapse}th,td{border:1px solid #999;padding:6px;text-align:left}th{background:#f2f2f2}</style></head><body>';
        $html .= '<h1>'.$this->escape($report['title'] ?? 'Rapport').'</h1>';
        $html .= '<table><tbody>';
        $html .= '<tr><th>Information</th><th>Valeur</th></tr>';
        $html .= '<tr><td>Rapport</td><td>'.$this->escape($report['title'] ?? 'Rapport').'</td></tr>';
        $html .= '<tr><td>Généré le</td><td>'.now()->format('d/m/Y H:i').'</td></tr>';
        $html .= '</tbody></table>';

        if (filled($analysis['text'] ?? null)) {
            $html .= '<h2>Analyse du rapport</h2><p>'.$this->escape($analysis['text']).'</p>';
        }

        $html .= '<h2>Indicateurs</h2><table><thead><tr><th>Libellé</th><th>Valeur</th></tr></thead><tbody>';

        foreach (($report['summary'] ?? []) as $label => $value) {
            $html .= '<tr><td>'.$this->escape((string) $label).'</td><td>'.$this->escape($this->formatValue($value)).'</td></tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<h2>Détails</h2><table><thead><tr>';

        foreach ($headers as $header) {
            $html .= '<th>'.$this->escape($header).'</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $html .= '<td>'.$this->escape((string) ($row[$header] ?? '')).'</td>';
            }
            $html .= '</tr>';
        }

        return $html.'</tbody></table></body></html>';
    }

    private function pdf(array $report, ?array $analysis): string
    {
        return $this->simplePdf($report, $analysis);
    }

    private function pptx(array $report, ?array $analysis): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'mysignal-report-');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->pptContentTypes());
        $zip->addFromString('_rels/.rels', $this->pptRels());
        $zip->addFromString('ppt/presentation.xml', $this->pptPresentation());
        $zip->addFromString('ppt/_rels/presentation.xml.rels', $this->pptPresentationRels());
        $zip->addFromString('ppt/slides/slide1.xml', $this->pptSlide($report['title'] ?? 'Rapport', $analysis['text'] ?? 'Rapport généré avec analyse locale.'));
        $zip->addFromString('ppt/slides/slide2.xml', $this->pptTableSlide('Indicateurs clés', ['Libellé', 'Valeur'], collect($report['summary'] ?? [])->map(fn ($value, $key) => [(string) $key, $this->formatValue($value)])->values()->all()));
        $zip->addFromString('ppt/slides/slide3.xml', $this->pptTableSlide('Détails', $this->headers($report['rows'] ?? []), array_slice($report['rows'] ?? [], 0, 8)));
        $zip->close();
        $content = file_get_contents($tmp) ?: '';
        @unlink($tmp);

        return $content;
    }

    private function simplePdf(array $report, ?array $analysis): string
    {
        $content = [];
        $y = 545;
        $headers = $this->headers($report['rows'] ?? []);

        $this->pdfLine($content, 35, $y, 16, (string) ($report['title'] ?? 'Rapport'), true);
        $y -= 24;
        $this->pdfLine($content, 35, $y, 10, 'Généré le : '.now()->format('d/m/Y H:i'));
        $y -= 24;

        if (filled($analysis['text'] ?? null)) {
            $this->pdfLine($content, 35, $y, 11, 'Analyse du rapport', true);
            $y -= 16;
            foreach ($this->wrap((string) $analysis['text'], 118) as $line) {
                $this->pdfLine($content, 35, $y, 9, $line);
                $y -= 13;
            }
            $y -= 8;
        }

        $this->pdfLine($content, 35, $y, 11, 'Indicateurs', true);
        $y -= 16;
        $this->pdfRow($content, $y, ['Libellé', 'Valeur'], [240, 130], true);
        $y -= 16;
        foreach (($report['summary'] ?? []) as $label => $value) {
            $this->pdfRow($content, $y, [(string) $label, $this->formatValue($value)], [240, 130]);
            $y -= 15;
            if ($y < 120) {
                break;
            }
        }
        $y -= 10;

        $this->pdfLine($content, 35, $y, 11, 'Détails', true);
        $y -= 16;
        $widths = $this->pdfColumnWidths(count($headers));
        $this->pdfRow($content, $y, $headers, $widths, true);
        $y -= 16;
        foreach (array_slice($report['rows'] ?? [], 0, 18) as $row) {
            $this->pdfRow($content, $y, array_map(fn ($header) => $this->formatValue($row[$header] ?? ''), $headers), $widths);
            $y -= 15;
            if ($y < 35) {
                break;
            }
        }

        $stream = implode("\n", $content);
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Count 1 /Kids [3 0 R] >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            "<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i])."\n";
        }

        return $pdf."trailer << /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }

    private function headers(array $rows): array
    {
        return collect($rows)->flatMap(fn ($row) => array_keys($row))->unique()->values()->all();
    }

    private function pdfLine(array &$content, int $x, int $y, int $size, string $text, bool $bold = false): void
    {
        $content[] = 'BT /F1 '.$size.' Tf '.$x.' '.$y.' Td ('.$this->pdfEscape($this->pdfText($text)).') Tj ET';
    }

    private function pdfRow(array &$content, int $y, array $values, array $widths, bool $header = false): void
    {
        $x = 35;
        foreach ($values as $index => $value) {
            $width = $widths[$index] ?? 90;
            $text = $this->truncate((string) $value, max(8, (int) floor($width / 5.3)));
            $this->pdfLine($content, $x, $y, $header ? 9 : 8, $text, $header);
            $x += $width;
        }
    }

    private function pdfColumnWidths(int $count): array
    {
        if ($count <= 0) {
            return [760];
        }

        $available = 760;
        $first = min(260, max(130, (int) floor($available * 0.32)));
        $remaining = max(1, $count - 1);
        $other = (int) floor(($available - $first) / $remaining);

        return array_values(array_pad([$first], $count, $other));
    }

    private function wrap(string $text, int $limit): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->flatMap(fn ($line) => str_split((string) $line, $limit))
            ->filter(fn ($line) => filled(trim((string) $line)))
            ->values()
            ->all();
    }

    private function csvCell(string $value): string
    {
        return '"'.str_replace('"', '""', $value).'"';
    }

    private function formatValue(mixed $value): string
    {
        return is_numeric($value) ? number_format((float) $value, 0, ',', ' ') : (string) $value;
    }

    private function escape(string $value): string
    {
        return e($value);
    }

    private function normalize(string $value): string
    {
        return str_replace(['œ', 'Œ', '€'], ['oe', 'OE', 'EUR'], iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $value) ?: $value);
    }

    private function pdfText(string $value): string
    {
        return $this->normalize($value);
    }

    private function pdfEscape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }

    private function truncate(string $value, int $limit): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?: $value);

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return mb_substr($value, 0, max(1, $limit - 1)).'…';
    }

    private function pptContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/><Override PartName="/ppt/slides/slide1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/><Override PartName="/ppt/slides/slide2.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/><Override PartName="/ppt/slides/slide3.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/></Types>';
    }

    private function pptRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/></Relationships>';
    }

    private function pptPresentation(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><p:presentation xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><p:sldIdLst><p:sldId id="256" r:id="rId1"/><p:sldId id="257" r:id="rId2"/><p:sldId id="258" r:id="rId3"/></p:sldIdLst><p:sldSz cx="12192000" cy="6858000" type="screen16x9"/></p:presentation>';
    }

    private function pptPresentationRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide2.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide3.xml"/></Relationships>';
    }

    private function pptTableSlide(string $title, array $headers, array $rows): string
    {
        $headers = array_values($headers);
        $rows = collect($rows)
            ->take(10)
            ->map(function ($row) use ($headers): array {
                if (! is_array($row)) {
                    return array_fill(0, count($headers), '');
                }

                if (array_is_list($row)) {
                    return array_map(fn ($value) => $this->formatValue($value), array_pad($row, count($headers), ''));
                }

                return array_map(fn ($header) => $this->formatValue($row[$header] ?? ''), $headers);
            })
            ->values()
            ->all();

        if ($headers === []) {
            $headers = ['Information'];
            $rows = [['Aucune donnée disponible.']];
        }

        $table = $this->pptTableXml($headers, $rows);

        return '<?xml version="1.0" encoding="UTF-8"?><p:sld xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><p:cSld><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr/><p:sp><p:nvSpPr><p:cNvPr id="2" name="Titre"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="600000" y="350000"/><a:ext cx="11000000" cy="700000"/></a:xfrm></p:spPr><p:txBody><a:bodyPr/><a:lstStyle/><a:p><a:r><a:rPr sz="3200" b="1"/><a:t>'.$this->xml($title).'</a:t></a:r></a:p></p:txBody></p:sp>'.$table.'</p:spTree></p:cSld></p:sld>';
    }

    private function pptTableXml(array $headers, array $rows): string
    {
        $columnCount = max(1, count($headers));
        $columnWidth = (int) floor(10500000 / $columnCount);
        $grid = collect(range(1, $columnCount))->map(fn () => '<a:gridCol w="'.$columnWidth.'"/>')->implode('');
        $allRows = array_merge([$headers], $rows);
        $body = collect($allRows)->map(function (array $row, int $index) use ($headers): string {
            $cells = collect($headers)->map(function ($header, int $cellIndex) use ($row, $index): string {
                $value = $row[$cellIndex] ?? '';
                $bold = $index === 0 ? ' b="1"' : '';

                return '<a:tc><a:txBody><a:bodyPr/><a:lstStyle/><a:p><a:r><a:rPr sz="1500"'.$bold.'/><a:t>'.$this->xml($this->truncate((string) $value, 32)).'</a:t></a:r></a:p></a:txBody><a:tcPr/></a:tc>';
            })->implode('');

            return '<a:tr h="360000">'.$cells.'</a:tr>';
        })->implode('');

        return '<p:graphicFrame><p:nvGraphicFramePr><p:cNvPr id="3" name="Tableau"/><p:cNvGraphicFramePr/><p:nvPr/></p:nvGraphicFramePr><p:xfrm><a:off x="750000" y="1200000"/><a:ext cx="10500000" cy="4700000"/></p:xfrm><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/table"><a:tbl><a:tblPr firstRow="1" bandRow="1"/><a:tblGrid>'.$grid.'</a:tblGrid>'.$body.'</a:tbl></a:graphicData></a:graphic></p:graphicFrame>';
    }

    private function pptSlide(string $title, string $body): string
    {
        $bodyLines = collect(preg_split('/\r\n|\r|\n/', $body))->take(9)->map(fn ($line) => '<a:p><a:r><a:t>'.$this->xml($line).'</a:t></a:r></a:p>')->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?><p:sld xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><p:cSld><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr/><p:sp><p:nvSpPr><p:cNvPr id="2" name="Titre"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="600000" y="500000"/><a:ext cx="11000000" cy="900000"/></a:xfrm></p:spPr><p:txBody><a:bodyPr/><a:lstStyle/><a:p><a:r><a:rPr sz="3600" b="1"/><a:t>'.$this->xml($title).'</a:t></a:r></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="3" name="Contenu"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="750000" y="1700000"/><a:ext cx="10500000" cy="4300000"/></a:xfrm></p:spPr><p:txBody><a:bodyPr/><a:lstStyle/>'.$bodyLines.'</p:txBody></p:sp></p:spTree></p:cSld></p:sld>';
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
