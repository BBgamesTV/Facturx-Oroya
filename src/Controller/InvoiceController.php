<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Service\InvoiceXmlGenerator;
use App\Service\FacturxService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/invoice/new', name: 'invoice_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('invoice/new.html.twig');
    }

    #[Route('/invoice/create-facturx', name: 'invoice_create_facturx', methods: ['POST'])]
    public function createFacturx(
        Request $request,
        EntityManagerInterface $em,
        InvoiceXmlGenerator $xmlGenerator,
        FacturxService $facturxService
    ): Response {
        $data = $request->request->all();

        // --- Création de la facture ---
        $invoice = new Invoice();
        $invoice->setInvoiceNumber($data['invoiceNumber'] ?? 'INV-' . time());
        $invoice->setIssueDate(new \DateTime($data['issueDate'] ?? 'now'));
        $invoice->setCurrency($data['currency'] ?? 'NOK');
        $invoice->setPaymentReference($data['paymentReference'] ?? null);

        // --- Création des lignes ---
        if (!empty($data['lines'])) {
            foreach ($data['lines'] as $lineData) {
                $line = new InvoiceLine();
                $line->setLineId($lineData['lineId'] ?? '');
                $line->setProductName($lineData['productName'] ?? '');
                $line->setSellerId($lineData['sellerId'] ?? '');
                $line->setGlobalId($lineData['globalId'] ?? '');
                $line->setDescription($lineData['description'] ?? null);
                $line->setQuantity(floatval($lineData['quantity'] ?? 1));
                $line->setUnit($lineData['unit'] ?? 'NAR');
                $line->setGrossPrice(isset($lineData['grossPrice']) ? floatval($lineData['grossPrice']) : null);
                $line->setNetPrice(isset($lineData['netPrice']) ? floatval($lineData['netPrice']) : null);
                $line->setTaxRate(isset($lineData['taxRate']) ? floatval($lineData['taxRate']) : null);
                $line->setTaxCategory($lineData['taxCategory'] ?? 'S');
                $line->setNote($lineData['note'] ?? null);

                if (!empty($lineData['allowances'])) {
                    foreach ($lineData['allowances'] as $allowance) {
                        $line->addAllowance(
                            $allowance['charge'] ?? false,
                            isset($allowance['amount']) ? floatval($allowance['amount']) : 0.0,
                            $allowance['reason'] ?? ''
                        );
                    }
                }

                $invoice->addLine($line);
            }
        }

        // --- Persistance en base ---
        $em->persist($invoice);
        $em->flush();

        // --- Création du dossier factures ---
        $folder = $this->getParameter('kernel.project_dir') . '/factures';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        // --- Génération XML Factur-X ---
        $xmlFilePath = $folder . '/facture_' . $invoice->getInvoiceNumber() . '.xml';
        $xmlString = $xmlGenerator->generateXml($invoice);
        file_put_contents($xmlFilePath, $xmlString);

        // --- Génération PDF HTML avec Dompdf ---
        $pdfHtml = $this->renderView('invoice/pdf.html.twig', [
            'invoice' => $invoice
        ]);

        // --- Génération PDF Factur-X final ---
        $pdfFilePath = $folder . '/facture_' . $invoice->getInvoiceNumber() . '.pdf';
        $facturxService->generateFacturxPdf($xmlFilePath, $pdfFilePath, $pdfHtml);

        $this->addFlash('success', 'Facture Factur-X créée avec succès !');

        return $this->redirectToRoute('invoice_new');
    }
}
