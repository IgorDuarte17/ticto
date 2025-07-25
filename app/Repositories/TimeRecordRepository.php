<?php

namespace App\Repositories;

use App\Contracts\TimeRecordRepositoryInterface;
use App\Models\TimeRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TimeRecordRepository implements TimeRecordRepositoryInterface
{
    public function findById(int $id): ?TimeRecord
    {
        return TimeRecord::find($id);
    }
    
    public function create(array $data): TimeRecord
    {
        return TimeRecord::create($data);
    }
    
    public function getByUser(int $userId): Collection
    {
        return TimeRecord::where('user_id', $userId)
                         ->with('user')
                         ->orderBy('recorded_at', 'desc')
                         ->get();
    }
    
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return TimeRecord::whereBetween('recorded_at', [$startDate, $endDate])
                         ->with(['user', 'user.manager'])
                         ->orderBy('recorded_at', 'desc')
                         ->get();
    }
    
    public function getByUserAndDateRange(int $userId, string $startDate, string $endDate): Collection
    {
        return TimeRecord::where('user_id', $userId)
                         ->whereBetween('recorded_at', [$startDate, $endDate])
                         ->with('user')
                         ->orderBy('recorded_at', 'desc')
                         ->get();
    }
    
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TimeRecord::with(['user', 'user.manager']);
        
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (isset($filters['manager_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('manager_id', $filters['manager_id']);
            });
        }
        
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('recorded_at', [
                $filters['start_date'] . ' 00:00:00',
                $filters['end_date'] . ' 23:59:59'
            ]);
        }
        
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }
        
        return $query->orderBy('recorded_at', 'desc')->paginate($perPage);
    }
    
    public function getTodayRecordsByUser(int $userId): Collection
    {
        $today = Carbon::today();
        
        return TimeRecord::where('user_id', $userId)
                         ->whereDate('recorded_at', $today)
                         ->orderBy('recorded_at', 'desc')
                         ->get();
    }
    
    public function getLatestByUser(int $userId): ?TimeRecord
    {
        return TimeRecord::where('user_id', $userId)
                         ->orderBy('recorded_at', 'desc')
                         ->first();
    }
    
    public function getAllTodayRecords(): Collection
    {
        $today = Carbon::today();
        
        return TimeRecord::whereDate('recorded_at', $today)
                         ->with(['user', 'user.manager'])
                         ->orderBy('recorded_at', 'desc')
                         ->get();
    }
}
