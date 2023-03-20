<?php

namespace App\Http\Services\Payment;

use App\Http\Services\Payment\Contracts\PaymentContract;

class CreditService implements PaymentContract
{
    public function pay($total, $dataOrder): bool
    {
        return true;
    }
}
