<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    /**
     * The attributes that are mass assignable.
     * @todo create class for customers and change 'customerName' to 'customerId'
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customerName',
        'invoiceDate',
        'totalAmount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoiceDate' => 'date',
        'totalAmount' => 'decimal:2',
    ];

    /**
     * Get the invoice lines for this sales invoice.
     */
    public function invoiceLines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }
}
