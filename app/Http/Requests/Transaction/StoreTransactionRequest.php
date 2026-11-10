<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Expected payload:
     * {
     *   "payment_method": "cash",
     *   "latitude": -6.2088,
     *   "longitude": 106.8456,
     *   "items": [
     *     { "product_id": 1, "qty": 2 },
     *     { "product_id": 3, "qty": 1 }
     *   ]
     * }
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::in(['cash', 'qris'])],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'             => 'At least one item is required.',
            'items.*.product_id.exists'  => 'Product #:input does not exist.',
            'items.*.qty.min'            => 'Quantity must be at least 1.',
        ];
    }
}

