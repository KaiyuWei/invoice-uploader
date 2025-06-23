<?php

namespace App\Services;

use App\Services\Contracts\ExternalApiFakeClientInterface;
use App\Services\Contracts\ExactOnlineServiceInterface;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\Log;

class ExactOnlineService implements ExactOnlineServiceInterface
{
    public function __construct(
        private ExternalApiFakeClientInterface $exactOnlineClient
    ) {
    }

    public function sendInvoice(SalesInvoice $invoice): bool
    {
        $payload = $this->buildExactOnlinePayload($invoice);

        Log::info('Forwarding invoice to ExactOnline.', [
            'invoice_id' => $invoice->id,
            'payload' => $payload
        ]);

        // Since we are simulating, just log this action
        Log::info('Pretending to send payload to ExactOnline API client.', [
            'payload' => $payload
        ]);

        return true;
    }

    /**
     * Simulate building the ExactOnline api payload.
     */
    private function buildExactOnlinePayload(SalesInvoice $invoice): array
    {
        return [
            'CustomerName' => $invoice->customer_name,
            'InvoiceDate' => $invoice->invoice_date->toDateString(),
            'TotalAmount' => $invoice->total_amount,
            'Lines' => $invoice->invoiceLines->map(function ($line) {
                return [
                    'Description' => $line->description,
                    'Quantity' => $line->quantity,
                    'UnitPrice' => $line->unit_price
                ];
            })->toArray()
        ];
    }
}
