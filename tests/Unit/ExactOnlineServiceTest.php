<?php

namespace Tests\Unit;

use App\Models\SalesInvoice;
use App\Models\InvoiceLine;
use App\Services\Contracts\ExternalApiFakeClientInterface;
use App\Services\ExactOnlineService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class ExactOnlineServiceTest extends TestCase
{
    private ExactOnlineService $exactOnlineService;
    private ExternalApiFakeClientInterface|Mockery\LegacyMockInterface $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockClient = Mockery::mock(ExternalApiFakeClientInterface::class);
        $this->exactOnlineService = new ExactOnlineService($this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_send_invoice_successfully()
    {
        $attributes = [
            'CustomerName' => 'Test Company',
            'InvoiceDate' => '2024-01-15',
            'TotalAmount' => 1500.00,
            'Lines' => [
                [
                    'Description' => 'Web Development',
                    'Quantity' => 10.0,
                    'UnitPrice' => 100.00
                ]
            ]
        ];

        /** @var SalesInvoice|Mockery\LegacyMockInterface $mockInvoice */
        $mockInvoice = Mockery::mock(SalesInvoice::class)->makePartial();
        $mockInvoiceLines = Mockery::mock(Collection::class);
        
        $mockInvoice->id = 1;
        $mockInvoice->customerName = $attributes['CustomerName'];
        $mockInvoice->invoiceDate = $attributes['InvoiceDate'];
        $mockInvoice->totalAmount = $attributes['TotalAmount'];

        $invoiceLine0 = new InvoiceLine();
        $invoiceLine0->description = $attributes['Lines'][0]['Description'];
        $invoiceLine0->quantity = $attributes['Lines'][0]['Quantity'];
        $invoiceLine0->unitPrice = $attributes['Lines'][0]['UnitPrice'];

        $mockInvoice->invoiceLines = new Collection([$invoiceLine0]);

        $successResponse = [
            'status' => 'success',
            'message' => 'Invoice sent to ExactOnline',
        ];

        $this->mockClient->shouldReceive('post')
            ->once()
            ->with('/api/invoice', $attributes)
            ->andReturn($successResponse);

        $result = $this->exactOnlineService->sendInvoice($mockInvoice);

        $this->assertTrue($result);
    }

    public function test_send_invoice_with_api_error_response()
    {
        $attributes = [
            'CustomerName' => 'Test Company',
            'InvoiceDate' => '2024-01-15',
            'TotalAmount' => 1500.00,
            'Lines' => [
                [
                    'Description' => 'Web Development',
                    'Quantity' => 10.0,
                    'UnitPrice' => 100.00
                ]
            ]
        ];

        /** @var SalesInvoice|Mockery\LegacyMockInterface $mockInvoice */
        $mockInvoice = Mockery::mock(SalesInvoice::class)->makePartial();
        $mockInvoiceLines = Mockery::mock(Collection::class);
        
        $mockInvoice->id = 1;
        $mockInvoice->customerName = $attributes['CustomerName'];
        $mockInvoice->invoiceDate = $attributes['InvoiceDate'];
        $mockInvoice->totalAmount = $attributes['TotalAmount'];

        $invoiceLine0 = new InvoiceLine();
        $invoiceLine0->description = $attributes['Lines'][0]['Description'];
        $invoiceLine0->quantity = $attributes['Lines'][0]['Quantity'];
        $invoiceLine0->unitPrice = $attributes['Lines'][0]['UnitPrice'];

        $mockInvoice->invoiceLines = new Collection([$invoiceLine0]);

        $errorResponse = [
            'status' => 'error',
            'message' => 'Invoice not sent to ExactOnline',
        ];

        $this->mockClient->shouldReceive('post')
            ->once()
            ->with('/api/invoice', $attributes)
            ->andReturn($errorResponse);

        $result = $this->exactOnlineService->sendInvoice($mockInvoice);

        $this->assertFalse($result);
    }
} 