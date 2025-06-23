<?php

namespace App\Services;

use App\Factories\Contracts\SalesInvoiceFactoryInterface;
use App\Services\Contracts\InvoiceLineServiceInterface;
use App\Services\Contracts\ExactOnlineServiceInterface;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        private InvoiceLineServiceInterface $invoiceLineService,
        private SalesInvoiceFactoryInterface $salesInvoiceFactory,
        private ExactOnlineServiceInterface $exactOnlineService
    ) {
    }

    /**
     * Create a new sales invoice with line items
     *
     * @param array $validatedData
     * @return array
     * @throws \Exception
     */
    public function createInvoiceAndSendToExactOnline(array $validatedData): array
    {
        $invoice = $this->createInvoiceWithLines($validatedData);

        Log::info('Sales invoice created with ' . count($validatedData['invoiceLines']) . ' lines', [
            'invoice_id' => $invoice->id,
        ]);

        $reqeustResult = $this->exactOnlineService->sendInvoice($invoice);
        if (!$reqeustResult) {
            Log::error('Failed to send invoice to ExactOnline', [
                'invoice_id' => $invoice->id,
            ]);
        }

        return [
            'isSentToExactOnline' => $reqeustResult,
            'invoice' => $invoice
        ];
    }

    public function createInvoiceWithLines(array $validatedData): SalesInvoice
    {
        // a invoice without any line items is invalid
        if (empty($validatedData['invoiceLines'])) {
            throw new \InvalidArgumentException('Invoice lines cannot be empty. An invoice must have at least one line item.');
        }

        $invoice = DB::transaction(function () use ($validatedData) {
            $invoice = $this->salesInvoiceFactory->create([
                'customer_name' => $validatedData['customerName'],
                'invoice_date' => $validatedData['invoiceDate'],
                'total_amount' => $validatedData['totalAmount'],
            ]);

            $this->invoiceLineService->createInvoiceLines($invoice, $validatedData['invoiceLines']);

            return $invoice;
        });

        return $invoice;
    }
}
