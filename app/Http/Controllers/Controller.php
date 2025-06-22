<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 *  * @OA\Tag(
 *     name="Invoice",
 *     description="Invoice processing"
 * )
 * @OA\Info(
 *     title="Invoice processing API",
 *     version="1.0.0",
 *     description="API for managing sales invoices"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="API Server"
 * )
 */
class Controller extends BaseController
{
    //
}
