<?php

namespace App\Services\Contracts;

use App\Models\SalesInvoice;

interface ExactOnlineServiceInterface
{
    /**
     * send an invoice to ExactOnlineApi
     *
     * @param SalesInvoice $invoice
     * @return bool
     */
    public function sendInvoice(SalesInvoice $invoice): bool;
}
