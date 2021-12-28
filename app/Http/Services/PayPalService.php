<?php

namespace App\Http\Services;

use App\Http\Contracts\PaymentMethod;

class PayPalService implements PaymentMethod
{
    const PAYMENT_METHOD = 2;

    public function pay($total, $dataOrder): bool
    {
        return true;
    }
}
