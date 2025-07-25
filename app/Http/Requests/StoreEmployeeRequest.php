<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'size:11', 'unique:users,cpf'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'position' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'cep' => ['required', 'string', 'size:8'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
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
            
            'street.required' => 'O endereço é obrigatório.',
            'street.string' => 'O endereço deve ser um texto.',
            'street.max' => 'O endereço não pode ter mais de 255 caracteres.',
            
            'number.required' => 'O número do endereço é obrigatório.',
            'number.string' => 'O número do endereço deve ser um texto.',
            'number.max' => 'O número do endereço não pode ter mais de 10 caracteres.',
            
            'complement.string' => 'O complemento do endereço deve ser um texto.',
            'complement.max' => 'O complemento do endereço não pode ter mais de 255 caracteres.',
            
            'neighborhood.required' => 'O bairro é obrigatório.',
            'neighborhood.string' => 'O bairro deve ser um texto.',
            'neighborhood.max' => 'O bairro não pode ter mais de 255 caracteres.',
            
            'city.required' => 'A cidade é obrigatória.',
            'city.string' => 'A cidade deve ser um texto.',
            'city.max' => 'A cidade não pode ter mais de 255 caracteres.',
            
            'state.required' => 'O estado é obrigatório.',
            'state.string' => 'O estado deve ser um texto.',
            'state.size' => 'O estado deve ter exatamente 2 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/[^0-9]/', '', $this->cpf)
            ]);
        }

        if ($this->has('cep')) {
            $this->merge([
                'cep' => preg_replace('/[^0-9]/', '', $this->cep)
            ]);
        }
    }
}
