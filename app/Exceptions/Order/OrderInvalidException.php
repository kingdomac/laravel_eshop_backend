<?php

namespace App\Exceptions\Order;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class OrderInvalidException extends CustomException
{
    //
    public function render()
    {
        return new JsonResponse([
            'error' =>  $this->getMessage()

        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function InvalidQuantity(): self
    {
        return new self('Some of the purchased quantities are bigger than the one in the stock');
    }
}
