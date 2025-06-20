<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // No need to authorize, given the context of the assignment
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customerName' => 'required|string|max:255',
            'invoiceDate' => 'required|date',
            'totalAmount' => 'required|numeric|min:0|decimal:0,2',
            'invoiceLines' => 'required|array|min:1',
            'invoiceLines.*.description' => 'required|string|max:1000',
            'invoiceLines.*.quantity' => 'required|numeric|min:0|decimal:0,2',
            'invoiceLines.*.unitPrice' => 'required|numeric|min:0|decimal:0,2',
            'invoiceLines.*.amount' => 'required|numeric|min:0|decimal:0,2',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customerName.required' => 'Customer name is required.',
            'customerName.max' => 'Customer name cannot exceed 255 characters.',
            'invoiceDate.required' => 'Invoice date is required.',
            'invoiceDate.date' => 'Invoice date must be a valid date.',
            'totalAmount.required' => 'Total amount is required.',
            'totalAmount.numeric' => 'Total amount must be a number.',
            'totalAmount.min' => 'Total amount cannot be negative.',
            'totalAmount.decimal' => 'Total amount can have up to 2 decimal places.',
            'invoiceLines.required' => 'At least one invoice line is required.',
            'invoiceLines.array' => 'Invoice lines must be an array.',
            'invoiceLines.min' => 'At least one invoice line is required.',
            'invoiceLines.*.description.required' => 'Line item description is required.',
            'invoiceLines.*.description.max' => 'Line item description cannot exceed 1000 characters.',
            'invoiceLines.*.quantity.required' => 'Line item quantity is required.',
            'invoiceLines.*.quantity.numeric' => 'Line item quantity must be a number.',
            'invoiceLines.*.quantity.min' => 'Line item quantity cannot be negative.',
            'invoiceLines.*.unitPrice.required' => 'Line item unit price is required.',
            'invoiceLines.*.unitPrice.numeric' => 'Line item unit price must be a number.',
            'invoiceLines.*.unitPrice.min' => 'Line item unit price cannot be negative.',
            'invoiceLines.*.amount.required' => 'Line item amount is required.',
            'invoiceLines.*.amount.numeric' => 'Line item amount must be a number.',
            'invoiceLines.*.amount.min' => 'Line item amount cannot be negative.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customerName' => 'customer name',
            'invoiceDate' => 'invoice date',
            'totalAmount' => 'total amount',
            'invoiceLines' => 'invoice lines',
            'invoiceLines.*.description' => 'line item description',
            'invoiceLines.*.quantity' => 'line item quantity',
            'invoiceLines.*.unitPrice' => 'line item unit price',
            'invoiceLines.*.amount' => 'line item amount',
        ];
    }
}
