<?php
namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;

class GeneralController extends Controller
{
    use ApiResponse;

    public function orderStatus()
    {
        return $this->success(OrderStatus::values());
    }
}
