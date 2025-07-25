<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TimeRecordCacheService
{
    const CACHE_PREFIX = 'time_records';
    const DEFAULT_TTL = 3600; // 1 hora
    const REPORTS_TTL = 7200; // 2 horas para relatórios
    const TODAY_RECORDS_TTL = 300; // 5 minutos para registros de hoje
    const PAGINATION_TTL = 1800; // 30 minutos para paginação

    /**
     * Gera chave de cache para registros paginados
     */
    public function getPaginationCacheKey(array $filters, int $perPage, int $page = 1): string
    {
        $keyData = [
            'filters' => $filters,
            'per_page' => $perPage,
            'page' => $page,
        ];
        
        return self::CACHE_PREFIX . ':pagination:' . md5(serialize($keyData));
    }

    /**
     * Gera chave de cache para registros de hoje por usuário
     */
    public function getTodayRecordsCacheKey(int $userId): string
    {
        $today = Carbon::now()->toDateString();
        return self::CACHE_PREFIX . ':today:user:' . $userId . ':' . $today;
    }

    /**
     * Gera chave de cache para todos os registros de hoje (admin)
     */
    public function getAllTodayRecordsCacheKey(): string
    {
        $today = Carbon::now()->toDateString();
        return self::CACHE_PREFIX . ':today:all:' . $today;
    }

    /**
     * Gera chave de cache para relatórios por período
     */
    public function getReportCacheKey(array $filters): string
    {
        return self::CACHE_PREFIX . ':report:' . md5(serialize($filters));
    }

    /**
     * Gera chave de cache para status de registro (can_record)
     */
    public function getCanRecordCacheKey(int $userId): string
    {
        return self::CACHE_PREFIX . ':can_record:user:' . $userId;
    }

    /**
     * Gera uma chave de cache genérica com base em um tipo e parâmetros
     */
    public function generateCacheKey(string $type, array $params = []): string
    {
        $keyData = array_merge(['type' => $type], $params);
        return self::CACHE_PREFIX . ':' . $type . ':' . md5(serialize($keyData));
    }

    /**
     * Cache de registros paginados
     */
    public function cachePagedRecords(string $key, $data, int $ttl = self::PAGINATION_TTL)
    {
        try {
            Cache::put($key, $data, $ttl);
            Log::info("Cache armazenado", ['key' => $key, 'ttl' => $ttl]);
        } catch (\Exception $e) {
            Log::error("Erro ao armazenar cache", ['key' => $key, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Recupera registros paginados do cache
     */
    public function getPagedRecords(string $key)
    {
        try {
            $data = Cache::get($key);
            if ($data) {
                Log::info("Cache encontrado", ['key' => $key]);
            }
            return $data;
        } catch (\Exception $e) {
            Log::error("Erro ao recuperar cache", ['key' => $key, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Cache de registros de hoje
     */
    public function cacheTodayRecords(string $key, $data, int $ttl = self::TODAY_RECORDS_TTL)
    {
        try {
            Cache::put($key, $data, $ttl);
            Log::info("Cache today records armazenado", ['key' => $key]);
        } catch (\Exception $e) {
            Log::error("Erro ao armazenar cache today records", ['key' => $key, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Recupera registros de hoje do cache
     */
    public function getTodayRecords(string $key)
    {
        try {
            return Cache::get($key);
        } catch (\Exception $e) {
            Log::error("Erro ao recuperar cache today records", ['key' => $key, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Cache de relatórios
     */
    public function cacheReport(string $key, $data, int $ttl = self::REPORTS_TTL)
    {
        try {
            Cache::put($key, $data, $ttl);
            Log::info("Cache relatório armazenado", ['key' => $key]);
        } catch (\Exception $e) {
            Log::error("Erro ao armazenar cache relatório", ['key' => $key, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Recupera relatório do cache
     */
    public function getReport(string $key)
    {
        try {
            return Cache::get($key);
        } catch (\Exception $e) {
            Log::error("Erro ao recuperar cache relatório", ['key' => $key, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Cache do status can_record
     */
    public function cacheCanRecord(string $key, array $data, int $ttl = 60)
    {
        try {
            Cache::put($key, $data, $ttl);
            Log::info("Cache can_record armazenado", ['key' => $key]);
        } catch (\Exception $e) {
            Log::error("Erro ao armazenar cache can_record", ['key' => $key, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Recupera status can_record do cache
     */
    public function getCanRecord(string $key)
    {
        try {
            return Cache::get($key);
        } catch (\Exception $e) {
            Log::error("Erro ao recuperar cache can_record", ['key' => $key, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Método de conveniência para recuperar dados cacheados diretamente por chave
     */
    public function getCachedData(string $key): mixed
    {
        try {
            return Cache::get($key);
        } catch (\Exception $e) {
            Log::warning('Erro ao recuperar dados do cache', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Invalida cache relacionado a um usuário específico
     */
    public function invalidateUserCache(int $userId): void
    {
        try {
            if (config('cache.default') === 'array') {
                $specificKeys = [
                    $this->getTodayRecordsCacheKey($userId),
                    $this->getCanRecordCacheKey($userId),
                    $this->getAllTodayRecordsCacheKey(),
                ];
                
                foreach ($specificKeys as $key) {
                    Cache::forget($key);
                }
                
                Log::info("Cache do usuário invalidado (array driver)", ['user_id' => $userId]);
                return;
            }
            
            $patterns = [
                self::CACHE_PREFIX . ':today:user:' . $userId . ':*',
                self::CACHE_PREFIX . ':can_record:user:' . $userId,
                self::CACHE_PREFIX . ':pagination:*',
                self::CACHE_PREFIX . ':report:*',
                self::CACHE_PREFIX . ':today:all:*',
            ];

            foreach ($patterns as $pattern) {
                $this->invalidateCachePattern($pattern);
            }

            Log::info("Cache do usuário invalidado", ['user_id' => $userId]);
        } catch (\Exception $e) {
            Log::error("Erro ao invalidar cache do usuário", ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Invalida cache por padrão
     */
    private function invalidateCachePattern(string $pattern): void
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                    Log::info("Cache invalidado por padrão", ['pattern' => $pattern, 'keys_count' => count($keys)]);
                }
            } else {
                Log::info("Cache invalidation solicitada para padrão", ['pattern' => $pattern, 'driver' => config('cache.default')]);
            }
        } catch (\Exception $e) {
            Log::warning("Erro ao invalidar por padrão, usando fallback", ['pattern' => $pattern, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Invalida todo o cache de time records
     */
    public function invalidateAllCache(): void
    {
        try {
            $this->invalidateCachePattern(self::CACHE_PREFIX . ':*');
            Log::info("Todo cache de time records invalidado");
        } catch (\Exception $e) {
            Log::error("Erro ao invalidar todo cache", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Limpa cache expirado (pode ser usado em comandos agendados)
     */
    public function cleanExpiredCache(): void
    {
        try {
            Log::info("Limpeza de cache expirado executada");
        } catch (\Exception $e) {
            Log::error("Erro na limpeza de cache", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtém estatísticas do cache
     */
    public function getCacheStats(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $info = $redis->info('memory');
                
                return [
                    'memory_used' => $info['used_memory_human'] ?? 'N/A',
                    'memory_peak' => $info['used_memory_peak_human'] ?? 'N/A',
                    'keys_count' => $redis->dbsize(),
                    'connected' => true,
                    'driver' => 'redis',
                ];
            } else {
                return [
                    'driver' => config('cache.default'),
                    'connected' => true,
                    'message' => 'Cache statistics only available for Redis driver',
                ];
            }
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'connected' => false,
                'driver' => config('cache.default', 'unknown'),
            ];
        }
    }

    /**
     * Invalida apenas o cache de paginação (usado na listagem de registros)
     */
    public function invalidatePaginationCache(): void
    {
        try {
            if (config('cache.default') === 'array') {
                Cache::flush();
                Log::info("Cache de paginação invalidado (array driver - cache completo limpo)");
                return;
            }
            
            $pattern = self::CACHE_PREFIX . ':pagination:*';
            $this->invalidateCachePattern($pattern);
            
            Log::info("Cache de paginação invalidado", ['pattern' => $pattern]);
        } catch (\Exception $e) {
            Log::error("Erro ao invalidar cache de paginação", ['error' => $e->getMessage()]);
        }
    }
}
