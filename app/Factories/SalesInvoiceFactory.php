<?php

namespace App\Factories;

use App\Factories\Contracts\SalesInvoiceFactoryInterface;
use App\Models\SalesInvoice;

class SalesInvoiceFactory implements SalesInvoiceFactoryInterface
{
    public function create(array $attributes): SalesInvoice
    {
        return SalesInvoice::create($attributes);
    }
}
