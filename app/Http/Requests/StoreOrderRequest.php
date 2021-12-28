<?php

namespace App\Http\Requests;

use App\Http\Services\CreditService;
use App\Http\Services\PayPalService;
use App\Models\Order;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
            'paymentMethod' => Rule::in([CreditService::PAYMENT_METHOD, PayPalService::PAYMENT_METHOD]),
            'cardNumber' => ['exclude_unless:paymentMethod,' . CreditService::PAYMENT_METHOD, 'required', 'integer'],
            'expiryDate' => ['exclude_unless:paymentMethod,' . CreditService::PAYMENT_METHOD, 'required', 'date_format:m/y', 'after:' . date('m/y')],
            'securityNumber' => ['exclude_unless:paymentMethod,' . CreditService::PAYMENT_METHOD, 'required',  'max:999', 'digits:3', 'numeric']
        ];
    }
}
