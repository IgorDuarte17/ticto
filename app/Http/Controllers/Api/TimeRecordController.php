<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeRecordIndexRequest;
use App\Http\Requests\MyRecordsRequest;
use App\Http\Resources\TimeRecordResource;
use App\Http\Resources\TimeRecordCollection;
use App\Http\Resources\TimeRecordReportCollection;
use App\Services\TimeRecordService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimeRecordController extends Controller
{
    public function __construct(
        private TimeRecordService $timeRecordService
    ) {}

    public function store(Request $request)
    {
        try {
            $timeRecord = $this->timeRecordService->recordTime($request->user()->id);
            $timeRecord->load('user');

            return response()->json([
                'message' => 'Ponto registrado com sucesso',
                'status' => 'success',
                'data' => new TimeRecordResource($timeRecord)
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro ao registrar ponto',
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function index(TimeRecordIndexRequest $request)
    {
        try {
            $filters = array_filter($request->validated());

            $user = $request->user();
            if ($user->role === 'employee') {
                $filters['user_id'] = $user->id;
            } elseif ($user->role === 'manager') {
                $filters['manager_id'] = $user->id;
            }

            $perPage = $request->input('per_page', 15);
            $records = $this->timeRecordService->getPaginatedRecords($filters, $perPage);

            return new TimeRecordReportCollection($records);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro ao listar registros',
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function myRecords(MyRecordsRequest $request)
    {
        try {
            $userId = $request->user()->id;
            $filters = array_filter($request->validated());
            $filters['user_id'] = $userId;
            
            $perPage = $request->input('per_page', 15);
            $records = $this->timeRecordService->getPaginatedRecords($filters, $perPage);

            return new TimeRecordReportCollection($records);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro ao buscar registros',
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function canRecord(Request $request)
    {
        $result = $this->timeRecordService->canRecordTime($request->user()->id);

        return response()->json([
            'message' => $result['message'],
            'status' => $result['can_record'] ? 'success' : 'warning',
            'can_record' => $result['can_record'],
            'next_allowed_at' => $result['next_allowed_at'] ?? null,
        ]);
    }

    public function todayRecords(Request $request)
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $records = $this->timeRecordService->getAllTodayRecords();
        } else {
            $records = $this->timeRecordService->getTodayRecordsByUser($user->id);
        }

        return response()->json([
            'message' => 'Registros de hoje',
            'status' => 'success',
            'data' => TimeRecordResource::collection($records),
            'meta' => [
                'total_records' => $records->count(),
                'date' => now()->format('d/m/Y')
            ]
        ]);
    }
}
