<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/health",
     *     tags={"Utilitários"},
     *     summary="Verificar status da API",
     *     description="Endpoint para verificar se a API e seus serviços estão funcionando",
     *     @OA\Response(
     *         response=200,
     *         description="API funcionando",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="message", type="string", example="API is running successfully!"),
     *             @OA\Property(property="timestamp", type="string", format="datetime", example="2025-07-25T14:30:00.000000Z"),
     *             @OA\Property(property="services", type="object",
     *                 @OA\Property(property="database", type="string", example="connected"),
     *                 @OA\Property(property="redis", type="string", example="connected")
     *             )
     *         )
     *     )
     * )
     */
    public function check()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'API is running successfully!',
            'timestamp' => now(),
            'services' => [
                'database' => $this->checkDatabase(),
                'redis' => $this->checkRedis(),
            ]
        ]);
    }

    /**
     * Check database connection
     */
    private function checkDatabase()
    {
        try {
            \DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }

    /**
     * Check Redis connection
     */
    private function checkRedis()
    {
        try {
            \Illuminate\Support\Facades\Redis::ping();
            return 'connected';
        } catch (\Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }

    /**
     * @OA\Get(
     *     path="/phpinfo",
     *     tags={"Utilitários"},
     *     summary="Informações do PHP",
     *     description="Exibe informações detalhadas do PHP (apenas para desenvolvimento)",
     *     @OA\Response(
     *         response=200,
     *         description="Página HTML com informações do PHP",
     *         @OA\MediaType(
     *             mediaType="text/html",
     *             @OA\Schema(type="string")
     *         )
     *     )
     * )
     */
    public function phpinfo()
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        return response($phpinfo)->header('Content-Type', 'text/html');
    }
}
