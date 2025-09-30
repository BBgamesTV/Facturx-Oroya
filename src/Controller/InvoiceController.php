<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\InvoiceRepository;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Service\InvoiceXmlGenerator;
use App\Service\FacturxService;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/new', name: 'invoice_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('invoice/new.html.twig');
    }

    #[Route('/', name: 'invoice_index')]
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        $invoices = $invoiceRepository->findAll();

        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoices,
        ]);
    }

    #[Route('/show/{id}', name: 'invoice_show')]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/edit/{id}', name: 'invoice_edit')]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $invoice->setInvoiceNumber($request->request->get('invoiceNumber'));
            $invoice->setIssueDate(new \DateTime($request->request->get('issueDate')));
            $invoice->setCurrency($request->request->get('currency'));
            $invoice->setPaymentReference($request->request->get('paymentReference'));
            $invoice->setNote($request->request->get('note'));

            $em->flush();

            $this->addFlash('success', 'Facture mise à jour.');
            return $this->redirectToRoute('invoice_index');
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/delete/{id}', name: 'invoice_delete')]
    public function delete(Invoice $invoice, EntityManagerInterface $em): Response
    {
        $em->remove($invoice);
        $em->flush();

        $this->addFlash('success', 'Facture supprimée.');
        return $this->redirectToRoute('invoice_index');
    }


    #[Route('/invoice/create-facturx', name: 'invoice_create_facturx', methods: ['POST'])]
    public function createFacturx(
        Request $request,
        EntityManagerInterface $em,
        InvoiceXmlGenerator $xmlGenerator,
        FacturxService $facturxService,
        PdfGeneratorService $pdfGeneratorService
    ): Response {
        $data = $request->request->all();

        // --- Création de la facture ---
        $invoice = new Invoice();
        $invoice->setInvoiceNumber($data['invoiceNumber'] ?? 'INV-' . time());
        $invoice->setIssueDate(new \DateTime($data['issueDate'] ?? 'now'));
        $invoice->setCurrency($data['currency'] ?? 'NOK');
        $invoice->setPaymentReference($data['paymentReference'] ?? null);
        $invoice->setNote($data['note'] ?? null);

        // Helper pour nettoyer
        function cleanArray(array $data): array
        {
            return array_filter($data, fn($v) => $v !== null && $v !== '');
        }

        $invoice->setSeller(array_filter($data['seller'] ?? [], fn($v) => $v !== null && $v !== ''));

        $invoice->setBuyer(array_filter($data['buyer'] ?? [], fn($v) => $v !== null && $v !== ''));

        // --- Création des lignes ---
        if (!empty($data['lines'])) {
            foreach ($data['lines'] as $lineData) {
                $line = new InvoiceLine();
                $line->setLineId($lineData['lineId'] ?? '');
                $line->setProductName($lineData['productName'] ?? '');
                $line->setSellerId($lineData['sellerId'] ?? '');
                $line->setGlobalId($lineData['globalId'] ?? '');
                $line->setDescription($lineData['description'] ?? '');
                $line->setQuantity(floatval($lineData['quantity'] ?? 1));
                $line->setUnit($lineData['unit'] ?? 'NAR');
                $line->setGrossPrice(isset($lineData['grossPrice']) ? floatval($lineData['grossPrice']) : 0);
                $line->setNetPrice(isset($lineData['netPrice']) ? floatval($lineData['netPrice']) : 0);
                $line->setTaxRate(isset($lineData['taxRate']) ? floatval($lineData['taxRate']) : 0);
                $line->setTaxCategory($lineData['taxCategory'] ?? 'S');
                $line->setNote($lineData['note'] ?? null);

                // Allowances / Charges
                if (!empty($lineData['allowances'])) {
                    foreach ($lineData['allowances'] as $allowance) {
                        $line->addAllowance(
                            $allowance['charge'] ?? false,
                            isset($allowance['amount']) ? floatval($allowance['amount']) : 0.0,
                            $allowance['reason'] ?? ''
                        );
                    }
                }

                // Lier la ligne à la facture
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

        // --- Génération PDF HTML avec Twig ---
        $pdfHtml = $this->renderView('invoice/pdf.html.twig', ['invoice' => $invoice]);

        // --- Génération PDF avec Dompdf ---
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfHtml);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // --- Sauvegarde PDF
        $pdfContent = $dompdf->output();
        $pdfFilePath = $folder . '/facture_' . $invoice->getInvoiceNumber() . '.pdf';
        file_put_contents($pdfFilePath, $pdfContent);

        // --- Vérification
        if (!file_exists($pdfFilePath) || filesize($pdfFilePath) === 0) {
            throw new \Exception("Le PDF n’a pas été généré correctement !");
        }

        // --- Fusion XML + PDF pour Factur-X
        $facturxService->generateFacturxPdf($xmlFilePath, $pdfFilePath, $pdfContent);



        $this->addFlash('success', 'Facture Factur-X créée avec succès !');

        return $this->redirectToRoute('invoice_new');
    }
}
