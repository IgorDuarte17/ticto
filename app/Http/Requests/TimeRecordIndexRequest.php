<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeRecordIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'search' => ['nullable', 'string', 'max:255'],
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
            'user_id.integer' => 'O ID do usuário deve ser um número inteiro.',
            'user_id.exists' => 'O usuário especificado não existe.',
            'search.string' => 'O campo de busca deve ser um texto.',
            'search.max' => 'O campo de busca não pode ter mais de 255 caracteres.',
            'per_page.integer' => 'O número de itens por página deve ser um número inteiro.',
            'per_page.min' => 'O número de itens por página deve ser pelo menos 1.',
            'per_page.max' => 'O número de itens por página não pode ser maior que 100.',
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
            'user_id' => 'usuário',
            'search' => 'busca',
            'per_page' => 'itens por página',
        ];
    }
}
