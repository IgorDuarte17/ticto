<?php

use App\Services\ViaCepService;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->service = new ViaCepService();
});

describe('ViaCepService', function () {
    describe('getAddressByCep', function () {
        it('should return address data for valid CEP', function () {
            $cep = '01310-100';
            $expectedResponse = [
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'complemento' => ''
            ];

            Http::fake([
                'https://viacep.com.br/ws/01310100/json/' => Http::response($expectedResponse, 200)
            ]);

            $result = $this->service->getAddressByCep($cep);

            expect($result)->toBe([
                'cep' => '01310-100',
                'address' => 'Avenida Paulista',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'complement' => ''
            ]);
        });

        it('should return address data for CEP without mask', function () {
            $cep = '01310100';
            $expectedResponse = [
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'complemento' => ''
            ];

            Http::fake([
                'https://viacep.com.br/ws/01310100/json/' => Http::response($expectedResponse, 200)
            ]);

            $result = $this->service->getAddressByCep($cep);

            expect($result)->toBe([
                'cep' => '01310-100',
                'address' => 'Avenida Paulista',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'complement' => ''
            ]);
        });

        it('should throw exception for CEP with invalid length', function () {
            $cep = '123456'; // Menos de 8 dígitos

            expect(fn() => $this->service->getAddressByCep($cep))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception for CEP with more than 8 digits', function () {
            $cep = '123456789'; // Mais de 8 dígitos

            expect(fn() => $this->service->getAddressByCep($cep))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when CEP not found', function () {
            $cep = '99999999';
            $errorResponse = [
                'erro' => true
            ];

            Http::fake([
                'https://viacep.com.br/ws/99999999/json/' => Http::response($errorResponse, 200)
            ]);

            expect(fn() => $this->service->getAddressByCep($cep))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when API request fails', function () {
            $cep = '01310100';

            Http::fake([
                'https://viacep.com.br/ws/01310100/json/' => Http::response(null, 500)
            ]);

            expect(fn() => $this->service->getAddressByCep($cep))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when connection fails', function () {
            $cep = '01310100';

            Http::fake([
                'https://viacep.com.br/ws/01310100/json/' => function () {
                    throw new \Exception('Connection failed');
                }
            ]);

            expect(fn() => $this->service->getAddressByCep($cep))
                ->toThrow(ValidationException::class);
        });

        it('should handle missing fields in API response', function () {
            $cep = '01310100';
            $incompleteResponse = [
                'cep' => '01310-100',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
                // Campos faltando: logradouro, bairro, complemento
            ];

            Http::fake([
                'https://viacep.com.br/ws/01310100/json/' => Http::response($incompleteResponse, 200)
            ]);

            $result = $this->service->getAddressByCep($cep);

            expect($result)->toBe([
                'cep' => '01310-100',
                'address' => '',
                'neighborhood' => '',
                'city' => 'São Paulo',
                'state' => 'SP',
                'complement' => ''
            ]);
        });

        it('should remove non-numeric characters from CEP', function () {
            $cep = '01.310-100abc';
            $expectedResponse = [
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'complemento' => ''
            ];

            Http::fake([
                'https://viacep.com.br/ws/01310100/json/' => Http::response($expectedResponse, 200)
            ]);

            $result = $this->service->getAddressByCep($cep);

            expect($result['cep'])->toBe('01310-100');
        });
    });

    describe('isValidCepFormat', function () {
        it('should return true for valid CEP format with mask', function () {
            $cep = '01310-100';

            $result = $this->service->isValidCepFormat($cep);

            expect($result)->toBeTrue();
        });

        it('should return true for valid CEP format without mask', function () {
            $cep = '01310100';

            $result = $this->service->isValidCepFormat($cep);

            expect($result)->toBeTrue();
        });

        it('should return false for CEP with invalid length', function () {
            $cep = '123456';

            $result = $this->service->isValidCepFormat($cep);

            expect($result)->toBeFalse();
        });

        it('should return false for CEP with more than 8 digits', function () {
            $cep = '123456789';

            $result = $this->service->isValidCepFormat($cep);

            expect($result)->toBeFalse();
        });

        it('should return true for CEP with special characters removed', function () {
            $cep = '01.310-100';

            $result = $this->service->isValidCepFormat($cep);

            expect($result)->toBeTrue();
        });

        it('should return false for empty CEP', function () {
            $cep = '';

            $result = $this->service->isValidCepFormat($cep);

            expect($result)->toBeFalse();
        });

        it('should return false for CEP with only letters', function () {
            $cep = 'abcdefgh';

            $result = $this->service->isValidCepFormat($cep);

            expect($result)->toBeFalse();
        });
    });
});
