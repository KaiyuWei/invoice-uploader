<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadInvoiceRequest;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;

class SalesInvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {
    }

    /**
     * Upload a new sales invoice with line items
     *
     * @OA\Post(
     *     path="/sales-invoices",
     *     summary="Upload a new sales invoice",
     *     description="Creates a new sales invoice with customer details and line items",
     *     tags={"Invoices"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customerName", "invoiceDate", "totalAmount", "invoiceLines"},
     *             @OA\Property(
     *                 property="customerName",
     *                 type="string",
     *                 maxLength=255,
     *                 example="Kaiyu Company",
     *                 description="Name of the customer"
     *             ),
     *             @OA\Property(
     *                 property="invoiceDate",
     *                 type="string",
     *                 format="date",
     *                 example="2024-01-15",
     *                 description="Date of the invoice"
     *             ),
     *             @OA\Property(
     *                 property="totalAmount",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 example=1500.00,
     *                 description="Total amount of the invoice"
     *             ),
     *             @OA\Property(
     *                 property="invoiceLines",
     *                 type="array",
     *                 minItems=1,
     *                 @OA\Items(
     *                     type="object",
     *                     required={"description", "quantity", "unitPrice", "amount"},
     *                     @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         maxLength=1000,
     *                         example="Web Development Services",
     *                         description="Description of the line item"
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="number",
     *                         format="float",
     *                         minimum=0,
     *                         example=10.0,
     *                         description="Quantity of the item"
     *                     ),
     *                     @OA\Property(
     *                         property="unitPrice",
     *                         type="number",
     *                         format="float",
     *                         minimum=0,
     *                         example=100.00,
     *                         description="Unit price of the item"
     *                     ),
     *                     @OA\Property(
     *                         property="amount",
     *                         type="number",
     *                         format="float",
     *                         minimum=0,
     *                         example=1000.00,
     *                         description="Total amount for this line"
     *                     )
     *                 ),
     *                 description="Array of invoice line items"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Invoice created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"success", "incomplete"},
     *                 example="success",
     *                 description="Result of sending invoice to ExactOnline. If it is sent successfully, the status is 'success'. Otherwise, the status is 'incomplete'."
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invoice uploaded successfully, and has been sent to ExactOnline"
     *             ),
     *             @OA\Property(
     *                 property="invoice",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="customer_name", type="string", example="Test Customer Integration"),
     *                 @OA\Property(property="invoice_date", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=1500.00),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="invoice_lines",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="sales_invoice_id", type="integer", example=1),
     *                         @OA\Property(property="description", type="string", example="Web Development Services"),
     *                         @OA\Property(property="quantity", type="number", format="float", example=10.0),
     *                         @OA\Property(property="unit_price", type="number", format="float", example=100.00),
     *                         @OA\Property(property="amount", type="number", format="float", example=1000.00),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Authentication error.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Need authentication to access this endpoint"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="customerName",
     *                     type="array",
     *                     @OA\Items(type="string", example="Customer name is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function uploadInvoice(UploadInvoiceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $result = $this->invoiceService->createInvoiceAndSendToExactOnline($validated);

            if ($result['isSentToExactOnline']) {
                $status = 'success';
                $message = 'Invoice uploaded successfully, and has been sent to ExactOnline';
            } else {
                $status = 'incomplete';
                $message = 'Invoice uploaded successfully, but failed to send to ExactOnline';
            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                'invoice' => $result['invoice'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
