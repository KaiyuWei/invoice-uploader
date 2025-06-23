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
            'customer_name' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0|decimal:0,5',
            'invoice_lines' => 'required|array|min:1',
            'invoice_lines.*.description' => 'required|string|max:1000',
            'invoice_lines.*.quantity' => 'required|numeric|min:0|decimal:0,5',
            'invoice_lines.*.unit_price' => 'required|numeric|min:0|decimal:0,5',
            'invoice_lines.*.amount' => 'required|numeric|min:0|decimal:0,5',
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
            'customer_name.required' => 'Customer name is required.',
            'customer_name.max' => 'Customer name cannot exceed 255 characters.',
            'invoice_date.required' => 'Invoice date is required.',
            'invoice_date.date' => 'Invoice date must be a valid date.',
            'total_amount.required' => 'Total amount is required.',
            'total_amount.numeric' => 'Total amount must be a number.',
            'total_amount.min' => 'Total amount cannot be negative.',
            'invoice_lines.required' => 'At least one invoice line is required.',
            'invoice_lines.array' => 'Invoice lines must be an array.',
            'invoice_lines.min' => 'At least one invoice line is required.',
            'invoice_lines.*.description.required' => 'Line item description is required.',
            'invoice_lines.*.description.max' => 'Line item description cannot exceed 1000 characters.',
            'invoice_lines.*.quantity.required' => 'Line item quantity is required.',
            'invoice_lines.*.quantity.numeric' => 'Line item quantity must be a number.',
            'invoice_lines.*.quantity.min' => 'Line item quantity cannot be negative.',
            'invoice_lines.*.unit_price.required' => 'Line item unit price is required.',
            'invoice_lines.*.unit_price.numeric' => 'Line item unit price must be a number.',
            'invoice_lines.*.unit_price.min' => 'Line item unit price cannot be negative.',
            'invoice_lines.*.amount.required' => 'Line item amount is required.',
            'invoice_lines.*.amount.numeric' => 'Line item amount must be a number.',
            'invoice_lines.*.amount.min' => 'Line item amount cannot be negative.',
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
            'customer_name' => 'customer name',
            'invoice_date' => 'invoice date',
            'total_amount' => 'total amount',
            'invoice_lines' => 'invoice lines',
            'invoice_lines.*.description' => 'line item description',
            'invoice_lines.*.quantity' => 'line item quantity',
            'invoice_lines.*.unit_price' => 'line item unit price',
            'invoice_lines.*.amount' => 'line item amount',
        ];
    }
}
