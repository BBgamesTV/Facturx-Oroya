<?php
namespace App\Controller;

use App\Service\PdfGeneratorService;
use App\Service\FacturxService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/facturx', name: 'generate_invoice_facturx')]
    public function generateInvoice(
        PdfGeneratorService $pdfGeneratorService,
        FacturxService $facturxService
    ): Response {
        // Données fictives
        $invoiceData = [
            'invoice_number' => '2025-001',
            'customer_name' => 'Jean Dupont',
            'customer_address' => '45 rue des Champs, 69000 Lyon',
            'items' => [
                ['name' => 'Bouteille de vin rouge', 'quantity' => 2, 'price' => 15.50],
                ['name' => 'Champagne Brut', 'quantity' => 1, 'price' => 35.00],
            ],
            'total_ht' => 66.00,
            'tva' => 13.20,
            'total_ttc' => 79.20,
        ];

        // 1. Générer le PDF de la facture
        $pdfContent = $pdfGeneratorService->generateInvoicePdf($invoiceData);

        // 2. Fusionner avec ton XML Factur-X
        $xmlPath = $this->getParameter('kernel.project_dir') . '/assets/CII_example2.xml';
        $outputPath = $this->getParameter('kernel.project_dir') . '/var/invoices/facture_oroya.pdf';

        $facturxService->generateFacturxPdf($xmlPath, $outputPath, $pdfContent);

        return new Response("Facture Factur-X générée : $outputPath");
    }
}
