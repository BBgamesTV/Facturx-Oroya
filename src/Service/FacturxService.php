<?php
namespace App\Service;

use Atgp\FacturX\Writer;

class FacturxService
{
    public function generateFacturxPdf(string $xmlPath, string $outputPath, string $pdfContent): void
    {
        $xmlContent = file_get_contents($xmlPath);

        // Ici on utilise Writer
        $writer = new Writer();

        // generate(string $pdfInvoice, string $xml, ?string $profile = null, bool $validateXSD = true, array $additionalAttachments = [], bool $addLogo = false, string $relationship = 'Data')
        $facturxPdf = $writer->generate(
            $pdfContent,      // ton PDF (string binaire)
            $xmlContent,      // ton XML Factur-X
            null,             // profil (auto-détection si null)
            true,             // validation XSD
            [],               // pièces jointes additionnelles
            false,            // ajouter logo Factur-X en haut de la 1ère page
            'Data'            // relation
        );

        file_put_contents($outputPath, $facturxPdf);
    }
}
