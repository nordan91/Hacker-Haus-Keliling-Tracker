<?php

namespace App\Http\Requests\Location;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'address'       => ['nullable', 'string', 'max:500'],
            'latitude'      => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude'     => ['sometimes', 'numeric', 'between:-180,180'],
            'radius_meters' => ['sometimes', 'integer', 'min:10', 'max:5000'],
        ];
    }
}

