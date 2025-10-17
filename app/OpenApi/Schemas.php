<?php

namespace App\OpenApi;

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
 *   @OA\Property(property="login",      type="string",  example="ZOV"),
 *   @OA\Property(property="updated_at", type="string",  example="2025-10-12T11:31:47.661000Z"),
 *   @OA\Property(property="created_at", type="string",  example="2025-10-12T11:31:47.661000Z"),
 *   @OA\Property(property="id",         type="string",  example="68eb91a3b5bc2188710e0e92")
 * )
 *
 * YandexAuthUrl 
 * @OA\Schema(
 *   schema="YandexAuthUrl",
 *   type="object",
 *   required={"auth_url"},
 *   @OA\Property(property="auth_url", type="string",
 *     example="https://oauth.yandex.ru/authorize?response_type=code&client_id=...&redirect_uri=...&scope=login:info")
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
class Schemas {}
