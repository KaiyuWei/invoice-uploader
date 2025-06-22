<?php

namespace App\Services;

use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\InvoiceLineService;

class InvoiceService
{
    public function __construct(
        private InvoiceLineService $invoiceLineService
    ) {
    }

    /**
     * Create a new sales invoice with line items
     *
     * @param array $validatedData
     * @return SalesInvoice
     * @throws \Exception
     */
    public function createInvoice(array $validatedData): SalesInvoice
    {
        return DB::transaction(function () use ($validatedData) {
            $invoice = SalesInvoice::create([
                'customer_name' => $validatedData['customerName'],
                'invoice_date' => $validatedData['invoiceDate'],
                'total_amount' => $validatedData['totalAmount'],
            ]);

            $this->invoiceLineService->createInvoiceLines($invoice, $validatedData['invoiceLines']);

            Log::info('Sales invoice created with ' . count($validatedData['invoiceLines']) . ' lines', [
                'invoice_id' => $invoice->id,
                'customer_name' => $invoice->customerName,
                'total_amount' => $invoice->totalAmount
            ]);

            return $invoice->load('invoiceLines');
        });
    }
}
