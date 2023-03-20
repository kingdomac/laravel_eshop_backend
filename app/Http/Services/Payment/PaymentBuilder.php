<?php

namespace App\Http\Services\Payment;

use App\Http\Services\Payment\CreditService;
use App\Http\Services\Payment\PayPalService;
use App\Http\Services\Payment\Enums\PaymentMethodEnum;

class PaymentBuilder
{

    public static function build(PaymentMethodEnum $paymentMethod)
    {
        return match ($paymentMethod) {
            PaymentMethodEnum::CREDIT => new CreditService(),
            PaymentMethodEnum::PAYPAL => new PayPalService(),
        };
    }
}
