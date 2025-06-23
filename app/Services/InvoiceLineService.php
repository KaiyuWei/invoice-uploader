<?php

namespace App\Services;

use App\Services\Contracts\InvoiceLineServiceInterface;
use App\Models\SalesInvoice;
use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Collection;

class InvoiceLineService implements InvoiceLineServiceInterface
{
    public function createInvoiceLines(SalesInvoice $invoice, array $invoiceLinesData): Collection
    {
        $createdLines = new Collection();

        foreach ($invoiceLinesData as $lineData) {
            $createdLines->push($this->createInvoiceLine($invoice, $lineData));
        }

        return $createdLines;
    }

    public function createInvoiceLine(SalesInvoice $invoice, array $lineData): InvoiceLine
    {
        $invoiceLine = $invoice->invoiceLines()->create($lineData);

        return $invoiceLine;
    }
}
