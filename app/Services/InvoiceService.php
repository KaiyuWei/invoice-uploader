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
        // a invoice without any line items is invalid
        if (empty($validatedData['invoiceLines'])) {
            throw new \InvalidArgumentException('Invoice lines cannot be empty. An invoice must have at least one line item.');
        }

        $invoice = $this->createInvoiceWithLines($validatedData);

        Log::info('Sales invoice created with ' . count($validatedData['invoiceLines']) . ' lines', [
            'invoice_id' => $invoice->id,
        ]);

        $reqeustResult = $this->exactOnlineService->sendInvoice($invoice);
        if (!$reqeustResult) {
            /**
             * There are different ways to handle this situation:
             *
             * 1. Retry the send invoice operation in limited times, say at most 3 times. If it still fails,
             *    we should send an email to the user with the invoice details. This method can be used when we want to
             *    use the ExactOnline cloud service to send the invoice data with our customer.
             *
             * 2. Retry the send invoice operation in limited times. If it still fails, we should roll back the invoice data
             *    stored in the app database;
             *
             *    OR, we can first create the Eloquent object of invoice and invoice lines, only when the response is successful,
             *    we can then store the invoice and invoice lines in the database.
             *
             *    This method can be used when the synchronization is strictly required, e.g. when making the backup of
             *    the invoice data.
             *
             * 3. We can also just log the invoice id when it still fails after the max try times. Later the list of invoice ids
             *    that failed to send to ExactOnline can be processed by a cron job regularly. This method fit for the case
             *    that the synchronization is not strictly required, and it is also not urgent to do.
             *
             * Given that we're just simulating the implementation, we choose the simplest method, i.e. just log the invoice id.
             */

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
