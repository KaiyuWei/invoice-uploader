<?php

namespace App\Services;

use App\Models\SalesInvoice;
use App\Models\InvoiceLine;
use Illuminate\Support\Facades\Log;

class InvoiceLineService
{
    /**
     * Create invoice line items for a invoice
     *
     * @param SalesInvoice $invoice
     * @param array $invoiceLinesData
     * @return void
     */
    public function createInvoiceLines(SalesInvoice $invoice, array $invoiceLinesData): void
    {
        foreach ($invoiceLinesData as $lineData) {
            $this->createInvoiceLine($invoice, $lineData);
        }
    }

    /**
     * Create a single invoice line item
     *
     * @param SalesInvoice $invoice
     * @param array $lineData
     * @return InvoiceLine
     */
    public function createInvoiceLine(SalesInvoice $invoice, array $lineData): InvoiceLine
    {
        $invoiceLine = $invoice->invoiceLines()->create([
            'description' => $lineData['description'],
            'quantity' => $lineData['quantity'],
            'unit_price' => $lineData['unitPrice'],
            'amount' => $lineData['amount'],
        ]);

        return $invoiceLine;
    }
}
