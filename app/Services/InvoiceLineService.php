<?php

namespace App\Services;

use App\Services\Contracts\InvoiceLineServiceInterface;
use App\Models\SalesInvoice;
use App\Models\InvoiceLine;

class InvoiceLineService implements InvoiceLineServiceInterface
{
    public function createInvoiceLines(SalesInvoice $invoice, array $invoiceLinesData): void
    {
        foreach ($invoiceLinesData as $lineData) {
            $this->createInvoiceLine($invoice, $lineData);
        }
    }

    public function createInvoiceLine(SalesInvoice $invoice, array $lineData): InvoiceLine
    {
        $lineData['quantity'] = round($lineData['quantity'], 2);
        $lineData['unit_price'] = round($lineData['unitPrice'], 2);
        $lineData['amount'] = round($lineData['amount'], 2);

        $invoiceLine = $invoice->invoiceLines()->create([
            'description' => $lineData['description'],
            'quantity' => $lineData['quantity'],
            'unit_price' => $lineData['unitPrice'],
            'amount' => $lineData['amount'],
        ]);

        return $invoiceLine;
    }
}
