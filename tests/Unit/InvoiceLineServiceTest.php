<?php

namespace Tests\Unit;

use App\Models\InvoiceLine;
use App\Models\SalesInvoice;
use App\Services\InvoiceLineService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\TestCase;

class InvoiceLineServiceTest extends TestCase
{
    private InvoiceLineService $invoiceLineService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceLineService = new InvoiceLineService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_invoice_line_successfully()
    {
        $lineData = [
            'description' => 'Consulting Services',
            'quantity' => 8.75,
            'unit_price' => 125.00,
            'amount' => 1093.75,
        ];

        $mockInvoice = Mockery::mock(SalesInvoice::class)->makePartial();
        $mockHasMany = Mockery::mock(HasMany::class)->makePartial();

        $mockInvoiceLine = Mockery::mock(InvoiceLine::class)->makePartial();
        $mockInvoiceLine->description = $lineData['description'];
        $mockInvoiceLine->quantity = $lineData['quantity'];
        $mockInvoiceLine->unit_price = $lineData['unit_price'];
        $mockInvoiceLine->amount = $lineData['amount'];

        /** @var SalesInvoice|Mockery\LegacyMockInterface $mockInvoice */
        $mockInvoice->shouldReceive('invoiceLines')
            ->once()
            ->andReturn($mockHasMany);

        $mockHasMany->shouldReceive('create')
            ->once()
            ->with([
                'description' => $lineData['description'],
                'quantity' => $lineData['quantity'],
                'unit_price' => $lineData['unit_price'],
                'amount' => $lineData['amount'],
            ])
            ->andReturn($mockInvoiceLine);

        $result = $this->invoiceLineService->createInvoiceLine($mockInvoice, $lineData);

        $this->assertInstanceOf(InvoiceLine::class, $result);
        $this->assertEquals($lineData['description'], $result->description);
        $this->assertEquals($lineData['quantity'], $result->quantity);
        $this->assertEquals($lineData['unit_price'], $result->unit_price);
        $this->assertEquals($lineData['amount'], $result->amount);
    }

    public function test_create_invoice_lines_successfully()
    {
        $line1 = [
            'description' => 'Web Development',
            'quantity' => 10.5,
            'unit_price' => 100.25,
            'amount' => 1052.625,
        ];
        $line2 = [
            'description' => 'Design Services',
            'quantity' => 5.0,
            'unit_price' => 75.50,
            'amount' => 377.50,
        ];

        $invoiceLinesData = [$line1, $line2];

        $mockInvoice = Mockery::mock(SalesInvoice::class);
        /** @var InvoiceLineService|Mockery\LegacyMockInterface $mockService */
        $mockService = Mockery::mock(InvoiceLineService::class)->makePartial();
        $invoiceLine1 = new InvoiceLine($line1);
        $invoiceLine2 = new InvoiceLine($line2);

        $mockService->shouldReceive('createInvoiceLine')
        ->once()
        ->with($mockInvoice, $line1)
        ->andReturn($invoiceLine1);

        $mockService->shouldReceive('createInvoiceLine')
        ->once()
        ->with($mockInvoice, $line2)
        ->andReturn($invoiceLine2);

        $expectedLineCollection = new Collection([$invoiceLine1, $invoiceLine2]);
        $result = $mockService->createInvoiceLines($mockInvoice, $invoiceLinesData);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals($expectedLineCollection, $result);
    }
}
