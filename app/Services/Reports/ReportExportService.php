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
            'csv' => $this->response($this->csv($report), 'text/csv; charset=UTF-8', $filename.'.csv'),
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

    private function csv(array $report): string
    {
        $rows = $report['rows'] ?? [];
        $headers = $this->headers($rows);
        $lines = ["\xEF\xBB\xBF".implode(';', array_map([$this, 'csvCell'], $headers))];

        foreach ($rows as $row) {
            $lines[] = implode(';', array_map(fn ($header) => $this->csvCell((string) ($row[$header] ?? '')), $headers));
        }

        return implode("\n", $lines);
    }

    private function xls(array $report, ?array $analysis): string
    {
        $rows = $report['rows'] ?? [];
        $headers = $this->headers($rows);
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<h1>'.$this->escape($report['title'] ?? 'Rapport').'</h1>';
        $html .= '<p>'.$this->escape($analysis['text'] ?? '').'</p>';
        $html .= '<table border="1"><thead><tr>';

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
        $lines = [
            'Rapport: '.($report['title'] ?? 'Rapport'),
            'Généré le: '.now()->format('d/m/Y H:i'),
            '',
            'Synthèse:',
            $analysis['text'] ?? '',
            '',
            'Indicateurs:',
        ];

        foreach (($report['summary'] ?? []) as $label => $value) {
            $lines[] = $label.' : '.$this->formatValue($value);
        }

        $lines[] = '';
        $lines[] = 'Détails:';

        foreach (array_slice($report['rows'] ?? [], 0, 28) as $row) {
            $lines[] = collect($row)->map(fn ($value, $key) => $key.'='.$this->formatValue($value))->implode(' | ');
        }

        return $this->simplePdf($lines);
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
        $zip->addFromString('ppt/slides/slide1.xml', $this->pptSlide($report['title'] ?? 'Rapport', $analysis['text'] ?? 'Synthèse indisponible.'));
        $zip->addFromString('ppt/slides/slide2.xml', $this->pptSlide('Indicateurs clés', collect($report['summary'] ?? [])->map(fn ($value, $key) => $key.' : '.$this->formatValue($value))->implode("\n")));
        $zip->close();
        $content = file_get_contents($tmp) ?: '';
        @unlink($tmp);

        return $content;
    }

    private function simplePdf(array $lines): string
    {
        $content = [];
        $y = 790;
        foreach ($lines as $line) {
            foreach (str_split($this->normalize((string) $line), 92) as $part) {
                $content[] = 'BT /F1 10 Tf 45 '.$y.' Td ('.$this->pdfEscape($part).') Tj ET';
                $y -= 18;
                if ($y < 50) {
                    break 2;
                }
            }
        }

        $stream = implode("\n", $content);
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Count 1 /Kids [3 0 R] >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            "<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
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

    private function pdfEscape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }

    private function pptContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/><Override PartName="/ppt/slides/slide1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/><Override PartName="/ppt/slides/slide2.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/></Types>';
    }

    private function pptRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/></Relationships>';
    }

    private function pptPresentation(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><p:presentation xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><p:sldIdLst><p:sldId id="256" r:id="rId1"/><p:sldId id="257" r:id="rId2"/></p:sldIdLst><p:sldSz cx="12192000" cy="6858000" type="screen16x9"/></p:presentation>';
    }

    private function pptPresentationRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide2.xml"/></Relationships>';
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
