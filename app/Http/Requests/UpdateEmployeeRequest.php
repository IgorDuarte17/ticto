<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
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
        $employeeId = $this->route('id');
        
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'cpf' => ['sometimes', 'required', 'string', 'size:11', Rule::unique('users', 'cpf')->ignore($employeeId)],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employeeId)],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'position' => ['sometimes', 'required', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'required', 'date', 'before:today'],
            'cep' => ['sometimes', 'required', 'string', 'size:8'],
            'address_number' => ['sometimes', 'required', 'string', 'max:10'],
            'address_complement' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser um texto.',
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
            
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este email já está cadastrado no sistema.',
            
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser um texto.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            
            'position.required' => 'O cargo é obrigatório.',
            'position.string' => 'O cargo deve ser um texto.',
            'position.max' => 'O cargo não pode ter mais de 255 caracteres.',
            
            'birth_date.required' => 'A data de nascimento é obrigatória.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            
            'cep.required' => 'O CEP é obrigatório.',
            'cep.string' => 'O CEP deve ser um texto.',
            'cep.size' => 'O CEP deve ter exatamente 8 dígitos.',
            
            'address_number.required' => 'O número do endereço é obrigatório.',
            'address_number.string' => 'O número do endereço deve ser um texto.',
            'address_number.max' => 'O número do endereço não pode ter mais de 10 caracteres.',
            
            'address_complement.string' => 'O complemento do endereço deve ser um texto.',
            'address_complement.max' => 'O complemento do endereço não pode ter mais de 255 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove formatação do CPF se houver
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/[^0-9]/', '', $this->cpf)
            ]);
        }

        // Remove formatação do CEP se houver
        if ($this->has('cep')) {
            $this->merge([
                'cep' => preg_replace('/[^0-9]/', '', $this->cep)
            ]);
        }
    }
}
