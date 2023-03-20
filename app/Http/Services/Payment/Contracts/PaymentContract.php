<?php

namespace App\Http\Services\Payment\Contracts;

interface PaymentContract
{
    public function pay($amount, $order);
}
