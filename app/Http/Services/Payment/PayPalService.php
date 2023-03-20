<?php

namespace App\Http\Services\Payment;

use App\Http\Services\Payment\Contracts\PaymentContract;

class PayPalService implements PaymentContract
{
    public function pay($total, $order): bool
    {
        return true;
    }
}
