<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'merchant_order_id' => ['required', 'string', 'max:128'],
            'amount' => ['required', 'string', 'regex:/^\d+(\.\d+)?$/'],
            'currency' => ['required', 'string', 'max:16'],
            'network' => ['required', 'string', 'max:32'],
            'callback_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
