<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Expected payload:
     * {
     *   "inventories": [
     *     { "product_id": 1, "start_qty": 20 },
     *     { "product_id": 2, "start_qty": 15 },
     *     ...
     *   ]
     * }
     */
    public function rules(): array
    {
        return [
            'inventories'              => ['required', 'array', 'min:1'],
            'inventories.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'inventories.*.start_qty'  => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'inventories.required'                 => 'At least one product inventory is required.',
            'inventories.*.product_id.exists'      => 'Product #:input does not exist.',
            'inventories.*.start_qty.min'           => 'Starting quantity cannot be negative.',
        ];
    }
}

