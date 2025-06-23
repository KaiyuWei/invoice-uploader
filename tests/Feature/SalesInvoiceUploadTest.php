<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Simulation\ExactOnlineFakeClient;

class SalesInvoiceUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_invoice_with_valid_payload_returns_success(): void
    {
        $payload = [
            'customer_name' => 'Test Customer Integration',
            'invoice_date' => '2024-01-15',
            'total_amount' => 1500.00,
            'invoice_lines' => [
                [
                    'description' => 'Web Development Services',
                    'quantity' => 10.0,
                    'unit_price' => 100.00,
                    'amount' => 1000.00
                ],
                [
                    'description' => 'Consulting Services',
                    'quantity' => 5.0,
                    'unit_price' => 100.00,
                    'amount' => 500.00
                ]
            ]
        ];

        $response = $this->postJson('/api/sales-invoices', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message'
            ]);

        $responseData = $response->json();
        $this->assertContains($responseData['status'], ['success', 'incomplete']);

        if ($responseData['status'] === 'success') {
            $this->assertEquals('Invoice uploaded successfully, and has been sent to ExactOnline', $responseData['message']);
        } else {
            $this->assertEquals('Invoice uploaded successfully, but failed to send to ExactOnline', $responseData['message']);
        }

        $this->assertDatabaseHas('sales_invoices', [
            'customer_name' => 'Test Customer Integration',
            'invoice_date' => '2024-01-15 00:00:00',
            'total_amount' => 1500.00
        ]);

        $this->assertDatabaseHas('invoice_lines', [
            'description' => 'Web Development Services',
            'quantity' => 10.0,
            'unit_price' => 100.00,
            'amount' => 1000.00
        ]);

        $this->assertDatabaseHas('invoice_lines', [
            'description' => 'Consulting Services',
            'quantity' => 5.0,
            'unit_price' => 100.00,
            'amount' => 500.00
        ]);
    }

    public function test_upload_invoice_with_invalid_payload_returns_validation_error(): void
    {
        $payload = [
            'customer_name' => '', // Invalid: empty customer name
            'invoice_date' => 'invalid-date', // Invalid: wrong date format
            'total_amount' => -100, // Invalid: negative amount
            'invoice_lines' => [] // Invalid: empty invoice lines
        ];

        $response = $this->postJson('/api/sales-invoices', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'customer_name',
                'invoice_date',
                'total_amount',
                'invoice_lines'
            ]);
    }

    public function test_missing_customer_name_returns_validation_error(): void
    {
        $payload = [
            'invoice_date' => '2024-01-15',
            'total_amount' => 1500.00,
            'invoice_lines' => [
                [
                    'description' => 'Web Development Services',
                    'quantity' => 10.0,
                    'unit_price' => 100.00,
                    'amount' => 1000.00
                ],
                [
                    'description' => 'Consulting Services',
                    'quantity' => 5.0,
                    'unit_price' => 100.00,
                    'amount' => 500.00
                ]
            ]
        ];

        $response = $this->postJson('/api/sales-invoices', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'customer_name'
            ]);
    }

    public function test_missing_invoice_date_returns_validation_error(): void
    {
        $payload = [
            'customer_name' => 'Test Customer Integration',
            'total_amount' => 1500.00,
            'invoice_lines' => [
                [
                    'description' => 'Web Development Services',
                    'quantity' => 10.0,
                    'unit_price' => 100.00,
                    'amount' => 1000.00
                ],
                [
                    'description' => 'Consulting Services',
                    'quantity' => 5.0,
                    'unit_price' => 100.00,
                    'amount' => 500.00
                ]
            ]
        ];

        $response = $this->postJson('/api/sales-invoices', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'invoice_date'
            ]);
    }

    public function test_missing_total_amount_returns_validation_error(): void
    {
        $payload = [
            'customer_name' => 'Test Customer Integration',
            'invoice_date' => '2024-01-15',
            'invoice_lines' => [
                [
                    'description' => 'Web Development Services',
                    'quantity' => 10.0,
                    'unit_price' => 100.00,
                    'amount' => 1000.00
                ],
                [
                    'description' => 'Consulting Services',
                    'quantity' => 5.0,
                    'unit_price' => 100.00,
                    'amount' => 500.00
                ]
            ]
        ];

        $response = $this->postJson('/api/sales-invoices', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'total_amount'
            ]);
    }

    public function test_missing_invoice_lines_returns_validation_error(): void
    {
        $payload = [
            'customer_name' => 'Test Customer Integration',
            'invoice_date' => '2024-01-15',
            'total_amount' => 1500.00,
        ];

        $response = $this->postJson('/api/sales-invoices', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'invoice_lines'
            ]);
    }
}
