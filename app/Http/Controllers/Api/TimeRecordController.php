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

    /**
     * @OA\Post(
     *     path="/time-records",
     *     tags={"Registros de Ponto"},
     *     summary="Registrar ponto",
     *     description="Registra um novo ponto para o funcionário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Ponto registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ponto registrado com sucesso"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro ao registrar ponto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao registrar ponto"),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/time-records",
     *     tags={"Registros de Ponto"},
     *     summary="Listar registros de ponto",
     *     description="Lista registros de ponto com filtros e paginação",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Registros por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Data inicial (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Data final (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="ID do usuário (apenas admin/manager)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de registros de ponto",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/time-records/my-records",
     *     tags={"Registros de Ponto"},
     *     summary="Meus registros de ponto",
     *     description="Retorna os registros de ponto do funcionário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Registros por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Data inicial (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Data final (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registros do funcionário",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/time-records/can-record",
     *     tags={"Registros de Ponto"},
     *     summary="Verificar se pode registrar ponto",
     *     description="Verifica se o funcionário pode registrar um novo ponto",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Status da verificação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Você pode registrar seu ponto agora."),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="can_record", type="boolean", example=true),
     *             @OA\Property(property="next_allowed_at", type="string", nullable=true, example="14:30:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/time-records/today",
     *     tags={"Registros de Ponto"},
     *     summary="Registros de hoje",
     *     description="Retorna os registros de ponto do dia atual",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Registros de hoje",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registros de hoje"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido"
     *     )
     * )
     */
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
