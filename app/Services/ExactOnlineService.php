<?php

namespace App\Services;

use App\Services\Contracts\ExternalApiFakeClientInterface;
use App\Services\Contracts\ExactOnlineServiceInterface;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\Log;
use Exception;

class ExactOnlineService implements ExactOnlineServiceInterface
{
    public const POST_INVOICE_ENDPOINT = '/api/invoice';

    public function __construct(
        private ExternalApiFakeClientInterface $exactOnlineClient
    ) {
    }

    public function sendInvoice(SalesInvoice $invoice): bool
    {
        $payload = $this->buildExactOnlinePayload($invoice);

        Log::info('Forwarding invoice to ExactOnline.', [
            // better to generate and send a uuid to the api. The uuid of invoice should also be stored in the database.
            'invoice_id' => $invoice->id,
            'payload' => $payload
        ]);

        try {
            $response = $this->exactOnlineClient->post(self::POST_INVOICE_ENDPOINT, $payload);
            if ($this->isSuccessfulResponse($response)) {
                Log::info('Invoice successfully sent to ExactOnline.', [
                    'invoice_id' => $invoice->id,
                ]);
                return true;
            } else {
                Log::error('ExactOnline API returned an error response.', [
                    'invoice_id' => $invoice->id,
                ]);
                return false;
            }
        } catch (Exception $e) {
            Log::error('Failed to send invoice to ExactOnline.', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if the API response indicates success
     */
    private function isSuccessfulResponse(array $response): bool
    {
        return $response['status'] === 'success';
    }

    /**
     * Simulate building the ExactOnline api payload.
     */
    private function buildExactOnlinePayload(SalesInvoice $invoice): array
    {
        return [
            'CustomerName' => $invoice->customerName,
            'InvoiceDate' => $invoice->invoiceDate,
            'TotalAmount' => $invoice->totalAmount,
            'Lines' => $invoice->invoiceLines->map(function ($line) {
                return [
                    'Description' => $line->description,
                    'Quantity' => $line->quantity,
                    'UnitPrice' => $line->unitPrice
                ];
            })->toArray()
        ];
    }
}
