<?php

namespace App\Http\Services\Payment\Enums;

enum PaymentMethodEnum: int
{
    case CREDIT = 1;
    case PAYPAL = 2;
}
