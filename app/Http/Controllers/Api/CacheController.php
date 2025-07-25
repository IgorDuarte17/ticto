<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TimeRecordCacheService;
use Illuminate\Http\Request;

class CacheController extends Controller
{
    public function __construct(
        private TimeRecordCacheService $cacheService
    ) {}

    /**
     * @OA\Get(
     *     path="/cache/stats",
     *     tags={"Utilitários"},
     *     summary="Estatísticas do cache",
     *     description="Retorna estatísticas de uso do cache Redis",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estatísticas do cache",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="memory_used", type="string", example="1.5M"),
     *                 @OA\Property(property="memory_peak", type="string", example="2.1M"),
     *                 @OA\Property(property="keys_count", type="integer", example=150),
     *                 @OA\Property(property="connected", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado (apenas admin)"
     *     )
     * )
     */
    public function stats(Request $request)
    {
        // Apenas admin pode ver estatísticas
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado',
                'status' => 'error'
            ], 403);
        }

        $stats = $this->cacheService->getCacheStats();

        return response()->json([
            'message' => 'Estatísticas do cache',
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/cache/clear",
     *     tags={"Utilitários"},
     *     summary="Limpar cache",
     *     description="Limpa o cache de registros de ponto",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID do usuário (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cache limpo com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cache limpo com sucesso"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado (apenas admin)"
     *     )
     * )
     */
    public function clear(Request $request)
    {
        // Apenas admin pode limpar cache
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado',
                'status' => 'error'
            ], 403);
        }

        $userId = $request->input('user_id');

        if ($userId) {
            $this->cacheService->invalidateUserCache($userId);
            $message = "Cache do usuário {$userId} limpo com sucesso";
        } else {
            $this->cacheService->invalidateAllCache();
            $message = 'Todo cache de registros de ponto limpo com sucesso';
        }

        return response()->json([
            'message' => $message,
            'status' => 'success'
        ]);
    }
}
