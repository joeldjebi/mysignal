<?php

namespace App\Services;

use App\Models\InstitutionActivationLetter;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class InstitutionActivationLetterPdfService
{
    public function make(InstitutionActivationLetter $letter): string
    {
        $letter->loadMissing(['organization', 'institutionAdmin']);

        $qrCode = (new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: (string) $letter->activation_url,
            size: 180,
            margin: 0,
        ))->build();

        return Pdf::loadView('super-admin.institution-admins.activation-letter-pdf', [
            'institutionAdmin' => $letter->institutionAdmin,
            'letter' => $letter,
            'qrCodeDataUri' => $qrCode->getDataUri(),
        ])
            ->setPaper('a4')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ])
            ->output();
    }
}
