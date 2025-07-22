<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    /**
     * Health check endpoint
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
            \Redis::ping();
            return 'connected';
        } catch (\Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }
}
