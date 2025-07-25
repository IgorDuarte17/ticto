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

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Autenticação"},
     *     summary="Fazer login no sistema",
     *     description="Autentica um usuário e retorna um token de acesso",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@ticto.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="1|randomTokenString")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciais inválidas"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Autenticação"},
     *     summary="Fazer logout do sistema",
     *     description="Revoga o token de acesso atual do usuário",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso',
            'status' => 'success'
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/auth/change-password",
     *     tags={"Autenticação"},
     *     summary="Alterar senha do usuário",
     *     description="Altera a senha atual do usuário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password"},
     *             @OA\Property(property="current_password", type="string", format="password", example="oldpassword"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Senha alterada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Senha alterada com sucesso"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro ao alterar senha",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao alterar senha"),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Autenticação"},
     *     summary="Obter dados do usuário autenticado",
     *     description="Retorna as informações do usuário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dados do usuário autenticado"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('manager');
        
        return response()->json([
            'message' => 'Dados do usuário autenticado',
            'status' => 'success',
            'user' => new UserResource($user)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/auth/profile",
     *     tags={"Autenticação"},
     *     summary="Atualizar perfil do usuário autenticado",
     *     description="Permite ao usuário autenticado atualizar seus próprios dados",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-15"),
     *             @OA\Property(property="cpf", type="string", example="12345678901", description="Apenas para administradores"),
     *             @OA\Property(property="position", type="string", example="Desenvolvedor", description="Apenas para administradores"),
     *             @OA\Property(property="cep", type="string", example="12345678"),
     *             @OA\Property(property="street", type="string", example="Rua das Flores"),
     *             @OA\Property(property="number", type="string", example="123"),
     *             @OA\Property(property="complement", type="string", example="Apto 45"),
     *             @OA\Property(property="neighborhood", type="string", example="Centro"),
     *             @OA\Property(property="city", type="string", example="São Paulo"),
     *             @OA\Property(property="state", type="string", example="SP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Perfil atualizado com sucesso"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro de validação"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido"
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'birth_date' => 'nullable|date|before:today',
                'cep' => 'nullable|string|size:8',
                'street' => 'nullable|string|max:255',
                'number' => 'nullable|string|max:20',
                'complement' => 'nullable|string|max:255',
                'neighborhood' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|size:2',
            ];

            // Apenas administradores podem alterar CPF e cargo
            if ($user->role === 'admin') {
                $rules['cpf'] = 'nullable|string|size:11|unique:users,cpf,' . $user->id;
                $rules['position'] = 'nullable|string|max:255';
            }

            $validated = $request->validate($rules);

            $user->update($validated);
            $user->load('manager');

            return response()->json([
                'message' => 'Perfil atualizado com sucesso',
                'status' => 'success',
                'user' => new UserResource($user)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
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
}
