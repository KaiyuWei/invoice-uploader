<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SalesInvoiceController;

Route::post('/sales-invoices', [SalesInvoiceController::class, 'upload']);
