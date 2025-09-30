<?php
namespace App\Service;

use Atgp\FacturX\Writer;

class FacturxService
{
    public function generateFacturxPdf(string $xmlPath, string $outputPath, string $pdfContent): void
    {
        $xmlContent = file_get_contents($xmlPath);
        
        $writer = new Writer();

        $facturxPdf = $writer->generate(
            $pdfContent,      
            $xmlContent,      
            "wl",             
            false,             
            [],               
            false,            
            'Data'            
        );

        file_put_contents($outputPath, $facturxPdf);
    }
}
