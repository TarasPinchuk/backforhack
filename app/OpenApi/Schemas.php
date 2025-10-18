<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * Базовый ответ
 * @OA\Schema(
 *   schema="ApiResponseBase",
 *   type="object",
 *   required={"statusCode","message"},
 *   @OA\Property(property="statusCode", type="integer", example=200),
 *   @OA\Property(property="message",    type="string",  example="OK")
 * )
 *
 * Пара токенов
 * @OA\Schema(
 *   schema="TokenPair",
 *   type="object",
 *   required={"access_token","expires_in","refresh_token","refresh_expires_in"},
 *   @OA\Property(property="access_token",       type="string"),
 *   @OA\Property(property="expires_in",         type="integer", example=3600),
 *   @OA\Property(property="refresh_token",      type="string"),
 *   @OA\Property(property="refresh_expires_in", type="integer", example=1209600)
 * )
 *
 * RegisterRequest
 * @OA\Schema(
 *   schema="RegisterRequest",
 *   type="object",
 *   required={"login","password"},
 *   @OA\Property(property="login",    type="string", example="ZOV"),
 *   @OA\Property(property="password", type="string", example="GOYDASVO")
 * )
 *
 * LoginRequest
 * @OA\Schema(
 *   schema="LoginRequest",
 *   type="object",
 *   required={"login","password"},
 *   @OA\Property(property="login",    type="string", example="ZOV"),
 *   @OA\Property(property="password", type="string", example="GOYDASVO")
 * )
 *
 * RefreshRequest
 * @OA\Schema(
 *   schema="RefreshRequest",
 *   type="object",
 *   @OA\Property(property="refresh_token", type="string", example="eyJhbGciOiJIUzI1...")
 * )
 *
 * Пользователь
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   @OA\Property(property="login",      type="string", example="ZOV"),
 *   @OA\Property(property="updated_at", type="string", example="2025-10-12T11:31:47.661000Z"),
 *   @OA\Property(property="created_at", type="string", example="2025-10-12T11:31:47.661000Z"),
 *   @OA\Property(property="id",         type="string", example="68eb91a3b5bc2188710e0e92")
 * )
 *
 * YandexAuthUrl
 * @OA\Schema(
 *   schema="YandexAuthUrl",
 *   type="object",
 *   required={"auth_url"},
 *   @OA\Property(
 *     property="auth_url",
 *     type="string",
 *     example="https://oauth.yandex.ru/authorize?response_type=code&client_id=74aecacf0602423f8cc9fa0e9e919d5c&redirect_uri=http%3A%2F%2F127.0.0.1%3A8000%2Fapi%2Fauth%2Fyandex%2Furl&scope=login%3Ainfo&force_confirm=yes"
 *   )
 * )
 *
 * YandexAuthCallback
 * @OA\Schema(
 *   schema="YandexAuthCallback",
 *   type="object",
 *   required={"code","redirect_with_code"},
 *   @OA\Property(property="code", type="string", example="57bpy27qfk7n7i6o"),
 *   @OA\Property(property="cid",  type="string", nullable=true, example="7kyx8crjpnqxa52zz83e9kp724"),
 *   @OA\Property(property="state", type="string", nullable=true, example="xyz123"),
 *   @OA\Property(
 *     property="redirect_with_code",
 *     type="string",
 *     format="uri",
 *     example="http://127.0.0.1:3000/Personal?code=57bpy27qfk7n7i6o&cid=7kyx8crjpnqxa52zz83e9kp724"
 *   )
 * )
 *
 * YandexExchangeRequest
 * @OA\Schema(
 *   schema="YandexExchangeRequest",
 *   type="object",
 *   required={"code"},
 *   @OA\Property(property="code", type="string", example="AQAAAAA...codefromyandex...")
 * )
 */
final class Schemas {}
