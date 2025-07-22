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

    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        
        $filters = [
            'search' => $request->input('search'),
        ];
        
        if ($user->role === 'admin') {
            $employees = $this->userService->getPaginatedEmployees($filters, $perPage);
        } else {
            $filters['manager_id'] = $user->id;
            $employees = $this->userService->getPaginatedEmployees($filters, $perPage);
        }
        
        return new UserCollection($employees);
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $addressData = $this->viaCepService->getAddressByCep($request->cep);
            
            $employeeData = array_merge($request->validated(), $addressData);
            
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

    public function update(UpdateEmployeeRequest $request, int $id)
    {
        try {
            $updateData = $request->validated();
            
            if ($request->has('cep')) {
                $addressData = $this->viaCepService->getAddressByCep($request->cep);
                $updateData = array_merge($updateData, $addressData);
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
        }
    }

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
