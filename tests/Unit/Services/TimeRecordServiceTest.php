<?php

use App\Contracts\TimeRecordRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\TimeRecord;
use App\Models\User;
use App\Services\TimeRecordService;
use App\Services\TimeRecordCacheService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->timeRecordRepository = Mockery::mock(TimeRecordRepositoryInterface::class);
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->cacheService = Mockery::mock(TimeRecordCacheService::class);
    $this->service = new TimeRecordService($this->timeRecordRepository, $this->userRepository, $this->cacheService);
});

afterEach(function () {
    Mockery::close();
});

describe('TimeRecordService', function () {
    describe('recordTime', function () {
        it('should record time for a valid employee', function () {
            $userId = 1;
            $user = new User([
                'id' => $userId,
                'name' => 'John Doe',
                'role' => 'employee'
            ]);
            
            $expectedRecord = new TimeRecord([
                'id' => 1,
                'user_id' => $userId,
                'recorded_at' => now()
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn($user);

            $this->timeRecordRepository
                ->shouldReceive('getLatestByUser')
                ->with($userId)
                ->once()
                ->andReturn(null);

            $this->timeRecordRepository
                ->shouldReceive('create')
                ->once()
                ->andReturn($expectedRecord);

            $this->cacheService
                ->shouldReceive('invalidateUserCache')
                ->with($userId)
                ->once();

            $result = $this->service->recordTime($userId);

            expect($result)->toBeInstanceOf(TimeRecord::class);
        });

        it('should throw exception when user not found', function () {
            $userId = 999;

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn(null);

            expect(fn() => $this->service->recordTime($userId))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when user is not employee', function () {
            $userId = 1;
            $user = new User([
                'id' => $userId,
                'name' => 'Admin User',
                'role' => 'admin'
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn($user);

            expect(fn() => $this->service->recordTime($userId))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when user recorded time recently', function () {
            $userId = 1;
            $user = new User([
                'id' => $userId,
                'name' => 'John Doe',
                'role' => 'employee'
            ]);

            $recentRecord = new TimeRecord([
                'id' => 1,
                'user_id' => $userId,
                'recorded_at' => now()->subSeconds(30) // 30 segundos atrás (menos de 1 minuto)
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn($user);

            $this->timeRecordRepository
                ->shouldReceive('getLatestByUser')
                ->with($userId)
                ->once()
                ->andReturn($recentRecord);

            expect(fn() => $this->service->recordTime($userId))
                ->toThrow(ValidationException::class);
        });
    });

    describe('getRecordsByDateRange', function () {
        it('should return records for valid date range', function () {
            $startDate = '2025-01-01';
            $endDate = '2025-01-31';
            $expectedCollection = new Collection([]);

            $this->timeRecordRepository
                ->shouldReceive('getByDateRange')
                ->with('2025-01-01 00:00:00', '2025-01-31 23:59:59')
                ->once()
                ->andReturn($expectedCollection);

            $result = $this->service->getRecordsByDateRange($startDate, $endDate);

            expect($result)->toBeInstanceOf(Collection::class);
        });

        it('should throw exception for invalid date format', function () {
            $startDate = 'invalid-date';
            $endDate = '2025-01-31';

            expect(fn() => $this->service->getRecordsByDateRange($startDate, $endDate))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when start date is after end date', function () {
            $startDate = '2025-01-31';
            $endDate = '2025-01-01';

            expect(fn() => $this->service->getRecordsByDateRange($startDate, $endDate))
                ->toThrow(ValidationException::class);
        });

        it('should return records for specific user when userId provided', function () {
            $startDate = '2025-01-01';
            $endDate = '2025-01-31';
            $userId = 1;
            $expectedCollection = new Collection([]);

            $this->timeRecordRepository
                ->shouldReceive('getByUserAndDateRange')
                ->with($userId, '2025-01-01 00:00:00', '2025-01-31 23:59:59')
                ->once()
                ->andReturn($expectedCollection);

            $result = $this->service->getRecordsByDateRange($startDate, $endDate, $userId);

            expect($result)->toBeInstanceOf(Collection::class);
        });
    });

    describe('canRecordTime', function () {
        it('should return true when user can record time', function () {
            $userId = 1;
            $user = new User([
                'id' => $userId,
                'name' => 'John Doe',
                'role' => 'employee'
            ]);

            $this->cacheService
                ->shouldReceive('getCanRecordCacheKey')
                ->with($userId)
                ->once()
                ->andReturn('cache_key');

            $this->cacheService
                ->shouldReceive('getCanRecord')
                ->with('cache_key')
                ->once()
                ->andReturn(null);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn($user);

            $this->timeRecordRepository
                ->shouldReceive('getLatestByUser')
                ->with($userId)
                ->once()
                ->andReturn(null);

            $this->cacheService
                ->shouldReceive('cacheCanRecord')
                ->with('cache_key', Mockery::type('array'), 60)
                ->once();

            $result = $this->service->canRecordTime($userId);

            expect($result['can_record'])->toBeTrue();
            expect($result['message'])->toBe('Você pode registrar seu ponto agora.');
        });

        it('should return false when user not found', function () {
            $userId = 999;

            $this->cacheService
                ->shouldReceive('getCanRecordCacheKey')
                ->with($userId)
                ->once()
                ->andReturn('cache_key');

            $this->cacheService
                ->shouldReceive('getCanRecord')
                ->with('cache_key')
                ->once()
                ->andReturn(null);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn(null);

            $result = $this->service->canRecordTime($userId);

            expect($result['can_record'])->toBeFalse();
            expect($result['message'])->toBe('Usuário não encontrado.');
        });

        it('should return false when user is not employee', function () {
            $userId = 1;
            $user = new User([
                'id' => $userId,
                'name' => 'Admin User',
                'role' => 'admin'
            ]);

            $this->cacheService
                ->shouldReceive('getCanRecordCacheKey')
                ->with($userId)
                ->once()
                ->andReturn('cache_key');

            $this->cacheService
                ->shouldReceive('getCanRecord')
                ->with('cache_key')
                ->once()
                ->andReturn(null);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn($user);

            $result = $this->service->canRecordTime($userId);

            expect($result['can_record'])->toBeFalse();
            expect($result['message'])->toBe('Apenas funcionários podem registrar ponto.');
        });
    });

    describe('getTodayRecordsByUser', function () {
        it('should return today records for user', function () {
            $userId = 1;
            $expectedCollection = new Collection([]);

            $this->cacheService
                ->shouldReceive('getTodayRecordsCacheKey')
                ->with($userId)
                ->once()
                ->andReturn('cache_key');

            $this->cacheService
                ->shouldReceive('getTodayRecords')
                ->with('cache_key')
                ->once()
                ->andReturn(null);

            $this->timeRecordRepository
                ->shouldReceive('getTodayRecordsByUser')
                ->with($userId)
                ->once()
                ->andReturn($expectedCollection);

            $this->cacheService
                ->shouldReceive('cacheTodayRecords')
                ->with('cache_key', $expectedCollection)
                ->once();

            $result = $this->service->getTodayRecordsByUser($userId);

            expect($result)->toBeInstanceOf(Collection::class);
        });
    });

    describe('getLatestRecordByUser', function () {
        it('should return latest record for user', function () {
            $userId = 1;
            $expectedRecord = new TimeRecord([
                'id' => 1,
                'user_id' => $userId,
                'recorded_at' => now()
            ]);

            $this->timeRecordRepository
                ->shouldReceive('getLatestByUser')
                ->with($userId)
                ->once()
                ->andReturn($expectedRecord);

            $result = $this->service->getLatestRecordByUser($userId);

            expect($result)->toBeInstanceOf(TimeRecord::class);
        });

        it('should return null when no records found', function () {
            $userId = 1;

            $this->timeRecordRepository
                ->shouldReceive('getLatestByUser')
                ->with($userId)
                ->once()
                ->andReturn(null);

            $result = $this->service->getLatestRecordByUser($userId);

            expect($result)->toBeNull();
        });
    });
});
