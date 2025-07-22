<?php

namespace App\Contracts;

use App\Models\TimeRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TimeRecordRepositoryInterface
{
    public function findById(int $id): ?TimeRecord;
    
    public function create(array $data): TimeRecord;
    
    public function getByUser(int $userId): Collection;
    
    public function getByDateRange(string $startDate, string $endDate): Collection;
    
    public function getByUserAndDateRange(int $userId, string $startDate, string $endDate): Collection;
    
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function getTodayRecordsByUser(int $userId): Collection;
    
    public function getLatestByUser(int $userId): ?TimeRecord;
}
