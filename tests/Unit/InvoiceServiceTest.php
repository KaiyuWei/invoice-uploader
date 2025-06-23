<?php

namespace Tests\Unit;

use App\Services\Contracts\InvoiceLineServiceInterface;
use App\Factories\Contracts\SalesInvoiceFactoryInterface;
use App\Services\Contracts\ExactOnlineServiceInterface;
use App\Models\SalesInvoice;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use App\Models\InvoiceLine;

class InvoiceServiceTest extends TestCase
{
    private InvoiceService $invoiceService;
    private ExactOnlineServiceInterface|MockInterface $mockExactOnlineService;
    private InvoiceLineServiceInterface|MockInterface $mockInvoiceLineService;
    private SalesInvoiceFactoryInterface|MockInterface $mockSalesInvoiceFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockInvoiceLineService = Mockery::mock(InvoiceLineServiceInterface::class);
        $this->mockSalesInvoiceFactory = Mockery::mock(SalesInvoiceFactoryInterface::class);
        $this->mockExactOnlineService = Mockery::mock(ExactOnlineServiceInterface::class);
        $this->invoiceService = new InvoiceService($this->mockInvoiceLineService, $this->mockSalesInvoiceFactory, $this->mockExactOnlineService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_invoice_and_send_to_exactonline_successful()
    {
        $validatedData = [
            'customer_name' => 'Test Company',
            'invoice_date' => '2024-01-15',
            'total_amount' => 1500.00,
            'invoice_lines' => [
                [
                    'description' => 'Web Development',
                    'quantity' => 10.0,
                    'unit_price' => 100.00,
                    'amount' => 1000.00,
                ],
                [
                    'description' => 'Design Services',
                    'quantity' => 5.0,
                    'unit_price' => 100.00,
                    'amount' => 500.00,
                ]
            ]
        ];

        $invoiceAttributes = [
            'customer_name' => $validatedData['customer_name'],
            'invoice_date' => $validatedData['invoice_date'],
            'total_amount' => $validatedData['total_amount'],
        ];

        $mockInvoice = Mockery::mock(SalesInvoice::class)->makePartial();
        $mockInvoice->customerName = $validatedData['customer_name'];
        $mockInvoice->invoiceDate = $validatedData['invoice_date'];
        $mockInvoice->totalAmount = $validatedData['total_amount'];

        $this->mockSalesInvoiceFactory
            ->shouldReceive('create')
            ->once()
            ->with($invoiceAttributes)
            ->andReturn($mockInvoice);
        $this->mockInvoiceLineService
            ->shouldReceive('createInvoiceLines')
            ->once()
            ->with($mockInvoice, $validatedData['invoice_lines'])
            ->andReturn(Mockery::mock(Collection::class));
        $this->mockExactOnlineService
            ->shouldReceive('sendInvoice')
            ->once()
            ->with($mockInvoice)
            ->andReturn(true);

        Log::shouldReceive('info')
            ->once();

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->invoiceService->createInvoiceAndSendToExactOnline($validatedData);

        $this->assertInstanceOf(SalesInvoice::class, $result['invoice']);
        $this->assertEquals('Test Company', $result['invoice']->customerName);
        $this->assertEquals('2024-01-15', $result['invoice']->invoiceDate);
        $this->assertEquals(1500.00, $result['invoice']->totalAmount);
        $this->assertTrue($result['isSentToExactOnline']);
    }

    public function test_sending_invoice_to_exactonline_fails()
    {
        $validatedData = [
            'customer_name' => 'Test Company',
            'invoice_date' => '2024-01-15',
            'total_amount' => 1500.00,
            'invoice_lines' => [
                [
                    'description' => 'Web Development',
                    'quantity' => 10.0,
                    'unit_price' => 100.00,
                    'amount' => 1000.00,
                ],
                [
                    'description' => 'Design Services',
                    'quantity' => 5.0,
                    'unit_price' => 100.00,
                    'amount' => 500.00,
                ]
            ]
        ];

        $invoiceAttributes = [
            'customer_name' => $validatedData['customer_name'],
            'invoice_date' => $validatedData['invoice_date'],
            'total_amount' => $validatedData['total_amount'],
        ];

        $mockInvoice = Mockery::mock(SalesInvoice::class)->makePartial();
        $mockInvoice->customerName = $validatedData['customer_name'];
        $mockInvoice->invoiceDate = $validatedData['invoice_date'];
        $mockInvoice->totalAmount = $validatedData['total_amount'];

        $this->mockSalesInvoiceFactory
            ->shouldReceive('create')
            ->once()
            ->with($invoiceAttributes)
            ->andReturn($mockInvoice);
        $this->mockInvoiceLineService
            ->shouldReceive('createInvoiceLines')
            ->once()
            ->with($mockInvoice, $validatedData['invoice_lines'])
            ->andReturn(Mockery::mock(Collection::class));
        $this->mockExactOnlineService
            ->shouldReceive('sendInvoice')
            ->once()
            ->with($mockInvoice)
            ->andReturn(false);

        Log::shouldReceive('info')
            ->once();

        Log::shouldReceive('error')
            ->once();

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->invoiceService->createInvoiceAndSendToExactOnline($validatedData);

        $this->assertInstanceOf(SalesInvoice::class, $result['invoice']);
        $this->assertEquals('Test Company', $result['invoice']->customerName);
        $this->assertEquals('2024-01-15', $result['invoice']->invoiceDate);
        $this->assertEquals(1500.00, $result['invoice']->totalAmount);
        $this->assertFalse($result['isSentToExactOnline']);
    }

    public function test_create_invoice_successful()
    {
        $line1 = [
            'description' => 'Web Development',
            'quantity' => 10.0,
            'unit_price' => 100.00,
            'amount' => 1000.00,
        ];
        $line2 = [
            'description' => 'Design Services',
            'quantity' => 5.0,
            'unit_price' => 100.00,
            'amount' => 500.00,
        ];
        $validatedData = [
            'customer_name' => 'Test Company',
            'invoice_date' => '2024-01-15',
            'total_amount' => 1500.00,
            'invoice_lines' => [$line1, $line2],
        ];
        $invoiceAttributes = [
            'customer_name' => $validatedData['customer_name'],
            'invoice_date' => $validatedData['invoice_date'],
            'total_amount' => $validatedData['total_amount'],
        ];

        $mockInvoice = Mockery::mock(SalesInvoice::class)->makePartial();
        $mockInvoice->customerName = $validatedData['customer_name'];
        $mockInvoice->invoiceDate = $validatedData['invoice_date'];
        $mockInvoice->totalAmount = $validatedData['total_amount'];

        $mockInvoiceLines = new Collection();
        $mockInvoiceLines->push(new InvoiceLine(['description' => $line1['description'], 'quantity' => $line1['quantity'], 'unit_price' => $line1['unit_price'], 'amount' => $line1['amount']]));
        $mockInvoiceLines->push(new InvoiceLine(['description' => $line2['description'], 'quantity' => $line2['quantity'], 'unit_price' => $line2['unit_price'], 'amount' => $line2['amount']]));
        $mockInvoice->invoiceLines = $mockInvoiceLines;

        $this->mockSalesInvoiceFactory
            ->shouldReceive('create')
            ->once()
            ->with($invoiceAttributes)
            ->andReturn($mockInvoice);
        $this->mockInvoiceLineService
            ->shouldReceive('createInvoiceLines')
            ->once()
            ->with($mockInvoice, $validatedData['invoice_lines'])
            ->andReturn(Mockery::mock(Collection::class));

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->invoiceService->createInvoiceWithLines($validatedData);

        $this->assertInstanceOf(SalesInvoice::class, $result);
        $this->assertEquals('Test Company', $result->customerName);
        $this->assertEquals('2024-01-15', $result->invoiceDate);
        $this->assertEquals(1500.00, $result->totalAmount);
        $this->assertCount(2, $result->invoiceLines);
    }

    public function test_throws_exception_for_empty_invoice_lines(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice lines cannot be empty. An invoice must have at least one line item.');

        $validatedData = [
            'customer_name' => 'Test Customer',
            'invoice_date' => '2024-01-15',
            'total_amount' => 1500.00,
            'invoice_lines' => []
        ];

        $this->invoiceService->createInvoiceWithLines($validatedData);
    }

    public function test_throws_exception_for_missing_invoice_lines(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice lines cannot be empty. An invoice must have at least one line item.');

        $validatedData = [
            'customer_name' => 'Test Customer',
            'invoice_date' => '2024-01-15',
            'total_amount' => 1500.00
        ];

        $this->invoiceService->createInvoiceWithLines($validatedData);
    }
}
