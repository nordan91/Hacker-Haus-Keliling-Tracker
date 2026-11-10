<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Role check handled by middleware
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'category'  => ['nullable', 'string', 'max:100'],
            'price'     => ['required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

