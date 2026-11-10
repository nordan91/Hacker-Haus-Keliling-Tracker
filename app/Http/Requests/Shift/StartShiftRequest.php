<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class StartShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_date' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Prepare the data for validation.
     * Default shift_date to today if not provided.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('shift_date')) {
            $this->merge([
                'shift_date' => now()->toDateString(),
            ]);
        }
    }
}

