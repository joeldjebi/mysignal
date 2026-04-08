<?php

namespace App\Support\Pdf;

use App\Models\Payment;
use App\Models\PublicUser;

class SimplePaymentReceiptPdf
{
    public function make(Payment $payment, PublicUser $publicUser): string
    {
        $lines = [
            ['fill', '0.06 0.16 0.24'],
            ['rect', 36, 744, 523, 62],
            ['fill'],
            ['text', 48, 785, 'F2', 24, '1 1 1', 'RECU DE PAIEMENT'],
            ['text', 48, 764, 'F1', 11, '1 1 1', 'Justificatif associe a un signalement public'],
            ['text', 400, 785, 'F1', 10, '1 1 1', 'Reference'],
            ['text', 400, 766, 'F2', 14, '1 1 1', (string) $payment->reference],

            ['text', 36, 714, 'F1', 10, '0.42 0.48 0.54', 'Usager'],
            ['text', 36, 696, 'F2', 14, '0.13 0.19 0.25', $this->userName($publicUser)],
            ['text', 36, 678, 'F1', 11, '0.30 0.36 0.42', (string) ($publicUser->phone ?: '-')],

            ['text', 300, 714, 'F1', 10, '0.42 0.48 0.54', 'Montant'],
            ['text', 300, 696, 'F2', 18, '0.13 0.19 0.25', $this->formatAmount($payment)],
            ['text', 300, 678, 'F1', 11, '0.30 0.36 0.42', 'Statut: '.strtoupper((string) $payment->status)],

            ['stroke', '0.87 0.90 0.93'],
            ['line', 36, 650, 559, 650],

            ['text', 36, 625, 'F1', 10, '0.42 0.48 0.54', 'Signalement'],
            ['text', 36, 607, 'F2', 13, '0.13 0.19 0.25', (string) ($payment->incidentReport?->reference ?: '-')],
            ['text', 36, 590, 'F1', 11, '0.30 0.36 0.42', $this->incidentLabel($payment)],

            ['text', 300, 625, 'F1', 10, '0.42 0.48 0.54', 'Canal de paiement'],
            ['text', 300, 607, 'F2', 13, '0.13 0.19 0.25', strtoupper((string) ($payment->provider ?: '-'))],
            ['text', 300, 590, 'F1', 11, '0.30 0.36 0.42', (string) ($payment->provider_reference ?: 'Reference fournisseur indisponible')],

            ['text', 36, 548, 'F1', 10, '0.42 0.48 0.54', 'Date d initiation'],
            ['text', 36, 530, 'F2', 12, '0.13 0.19 0.25', $this->formatDate($payment->initiated_at?->format('d/m/Y H:i'))],

            ['text', 300, 548, 'F1', 10, '0.42 0.48 0.54', 'Date de confirmation'],
            ['text', 300, 530, 'F2', 12, '0.13 0.19 0.25', $this->formatDate($payment->paid_at?->format('d/m/Y H:i'))],

            ['fill', '0.94 0.96 0.98'],
            ['rect', 36, 438, 523, 66],
            ['fill'],
            ['text', 48, 483, 'F1', 10, '0.42 0.48 0.54', 'Details de facturation'],
            ['text', 48, 462, 'F2', 13, '0.13 0.19 0.25', (string) ($payment->pricingRule?->label ?: 'Paiement signalement public')],
            ['text', 48, 444, 'F1', 11, '0.30 0.36 0.42', 'Compteur: '.($payment->incidentReport?->meter?->meter_number ?: '-').'   |   Reseau: '.($payment->incidentReport?->network_type ?: '-')],

            ['stroke', '0.87 0.90 0.93'],
            ['line', 36, 408, 559, 408],
            ['text', 36, 384, 'F1', 10, '0.42 0.48 0.54', 'Document genere le'],
            ['text', 36, 366, 'F2', 12, '0.13 0.19 0.25', now()->format('d/m/Y H:i')],
            ['text', 36, 332, 'F1', 11, '0.30 0.36 0.42', 'ACEPEN ALERTE - Recu numerique de paiement'],
        ];

        $stream = $this->buildContentStream($lines);

        return $this->buildPdf($stream);
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

        $pdf .= "trailer << /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    private function buildContentStream(array $instructions): string
    {
        $stream = [];

        foreach ($instructions as $instruction) {
            $type = array_shift($instruction);

            switch ($type) {
                case 'fill':
                    if ($instruction !== []) {
                        $stream[] = $instruction[0].' rg';
                    } else {
                        $stream[] = 'f';
                    }
                    break;
                case 'stroke':
                    $stream[] = $instruction[0].' RG';
                    break;
                case 'rect':
                    $stream[] = implode(' ', $instruction).' re';
                    break;
                case 'line':
                    [$x1, $y1, $x2, $y2] = $instruction;
                    $stream[] = $x1.' '.$y1.' m '.$x2.' '.$y2.' l S';
                    break;
                case 'text':
                    [$x, $y, $font, $size, $color, $text] = $instruction;
                    $stream[] = 'BT '.$color.' rg /'.$font.' '.$size.' Tf 1 0 0 1 '.$x.' '.$y.' Tm ('.$this->escapeText($text).') Tj ET';
                    break;
            }
        }

        return implode("\n", $stream);
    }

    private function escapeText(string $text): string
    {
        $text = $this->normalizeText($text);

        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );
    }

    private function normalizeText(string $text): string
    {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        return $converted === false ? preg_replace('/[^\x20-\x7E]/', '', $text) ?? '' : $converted;
    }

    private function userName(PublicUser $publicUser): string
    {
        $name = trim(($publicUser->first_name ?? '').' '.($publicUser->last_name ?? ''));

        return $name !== '' ? $name : 'Usager public';
    }

    private function formatAmount(Payment $payment): string
    {
        return number_format((float) $payment->amount, 0, ',', ' ').' '.$payment->currency;
    }

    private function formatDate(?string $value): string
    {
        return $value ?: '-';
    }

    private function incidentLabel(Payment $payment): string
    {
        $parts = array_filter([
            $payment->incidentReport?->signal_code,
            $payment->incidentReport?->signal_label,
        ]);

        return $parts !== [] ? implode(' - ', $parts) : '-';
    }
}
