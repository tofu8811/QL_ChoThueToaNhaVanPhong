<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

/**
 * Controller tầng cơ sở cho toàn bộ API V1.
 * Cung cấp chuẩn phản hồi thống nhất ApiResponse.
 */
abstract class BaseApiController extends Controller
{
    use ApiResponse;
}
