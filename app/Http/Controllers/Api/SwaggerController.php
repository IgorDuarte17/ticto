<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Ticto API",
 *     version="1.0.0",
 *     description="API para controle de ponto eletrônico",
 *     @OA\Contact(
 *         email="admin@ticto.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8080/api",
 *     description="Servidor de desenvolvimento"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Endpoints para autenticação de usuários"
 * )
 * 
 * @OA\Tag(
 *     name="Funcionários",
 *     description="Gerenciamento de funcionários"
 * )
 * 
 * @OA\Tag(
 *     name="Registros de Ponto",
 *     description="Controle de registros de ponto"
 * )
 * 
 * @OA\Tag(
 *     name="Utilitários",
 *     description="Endpoints utilitários"
 * )
 */
class SwaggerController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
