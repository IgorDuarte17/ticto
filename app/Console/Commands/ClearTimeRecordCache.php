<?php

namespace App\Console\Commands;

use App\Services\TimeRecordCacheService;
use Illuminate\Console\Command;

class ClearTimeRecordCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-time-records {--user= : Clear cache for specific user ID} {--stats : Show cache statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear time records cache or show cache statistics';

    public function __construct(
        private TimeRecordCacheService $cacheService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('stats')) {
            $this->showCacheStats();
            return;
        }

        $userId = $this->option('user');

        if ($userId) {
            $this->clearUserCache($userId);
        } else {
            $this->clearAllCache();
        }
    }

    private function showCacheStats(): void
    {
        $this->info('📊 Cache Statistics');
        $this->line('');

        $stats = $this->cacheService->getCacheStats();

        if (!$stats['connected']) {
            $this->error('❌ Redis not connected: ' . $stats['error']);
            return;
        }

        $this->line('✅ Redis Connected');
        $this->line('📦 Memory Used: ' . $stats['memory_used']);
        $this->line('📈 Memory Peak: ' . $stats['memory_peak']);
        $this->line('🔑 Total Keys: ' . $stats['keys_count']);
    }

    private function clearUserCache(int $userId): void
    {
        $this->info("🧹 Clearing cache for user ID: {$userId}");
        
        $this->cacheService->invalidateUserCache($userId);
        
        $this->line('✅ User cache cleared successfully!');
    }

    private function clearAllCache(): void
    {
        $this->info('🧹 Clearing all time records cache...');
        
        if ($this->confirm('This will clear all time records cache. Continue?')) {
            $this->cacheService->invalidateAllCache();
            $this->line('✅ All cache cleared successfully!');
        } else {
            $this->line('❌ Operation cancelled.');
        }
    }
}
