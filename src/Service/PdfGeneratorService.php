<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGeneratorService
{
    public function generateInvoicePdf(array $invoiceData): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true); // utile si tu charges des images/logo
        $dompdf = new Dompdf($options);

        // HTML du template facture
        $html = '
        <html>
        <head>
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #2c3e50; }
                .info { margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { text-align: right; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Oroya.fr</h1>
                <p>Facture N° '.$invoiceData['invoice_number'].'</p>
            </div>

            <div class="info">
                <strong>Vendeur :</strong><br>
                Oroya.fr<br>
                12 rue des Vins<br>
                75000 Paris<br><br>

                <strong>Client :</strong><br>
                '.$invoiceData['customer_name'].'<br>
                '.$invoiceData['customer_address'].'<br>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>PU HT</th>
                        <th>Total HT</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($invoiceData['items'] as $item) {
            $html .= '
                <tr>
                    <td>'.$item['name'].'</td>
                    <td>'.$item['quantity'].'</td>
                    <td>'.number_format($item['price'], 2).' €</td>
                    <td>'.number_format($item['quantity'] * $item['price'], 2).' €</td>
                </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <p class="total">Total HT : '.number_format($invoiceData['total_ht'], 2).' €</p>
            <p class="total">TVA (20%) : '.number_format($invoiceData['tva'], 2).' €</p>
            <p class="total">Total TTC : '.number_format($invoiceData['total_ttc'], 2).' €</p>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
