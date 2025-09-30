<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGeneratorService
{
    public function generateInvoicePdfFromHtml(string $html, string $outputPath): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true); // pour les images/logo
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($outputPath, $dompdf->output());
    }
}
