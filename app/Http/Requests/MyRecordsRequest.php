<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MyRecordsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'start_date.date_format' => 'A data inicial deve estar no formato Y-m-d (ex: 2025-01-15).',
            'end_date.date_format' => 'A data final deve estar no formato Y-m-d (ex: 2025-01-15).',
            'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'start_date' => 'data inicial',
            'end_date' => 'data final',
        ];
    }
}
