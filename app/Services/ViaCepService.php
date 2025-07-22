<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ViaCepService
{
    private const VIA_CEP_URL = 'https://viacep.com.br/ws';
    
    public function getAddressByCep(string $cep): array
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) !== 8) {
            throw ValidationException::withMessages([
                'cep' => ['CEP deve conter 8 dígitos.']
            ]);
        }
        
        try {
            $response = Http::timeout(10)->get(self::VIA_CEP_URL . "/{$cep}/json/");
            
            if (!$response->successful()) {
                throw ValidationException::withMessages([
                    'cep' => ['Erro ao consultar CEP. Tente novamente.']
                ]);
            }
            
            $data = $response->json();
            
            if (isset($data['erro']) && $data['erro'] === true) {
                throw ValidationException::withMessages([
                    'cep' => ['CEP não encontrado.']
                ]);
            }

            return [
                'cep' => $data['cep'] ?? $cep,
                'address' => $data['logradouro'] ?? '',
                'neighborhood' => $data['bairro'] ?? '',
                'city' => $data['localidade'] ?? '',
                'state' => $data['uf'] ?? '',
                'complement' => $data['complemento'] ?? '',
            ];
            
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            
            throw ValidationException::withMessages([
                'cep' => ['Erro de conexão ao consultar CEP. Verifique sua internet e tente novamente.']
            ]);
        }
    }
    
    public function isValidCepFormat(string $cep): bool
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return strlen($cep) === 8;
    }
}
