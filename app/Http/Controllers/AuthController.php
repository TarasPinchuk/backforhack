<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="legacy-hakaton-mvp0 API",
 *   description="Антон Пересекин напиши в люти тим если заметил изменение "
 * )
 * @OA\Server(url="http://127.0.0.1:8000", description="Local server")
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/auth/register",
     *   operationId="authRegister",
     *   summary="Регистрация",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Успешно. Возвращает access_token",
     *     @OA\JsonContent(ref="#/components/schemas/AuthToken")
     *   ),
     *   @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'login'    => ['required','string','min:3','max:64','regex:/^[a-zA-Z0-9._-]+$/','unique:users,login'],
            'password' => ['required','string','min:8','max:128'],
        ]);

        $user = User::create([
            'login'    => $data['login'],
            'password' => Hash::make($data['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ], 201);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   operationId="authLogin",
     *   summary="Логин",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK. Возвращает access_token",
     *     @OA\JsonContent(ref="#/components/schemas/AuthToken")
     *   ),
     *   @OA\Response(response=422, description="Неверные учётные данные")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required','string'],
            'password' => ['required','string'],
        ]);

        if (!$token = auth('api')->attempt([
            'login' => $credentials['login'],
            'password' => $credentials['password'],
        ])) {
            throw ValidationException::withMessages([
                'login' => ['Неверные учётные данные.'],
            ]);
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/auth/me",
     *   operationId="authMe",
     *   summary="Текущий пользователь",
     *   security={{"bearerAuth": {}}},
     *   tags={"Auth"},
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(ref="#/components/schemas/User")
     *   ),
     *   @OA\Response(response=401, description="Неавторизован")
     * )
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   operationId="authLogout",
     *   summary="Выход",
     *   security={{"bearerAuth": {}}},
     *   tags={"Auth"},
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *   ),
     *   @OA\Response(response=401, description="Неавторизован")
     * )
     */
    public function logout()
    {
        auth('api')->logout(true);
        return response()->json(['message' => 'Logged out']);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/refresh",
     *   operationId="authRefresh",
     *   summary="Обновление access токена",
     *   security={{"bearerAuth": {}}},
     *   tags={"Auth"},
     *   @OA\Response(
     *     response=200,
     *     description="OK. Возвращает новый access_token",
     *     @OA\JsonContent(ref="#/components/schemas/AuthToken")
     *   ),
     *   @OA\Response(response=401, description="Неавторизован/просрочен")
     * )
     */
    public function refresh()
    {
        return response()->json([
            'access_token' => auth('api')->refresh(),
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}