<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use DOMDocument;

class InvoiceXmlGenerator
{
    public function generateXml(Invoice $invoice): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // --- Root CrossIndustryInvoice with namespaces ---
        $root = $doc->createElementNS(
            'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
            'rsm:CrossIndustryInvoice'
        );
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:udt', 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100');
        $doc->appendChild($root);

        // --- ExchangedDocumentContext ---
        $context = $doc->createElement('rsm:ExchangedDocumentContext');
        $guideline = $doc->createElement('ram:GuidelineSpecifiedDocumentContextParameter');
        $guideline->appendChild($doc->createElement('ram:ID', 'urn:cen.eu:en16931:2017'));
        $context->appendChild($guideline);
        $root->appendChild($context);

        // --- ExchangedDocument ---
        $exDoc = $doc->createElement('rsm:ExchangedDocument');
        $exDoc->appendChild($doc->createElement('ram:ID', $invoice->getInvoiceNumber()));
        $exDoc->appendChild($doc->createElement('ram:TypeCode', '380')); // Facture commerciale
        $issueDate = $doc->createElement('ram:IssueDateTime');
        $dt = $doc->createElement('udt:DateTimeString', $invoice->getIssueDate()->format('Ymd'));
        $dt->setAttribute('format', '102');
        $issueDate->appendChild($dt);
        $exDoc->appendChild($issueDate);
        $root->appendChild($exDoc);

        // --- SupplyChainTradeTransaction ---
        $trade = $doc->createElement('rsm:SupplyChainTradeTransaction');
        $root->appendChild($trade);

        // --- Header Agreement ---
        $headerAgreement = $doc->createElement('ram:ApplicableHeaderTradeAgreement');

        // Seller
        $seller = $doc->createElement('ram:SellerTradeParty');
        $sellerData = $invoice->getSeller() ?? [];
        $seller->appendChild($doc->createElement('ram:Name', $sellerData['name'] ?? ''));
        $sellerAddress = $doc->createElement('ram:PostalTradeAddress');
        $sellerAddress->appendChild($doc->createElement('ram:PostcodeCode', $sellerData['zip'] ?? ''));
        $sellerAddress->appendChild($doc->createElement('ram:LineOne', $sellerData['address'] ?? ''));
        $sellerAddress->appendChild($doc->createElement('ram:CityName', $sellerData['city'] ?? ''));
        $sellerAddress->appendChild($doc->createElement('ram:CountryID', $sellerData['country'] ?? ''));
        $seller->appendChild($sellerAddress);
        $headerAgreement->appendChild($seller);

        // Buyer
        $buyerData = $invoice->getBuyer() ?? [];
        if (!empty($buyerData)) {
            $buyer = $doc->createElement('ram:BuyerTradeParty');
            $buyer->appendChild($doc->createElement('ram:Name', $buyerData['name'] ?? ''));
            $buyerAddress = $doc->createElement('ram:PostalTradeAddress');
            $buyerAddress->appendChild($doc->createElement('ram:PostcodeCode', $buyerData['zip'] ?? ''));
            $buyerAddress->appendChild($doc->createElement('ram:LineOne', $buyerData['address'] ?? ''));
            $buyerAddress->appendChild($doc->createElement('ram:CityName', $buyerData['city'] ?? ''));
            $buyerAddress->appendChild($doc->createElement('ram:CountryID', $buyerData['country'] ?? ''));
            $buyer->appendChild($buyerAddress);
            $headerAgreement->appendChild($buyer);
        }
        $trade->appendChild($headerAgreement);

        // --- Header Settlement (totaux globaux) ---
        $headerSettlement = $doc->createElement('ram:ApplicableHeaderTradeSettlement');
        $totals = $invoice->getTotals();
        $headerMonetary = $doc->createElement('ram:SpecifiedTradeSettlementHeaderMonetarySummation');
        $headerMonetary->appendChild($doc->createElement('ram:LineTotalAmount', $totals['lineTotal'] ?? 0));
        $headerMonetary->appendChild($doc->createElement('ram:TaxBasisTotalAmount', $totals['taxBasis'] ?? 0));
        $headerMonetary->appendChild($doc->createElement('ram:TaxTotalAmount', $totals['taxTotal'] ?? 0));
        $headerMonetary->appendChild($doc->createElement('ram:GrandTotalAmount', $totals['grandTotal'] ?? 0));
        $headerMonetary->appendChild($doc->createElement('ram:DuePayableAmount', $totals['grandTotal'] ?? 0));
        $headerSettlement->appendChild($headerMonetary);
        $trade->appendChild($headerSettlement);

        // --- Lignes de facture ---
        foreach ($invoice->getLines() as $line) {
            $lineEl = $doc->createElement('ram:IncludedSupplyChainTradeLineItem');
            $trade->appendChild($lineEl);

            // AssociatedDocumentLineDocument
            $docLine = $doc->createElement('ram:AssociatedDocumentLineDocument');
            $docLine->appendChild($doc->createElement('ram:LineID', $line->getLineId()));
            if ($line->getNote()) {
                $note = $doc->createElement('ram:IncludedNote');
                $note->appendChild($doc->createElement('ram:Content', $line->getNote()));
                $docLine->appendChild($note);
            }
            $lineEl->appendChild($docLine);

            // Product
            $product = $doc->createElement('ram:SpecifiedTradeProduct');
            $globalId = $doc->createElement('ram:GlobalID', $line->getGlobalId());
            $globalId->setAttribute('schemeID', 'GTIN');
            $product->appendChild($globalId);
            $product->appendChild($doc->createElement('ram:SellerAssignedID', $line->getSellerId()));
            $product->appendChild($doc->createElement('ram:Name', $line->getProductName()));
            if ($line->getDescription()) {
                $product->appendChild($doc->createElement('ram:Description', $line->getDescription()));
            }
            foreach ($line->getCharacteristics() as $c) {
                $charEl = $doc->createElement('ram:ApplicableProductCharacteristic');
                $charEl->appendChild($doc->createElement('ram:Description', $c['desc']));
                $charEl->appendChild($doc->createElement('ram:Value', $c['value']));
                $product->appendChild($charEl);
            }
            $lineEl->appendChild($product);

            // Line Agreement
            $agreement = $doc->createElement('ram:SpecifiedLineTradeAgreement');
            if ($line->getGrossPrice() !== null) {
                $gross = $doc->createElement('ram:GrossPriceProductTradePrice');
                $gross->appendChild($doc->createElement('ram:ChargeAmount', $line->getGrossPrice()));
                $agreement->appendChild($gross);
            }
            if ($line->getNetPrice() !== null) {
                $net = $doc->createElement('ram:NetPriceProductTradePrice');
                $net->appendChild($doc->createElement('ram:ChargeAmount', $line->getNetPrice()));
                $agreement->appendChild($net);
            }
            $lineEl->appendChild($agreement);

            // Line Delivery
            $delivery = $doc->createElement('ram:SpecifiedLineTradeDelivery');
            $billedQuantity = $doc->createElement('ram:BilledQuantity', $line->getQuantity());
            $billedQuantity->setAttribute('unitCode', $line->getUnit());
            $delivery->appendChild($billedQuantity);
            $lineEl->appendChild($delivery);

            // Line Settlement
            $settlement = $doc->createElement('ram:SpecifiedLineTradeSettlement');
            if ($line->getTaxRate() !== null) {
                $tax = $doc->createElement('ram:ApplicableTradeTax');
                $tax->appendChild($doc->createElement('ram:TypeCode', 'VAT'));
                $tax->appendChild($doc->createElement('ram:CategoryCode', $line->getTaxCategory()));
                $tax->appendChild($doc->createElement('ram:RateApplicablePercent', $line->getTaxRate()));
                $settlement->appendChild($tax);
            }
            foreach ($line->getAllowances() as $allowance) {
                $allowEl = $doc->createElement('ram:SpecifiedTradeAllowanceCharge');
                $chargeInd = $doc->createElement('ram:ChargeIndicator');
                $chargeInd->appendChild($doc->createElement('udt:Indicator', $allowance['charge'] ? 'true' : 'false'));
                $allowEl->appendChild($chargeInd);
                $allowEl->appendChild($doc->createElement('ram:ActualAmount', floatval($allowance['amount'])));
                $allowEl->appendChild($doc->createElement('ram:Reason', $allowance['reason']));
                $settlement->appendChild($allowEl);
            }
            $monetary = $doc->createElement('ram:SpecifiedTradeSettlementLineMonetarySummation');
            $monetary->appendChild($doc->createElement('ram:LineTotalAmount', $line->getNetPrice() ?? 0));
            $settlement->appendChild($monetary);
            $lineEl->appendChild($settlement);
        }

        return $doc->saveXML();
    }
}
