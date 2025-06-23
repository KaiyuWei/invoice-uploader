<?php

namespace App\Services\Contracts;

use App\Models\SalesInvoice;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceLineServiceInterface
{
    /**
     * Create invoice line items for a sales invoice
     *
     * @param SalesInvoice $invoice
     * @param array $invoiceLinesData
     * @return Collection
     */
    public function createInvoiceLines(SalesInvoice $invoice, array $invoiceLinesData): Collection;

    /**
     * Create a single invoice line item
     *
     * @param SalesInvoice $invoice
     * @param array $lineData
     * @return \App\Models\InvoiceLine
     */
    public function createInvoiceLine(SalesInvoice $invoice, array $lineData): \App\Models\InvoiceLine;
}
