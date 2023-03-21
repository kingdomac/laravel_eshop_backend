<?php

namespace App\Http\Requests;

use App\Http\Services\CreditService;
use App\Http\Services\Payment\Enums\PaymentMethodEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
            'address' => ['required'],
            'items' => ['required', 'array'],
            'items.*.id' => [Rule::exists('products', 'id')],
            'paymentMethod' => [new Enum(PaymentMethodEnum::class)],
            'cardNumber' => ['exclude_unless:paymentMethod,' . PaymentMethodEnum::CREDIT->value, 'required', 'integer'],
            'expiryDate' => ['exclude_unless:paymentMethod,' . PaymentMethodEnum::CREDIT->value, 'required', 'date_format:m/y', 'after:' . date('m/y')],
            'securityNumber' => ['exclude_unless:paymentMethod,' . PaymentMethodEnum::CREDIT->value, 'required',  'max:999', 'digits:3', 'numeric']
        ];
    }
}