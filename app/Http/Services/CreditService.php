<?php

namespace App\Http\Services;

use App\Http\Contracts\PaymentMethod;

class CreditService implements PaymentMethod
{
    const PAYMENT_METHOD = 1;

    public function pay($total, $dataOrder): bool
    {
        return true;
    }
}
