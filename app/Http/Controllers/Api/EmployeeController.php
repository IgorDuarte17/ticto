<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Requests\SearchCepRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\AddressResource;
use App\Services\UserService;
use App\Services\ViaCepService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function __construct(
        private UserService $userService,
        private ViaCepService $viaCepService
    ) {}

    /**
     * @OA\Get(
     *     path="/employees",
     *     tags={"Funcionários"},
     *     summary="Listar funcionários",
     *     description="Lista funcionários com filtros e paginação",
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
     *         description="Funcionários por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por nome ou email",
     *         required=false,
     *         @OA\Schema(type="string", example="João")
     *     ),
     *     @OA\Parameter(
     *         name="position",
     *         in="query",
     *         description="Filtrar por cargo",
     *         required=false,
     *         @OA\Schema(type="string", example="developer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de funcionários",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        
        $filters = [
            'search' => $request->input('search'),
            'position' => $request->input('position'),
        ];
        
        if ($user->role === 'admin') {
            $employees = $this->userService->getPaginatedEmployees($filters, $perPage);
        } else {
            $filters['manager_id'] = $user->id;
            $employees = $this->userService->getPaginatedEmployees($filters, $perPage);
        }
        
        return new UserCollection($employees);
    }

    /**
     * @OA\Post(
     *     path="/employees",
     *     tags={"Funcionários"},
     *     summary="Criar funcionário",
     *     description="Cria um novo funcionário no sistema",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","cpf","position","birth_date"},
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@exemplo.com"),
     *             @OA\Property(property="cpf", type="string", example="123.456.789-00"),
     *             @OA\Property(property="position", type="string", example="developer"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
     *             @OA\Property(property="cep", type="string", example="01234-567"),
     *             @OA\Property(property="street", type="string", example="Rua das Flores"),
     *             @OA\Property(property="number", type="string", example="123"),
     *             @OA\Property(property="complement", type="string", example="Apto 45"),
     *             @OA\Property(property="neighborhood", type="string", example="Centro"),
     *             @OA\Property(property="city", type="string", example="São Paulo"),
     *             @OA\Property(property="state", type="string", example="SP"),
     *             @OA\Property(property="manager_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Funcionário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Funcionário criado com sucesso"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
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
    public function store(StoreEmployeeRequest $request)
    {
        try {
            $employeeData = $request->validated();
            
            if (empty($employeeData['street'])) {
                $addressData = $this->viaCepService->getAddressByCep($request->cep);
                $employeeData = array_merge($employeeData, $addressData);
            }
            
            $employee = $this->userService->createEmployee(
                $employeeData,
                $request->user()->id
            );

            return response()->json([
                'message' => 'Funcionário criado com sucesso',
                'status' => 'success',
                'data' => new UserResource($employee)
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro ao criar funcionário',
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/employees/{id}",
     *     tags={"Funcionários"},
     *     summary="Visualizar funcionário",
     *     description="Retorna os dados de um funcionário específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do funcionário",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Funcionário encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Funcionário encontrado"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Sem permissão",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sem permissão para visualizar este funcionário"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Funcionário não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Funcionário não encontrado"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     )
     * )
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        
        try {
            $employee = $this->userService->findById($id);
            
            if (!$employee) {
                return response()->json([
                    'message' => 'Funcionário não encontrado',
                    'status' => 'error'
                ], 404);
            }
            
            if ($user->role !== 'admin' && $employee->manager_id !== $user->id) {
                return response()->json([
                    'message' => 'Sem permissão para visualizar este funcionário',
                    'status' => 'error'
                ], 403);
            }
            
            $employee->load('manager');
            
            return response()->json([
                'message' => 'Funcionário encontrado',
                'status' => 'success',
                'data' => new UserResource($employee)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar funcionário',
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/employees/{id}",
     *     tags={"Funcionários"},
     *     summary="Atualizar funcionário",
     *     description="Atualiza os dados de um funcionário existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do funcionário",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@exemplo.com"),
     *             @OA\Property(property="cpf", type="string", example="123.456.789-00"),
     *             @OA\Property(property="position", type="string", example="developer"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
     *             @OA\Property(property="cep", type="string", example="01234-567"),
     *             @OA\Property(property="street", type="string", example="Rua das Flores"),
     *             @OA\Property(property="number", type="string", example="123"),
     *             @OA\Property(property="complement", type="string", example="Apto 45"),
     *             @OA\Property(property="neighborhood", type="string", example="Centro"),
     *             @OA\Property(property="city", type="string", example="São Paulo"),
     *             @OA\Property(property="state", type="string", example="SP"),
     *             @OA\Property(property="manager_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Funcionário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Funcionário atualizado com sucesso"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Funcionário não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Funcionário não encontrado"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar funcionário"),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdateEmployeeRequest $request, int $id)
    {
        try {
            $updateData = $request->validated();
            
            $employee = $this->userService->findById($id);
            if (!$employee) {
                return response()->json([
                    'message' => 'Funcionário não encontrado',
                    'status' => 'error'
                ], 404);
            }
            
            $shouldFetchAddress = empty($updateData['street']) || 
                                 ($request->has('cep') && $request->cep !== $employee->cep);
            
            if ($shouldFetchAddress && $request->has('cep')) {
                try {
                    $addressData = $this->viaCepService->getAddressByCep($request->cep);
                    if (empty($updateData['street'])) $updateData['street'] = $addressData['street'];
                    if (empty($updateData['neighborhood'])) $updateData['neighborhood'] = $addressData['neighborhood'];
                    if (empty($updateData['city'])) $updateData['city'] = $addressData['city'];
                    if (empty($updateData['state'])) $updateData['state'] = $addressData['state'];
                } catch (\Exception $e) {
                }
            }
            
            $employee = $this->userService->updateEmployee($id, $updateData);
            $employee->load('manager');

            return response()->json([
                'message' => 'Funcionário atualizado com sucesso',
                'status' => 'success',
                'data' => new UserResource($employee)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro ao atualizar funcionário',
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor',
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/employees/{id}",
     *     tags={"Funcionários"},
     *     summary="Deletar funcionário",
     *     description="Remove um funcionário do sistema",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do funcionário",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Funcionário deletado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Funcionário deletado com sucesso"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro ao deletar funcionário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao deletar funcionário"),
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
    public function destroy(Request $request, int $id)
    {
        try {
            $this->userService->deleteEmployee($id);

            return response()->json([
                'message' => 'Funcionário deletado com sucesso',
                'status' => 'success'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro ao deletar funcionário',
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/search-cep",
     *     tags={"Funcionários"},
     *     summary="Buscar endereço por CEP",
     *     description="Busca dados de endereço através do CEP para preenchimento automático",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cep"},
     *             @OA\Property(property="cep", type="string", example="01234-567", description="CEP para busca")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Endereço encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Endereço encontrado"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cep", type="string", example="01234-567"),
     *                 @OA\Property(property="street", type="string", example="Rua das Flores"),
     *                 @OA\Property(property="neighborhood", type="string", example="Centro"),
     *                 @OA\Property(property="city", type="string", example="São Paulo"),
     *                 @OA\Property(property="state", type="string", example="SP")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="CEP inválido ou não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao buscar CEP"),
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
    public function searchCep(SearchCepRequest $request)
    {
        try {
            $addressData = $this->viaCepService->getAddressByCep($request->cep);

            return response()->json([
                'message' => 'Endereço encontrado',
                'status' => 'success',
                'data' => new AddressResource($addressData)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro ao buscar CEP',
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
