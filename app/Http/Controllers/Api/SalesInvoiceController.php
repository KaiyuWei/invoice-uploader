<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesInvoiceController extends Controller
{
    public function uploadInvoice(Request $request)
    {
        return response()->json(['message' => '200 OK']);
    }
}
