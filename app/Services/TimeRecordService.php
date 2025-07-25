<?php

namespace App\Services;

use App\Contracts\TimeRecordRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\TimeRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class TimeRecordService
{
    public function __construct(
        private TimeRecordRepositoryInterface $timeRecordRepository,
        private UserRepositoryInterface $userRepository
    ) {}
    
    public function recordTime(int $userId): TimeRecord
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }
        
        if ($user->role !== 'employee') {
            throw ValidationException::withMessages([
                'user' => ['Apenas funcionários podem registrar ponto.']
            ]);
        }
        
        $latestRecord = $this->timeRecordRepository->getLatestByUser($userId);
        if ($latestRecord && $latestRecord->recorded_at->diffInMinutes(now()) < 1) {
            throw ValidationException::withMessages([
                'time' => ['Você já registrou ponto recentemente. Aguarde 1 minuto.']
            ]);
        }
        
        return $this->timeRecordRepository->create([
            'user_id' => $userId,
            'recorded_at' => now()
        ]);
    }
    
    public function getRecordsByUser(int $userId): Collection
    {
        return $this->timeRecordRepository->getByUser($userId);
    }
    
    public function getRecordsByDateRange(string $startDate, string $endDate, ?int $userId = null): Collection
    {
        try {
            Carbon::createFromFormat('Y-m-d', $startDate);
            Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'dates' => ['Formato de data inválido. Use Y-m-d.']
            ]);
        }
        
        if ($startDate > $endDate) {
            throw ValidationException::withMessages([
                'dates' => ['Data inicial deve ser anterior à data final.']
            ]);
        }
        
        if ($userId) {
            return $this->timeRecordRepository->getByUserAndDateRange(
                $userId, 
                $startDate . ' 00:00:00', 
                $endDate . ' 23:59:59'
            );
        }
        
        return $this->timeRecordRepository->getByDateRange(
            $startDate . ' 00:00:00', 
            $endDate . ' 23:59:59'
        );
    }
    
    public function getPaginatedRecords(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            try {
                Carbon::createFromFormat('Y-m-d', $filters['start_date']);
                Carbon::createFromFormat('Y-m-d', $filters['end_date']);
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'dates' => ['Formato de data inválido. Use Y-m-d.']
                ]);
            }
            
            if ($filters['start_date'] > $filters['end_date']) {
                throw ValidationException::withMessages([
                    'dates' => ['Data inicial deve ser anterior à data final.']
                ]);
            }
        }
        
        return $this->timeRecordRepository->getPaginated($filters, $perPage);
    }
    
    public function getTodayRecordsByUser(int $userId): Collection
    {
        return $this->timeRecordRepository->getTodayRecordsByUser($userId);
    }
    
    public function getLatestRecordByUser(int $userId): ?TimeRecord
    {
        return $this->timeRecordRepository->getLatestByUser($userId);
    }
    
    public function getFormattedRecords(array $filters = [], int $perPage = 15): array
    {
        $records = $this->getPaginatedRecords($filters, $perPage);
        
        $formattedRecords = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'employee_name' => $record->user->name,
                'position' => $record->user->position,
                'age' => $record->user->age,
                'manager_name' => $record->user->manager ? $record->user->manager->name : 'N/A',
                'recorded_at' => $record->recorded_at->format('d/m/Y H:i:s'),
                'recorded_date' => $record->recorded_at->format('d/m/Y'),
                'recorded_time' => $record->recorded_at->format('H:i:s'),
            ];
        });
        
        return [
            'data' => $formattedRecords,
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'from' => $records->firstItem(),
                'to' => $records->lastItem(),
            ]
        ];
    }
    
    public function canRecordTime(int $userId): array
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            return [
                'can_record' => false,
                'message' => 'Usuário não encontrado.'
            ];
        }
        
        if ($user->role !== 'employee') {
            return [
                'can_record' => false,
                'message' => 'Apenas funcionários podem registrar ponto.'
            ];
        }
        
        $latestRecord = $this->timeRecordRepository->getLatestByUser($userId);
        if ($latestRecord && $latestRecord->recorded_at->diffInMinutes(now()) < 1) {
            return [
                'can_record' => false,
                'message' => 'Aguarde 1 minuto antes de registrar novamente.',
                'next_allowed_at' => $latestRecord->recorded_at->addMinutes(1)->format('H:i:s')
            ];
        }
        
        return [
            'can_record' => true,
            'message' => 'Você pode registrar seu ponto agora.'
        ];
    }
    
    public function getAllTodayRecords(): Collection
    {
        return $this->timeRecordRepository->getAllTodayRecords();
    }
}
