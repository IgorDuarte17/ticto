<?php

use App\Services\TimeRecordCacheService;
use App\Services\TimeRecordService;
use App\Models\User;
use App\Models\TimeRecord;

describe('TimeRecordCacheService', function () {
    beforeEach(function () {
        $this->cacheService = app(TimeRecordCacheService::class);
        $this->timeRecordService = app(TimeRecordService::class);
        
        $this->cacheService->invalidateAllCache();
    });

    it('should cache paginated records', function () {
        $user = User::factory()->admin()->create();
        
        TimeRecord::factory()->count(5)->create();
        
        $filters = ['page' => 1];
        $perPage = 15;
        
        $firstCall = $this->timeRecordService->getPaginatedRecords($filters, $perPage);
        
        $secondCall = $this->timeRecordService->getPaginatedRecords($filters, $perPage);
        
        expect($firstCall->total())->toBe($secondCall->total());
        expect($firstCall->count())->toBe($secondCall->count());
    });

    it('should cache today records for user', function () {
        $user = User::factory()->employee()->create();
        
        TimeRecord::factory()->count(3)->create([
            'user_id' => $user->id,
            'recorded_at' => now(),
        ]);
        
        $firstCall = $this->timeRecordService->getTodayRecordsByUser($user->id);
        
        $secondCall = $this->timeRecordService->getTodayRecordsByUser($user->id);
        
        expect($firstCall->count())->toBe($secondCall->count());
        expect($firstCall->count())->toBe(3);
    });

    it('should cache can record status', function () {
        $user = User::factory()->employee()->create();
        
        $firstCall = $this->timeRecordService->canRecordTime($user->id);
        
        $secondCall = $this->timeRecordService->canRecordTime($user->id);
        
        expect($firstCall)->toBe($secondCall);
        expect($firstCall['can_record'])->toBeTrue();
        expect($firstCall['message'])->toContain('Você pode registrar');
    });

    it('should invalidate cache when recording time', function () {
        $user = User::factory()->employee()->create();
        
        $canRecordBefore = $this->timeRecordService->canRecordTime($user->id);
        expect($canRecordBefore['can_record'])->toBeTrue();
        
        $this->timeRecordService->recordTime($user->id);
        
        $canRecordAfter = $this->timeRecordService->canRecordTime($user->id);
        expect($canRecordAfter['can_record'])->toBeFalse();
        expect($canRecordAfter['message'])->toContain('Aguarde 1 minuto');
    });

    it('should generate unique cache keys', function () {
        $key1 = $this->cacheService->generateCacheKey('test', ['param1' => 'value1']);
        $key2 = $this->cacheService->generateCacheKey('test', ['param1' => 'value2']);
        $key3 = $this->cacheService->generateCacheKey('different', ['param1' => 'value1']);
        
        expect($key1)->not->toBe($key2);
        expect($key1)->not->toBe($key3);
        expect($key2)->not->toBe($key3);
    });

    it('should handle cache errors gracefully', function () {
        $user = User::factory()->employee()->create();
        
        $result = $this->cacheService->getCachedData('invalid_key_that_does_not_exist');
        expect($result)->toBeNull();
        
        $records = $this->timeRecordService->getTodayRecordsByUser($user->id);
        expect($records)->not->toBeNull();
    });
});