<?php

namespace App\Http\Contracts;

interface PaymentMethod
{
    public function pay($amount, $order);
}
