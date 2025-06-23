<?php

namespace Tests\Unit;

use App\Services\Contracts\InvoiceLineServiceInterface;
use App\Factories\Contracts\SalesInvoiceFactoryInterface;
use App\Models\SalesInvoice;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    private InvoiceService $invoiceService;
    private InvoiceLineServiceInterface|MockInterface $mockInvoiceLineService;
    private SalesInvoiceFactoryInterface|MockInterface $mockSalesInvoiceFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockInvoiceLineService = Mockery::mock(InvoiceLineServiceInterface::class);
        $this->mockSalesInvoiceFactory = Mockery::mock(SalesInvoiceFactoryInterface::class);
        $this->invoiceService = new InvoiceService($this->mockInvoiceLineService, $this->mockSalesInvoiceFactory);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_invoice_successfully()
    {
        $validatedData = [
            'customerName' => 'Test Company',
            'invoiceDate' => '2024-01-15',
            'totalAmount' => 1500.00,
            'invoiceLines' => [
                [
                    'description' => 'Web Development',
                    'quantity' => 10.0,
                    'unitPrice' => 100.00,
                    'amount' => 1000.00,
                ],
                [
                    'description' => 'Design Services',
                    'quantity' => 5.0,
                    'unitPrice' => 100.00,
                    'amount' => 500.00,
                ]
            ]
        ];

        $invoiceAttributes = [
            'customer_name' => $validatedData['customerName'],
            'invoice_date' => $validatedData['invoiceDate'],
            'total_amount' => $validatedData['totalAmount'],
        ];

        $mockInvoice = Mockery::mock(SalesInvoice::class)->makePartial();
        $mockInvoice->customerName = $validatedData['customerName'];
        $mockInvoice->invoiceDate = $validatedData['invoiceDate'];
        $mockInvoice->totalAmount = $validatedData['totalAmount'];

        $mockInvoice->shouldReceive('load')
        ->once()
        ->with('invoiceLines')
        ->andReturnSelf();

        $this->mockSalesInvoiceFactory
            ->shouldReceive('create')
            ->once()
            ->with($invoiceAttributes)
            ->andReturn($mockInvoice);

        $this->mockInvoiceLineService
            ->shouldReceive('createInvoiceLines')
            ->once()
            ->with($mockInvoice, $validatedData['invoiceLines']);

        Log::shouldReceive('info')
            ->once();

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->invoiceService->createInvoice($validatedData);

        $this->assertInstanceOf(SalesInvoice::class, $result);
        $this->assertEquals('Test Company', $result->customerName);
        $this->assertEquals('2024-01-15', $result->invoiceDate);
        $this->assertEquals(1500.00, $result->totalAmount);
    }
}
