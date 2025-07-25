<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ViaCepService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ViaCepController extends Controller
{
    public function __construct(
        private ViaCepService $viaCepService
    ) {}

    /**
     * Consultar endereço por CEP
     */
    public function getAddress(string $cep): JsonResponse
    {
        try {
            $address = $this->viaCepService->getAddressByCep($cep);
            
            return response()->json([
                'status' => 'success',
                'data' => $address
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
