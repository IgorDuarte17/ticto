<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciais inválidas',
                'status' => 'error'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'status' => 'success',
            'user' => new AuthResource($user),
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso',
            'status' => 'success'
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $this->userService->changePassword(
                $request->user()->id,
                $request->current_password,
                $request->password
            );

            return response()->json([
                'message' => 'Senha alterada com sucesso',
                'status' => 'success'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro ao alterar senha',
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('manager');
        
        return response()->json([
            'message' => 'Dados do usuário autenticado',
            'status' => 'success',
            'user' => new UserResource($user)
        ]);
    }
}
