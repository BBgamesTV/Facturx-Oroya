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

        // --- Racine ---
        $root = $doc->createElementNS(
            'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
            'rsm:CrossIndustryInvoice'
        );
        $doc->appendChild($root);

        // --- ExchangedDocumentContext obligatoire pour Factur-X ---
        $docContext = $doc->createElement('rsm:ExchangedDocumentContext');
        $root->appendChild($docContext);

        $guideline = $doc->createElement('ram:GuidelineSpecifiedDocumentContextParameter');
        $docContext->appendChild($guideline);

        $id = $doc->createElement('ram:ID', 'urn:cen.eu:en16931:2017'); // profil Basic Factur-X
        $guideline->appendChild($id);

        // --- ExchangedDocument ---
        $exDoc = $doc->createElement('rsm:ExchangedDocument');
        $root->appendChild($exDoc);

        $exDoc->appendChild($doc->createElement('ram:ID', $invoice->getInvoiceNumber()));

        $issueDate = $doc->createElement('ram:IssueDateTime');
        $exDoc->appendChild($issueDate);
        $dt = $doc->createElement('udt:DateTimeString', $invoice->getIssueDate()->format('Ymd'));
        $dt->setAttribute('format', '102');
        $issueDate->appendChild($dt);

        // --- SupplyChainTradeTransaction ---
        $trade = $doc->createElement('rsm:SupplyChainTradeTransaction');
        $root->appendChild($trade);

        // --- Lignes de facture ---
        foreach ($invoice->getLines() as $line) {
            $lineEl = $doc->createElement('ram:IncludedSupplyChainTradeLineItem');
            $trade->appendChild($lineEl);

            // Document line
            $docLine = $doc->createElement('ram:AssociatedDocumentLineDocument');
            $lineEl->appendChild($docLine);
            $docLine->appendChild($doc->createElement('ram:LineID', $line->getLineId()));

            if ($line->getNote()) {
                $note = $doc->createElement('ram:IncludedNote');
                $note->appendChild($doc->createElement('ram:Content', $line->getNote()));
                $docLine->appendChild($note);
            }

            // Product
            $product = $doc->createElement('ram:SpecifiedTradeProduct');
            $lineEl->appendChild($product);
            $product->appendChild($doc->createElement('ram:GlobalID', $line->getGlobalId()));
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

            // Line trade agreement
            $agreement = $doc->createElement('ram:SpecifiedLineTradeAgreement');
            $lineEl->appendChild($agreement);
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

            // Line delivery
            $delivery = $doc->createElement('ram:SpecifiedLineTradeDelivery');
            $deliveryEl = $doc->createElement('ram:BilledQuantity', $line->getQuantity());
            $deliveryEl->setAttribute('unitCode', $line->getUnit());
            $delivery->appendChild($deliveryEl);
            $lineEl->appendChild($delivery);

            // Line settlement
            $settlement = $doc->createElement('ram:SpecifiedLineTradeSettlement');
            $lineEl->appendChild($settlement);

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
                $allowEl->appendChild($doc->createElement('ram:ActualAmount', $allowance['amount']));
                $allowEl->appendChild($doc->createElement('ram:Reason', $allowance['reason']));
                $settlement->appendChild($allowEl);
            }

            $monetary = $doc->createElement('ram:SpecifiedTradeSettlementLineMonetarySummation');
            $monetary->appendChild($doc->createElement('ram:LineTotalAmount', $line->getNetPrice() ?? 0));
            $settlement->appendChild($monetary);
        }

        // --- Header settlement (totaux) ---
        $headerSettle = $doc->createElement('ram:ApplicableHeaderTradeSettlement');
        $root->appendChild($headerSettle);

        $totals = $invoice->getTotals();
        $headerMonetary = $doc->createElement('ram:SpecifiedTradeSettlementHeaderMonetarySummation');
        foreach ($totals as $key => $value) {
            $headerMonetary->appendChild($doc->createElement('ram:' . ucfirst($key), $value));
        }
        $headerSettle->appendChild($headerMonetary);

        return $doc->saveXML();
    }
}
