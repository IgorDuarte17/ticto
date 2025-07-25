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
     * @OA\Get(
     *     path="/via-cep/{cep}",
     *     tags={"Utilitários"},
     *     summary="Consultar endereço por CEP",
     *     description="Consulta endereço através da API ViaCEP (rota pública)",
     *     @OA\Parameter(
     *         name="cep",
     *         in="path",
     *         description="CEP para consulta (formato: 12345678 - apenas números)",
     *         required=true,
     *         @OA\Schema(type="string", pattern="^[0-9]{8}$", example="01310100")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Endereço encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cep", type="string", example="01310-100"),
     *                 @OA\Property(property="logradouro", type="string", example="Avenida Paulista"),
     *                 @OA\Property(property="complemento", type="string", example=""),
     *                 @OA\Property(property="bairro", type="string", example="Bela Vista"),
     *                 @OA\Property(property="localidade", type="string", example="São Paulo"),
     *                 @OA\Property(property="uf", type="string", example="SP")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="CEP inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="CEP inválido")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="CEP não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="CEP não encontrado")
     *         )
     *     )
     * )
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
