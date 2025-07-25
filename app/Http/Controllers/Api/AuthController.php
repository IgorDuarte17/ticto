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
}
