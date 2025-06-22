<?php

namespace App\Factories\Contracts;

use App\Models\SalesInvoice;

interface SalesInvoiceFactoryInterface
{
    /**
     * Create a sales Invoice
     *
     * @param array $attributes
     * @return SalesInvoice
     */
    public function create(array $attributes): SalesInvoice;
}
