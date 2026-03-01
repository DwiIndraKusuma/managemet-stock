<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

/**
 * @OA\Info(
 *     title="Inventory & Procurement Management System API",
 *     version="1.0.0",
 *     description="API documentation for Inventory & Procurement Management System"
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */
class BaseController extends Controller
{
    use ApiResponse;
}
