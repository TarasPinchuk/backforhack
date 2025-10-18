<?php

namespace App\OpenApi;

/**
 * {
 *   "statusCode": 200,
 *   "message": "OK",
 * }
 *
 * @OA\Schema(
 *   schema="ApiResponseBase",
 *   type="object",
 *   required={"statusCode","message"},
 *   @OA\Property(property="statusCode", type="integer", example=200),
 *   @OA\Property(property="message",    type="string",  example="OK")
 * )
 *
 * @OA\Schema(
 *   schema="TokenPair",
 *   type="object",
 *   required={"access_token","expires_in","refresh_token","refresh_expires_in"},
 *   @OA\Property(property="access_token",       type="string", example="eyJhbGciOi..."),
 *   @OA\Property(property="expires_in",         type="integer", example=3600),
 *   @OA\Property(property="refresh_token",      type="string", example="eyJhbGciOi..."),
 *   @OA\Property(property="refresh_expires_in", type="integer", example=1209600)
 * )
 *
 * @OA\Schema(
 *   schema="RegisterRequest",
 *   type="object",
 *   required={"login","password"},
 *   @OA\Property(property="login",    type="string", example="ZOV"),
 *   @OA\Property(property="password", type="string", example="GOYDASVO")
 * )
 *
 * @OA\Schema(
 *   schema="LoginRequest",
 *   type="object",
 *   required={"login","password"},
 *   @OA\Property(property="login",    type="string", example="ZOV"),
 *   @OA\Property(property="password", type="string", example="GOYDASVO")
 * )
 *
 * @OA\Schema(
 *   schema="RefreshRequest",
 *   type="object",
 *   @OA\Property(property="refresh_token", type="string", example="eyJhbGciOiJIUzI1...")
 * )
 *
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   required={"login","id","created_at","updated_at"},
 *   @OA\Property(property="login",      type="string", example="ZOV"),
 *   @OA\Property(property="updated_at", type="string", example="2025-10-12T11:31:47.661000Z"),
 *   @OA\Property(property="created_at", type="string", example="2025-10-12T11:31:47.661000Z"),
 *   @OA\Property(property="id",         type="string", example="68eb91a3b5bc2188710e0e92")
 * )
 *
 * @OA\Schema(
 *   schema="YandexAuthUrl",
 *   type="object",
 *   required={"auth_url"},
 *   @OA\Property(
 *     property="auth_url",
 *     type="string",
 *     example="https://oauth.yandex.ru/authorize?response_type=code&client_id=...&redirect_uri=...&scope=login:info&force_confirm=yes"
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="YandexCallbackPayload",
 *   type="object",
 *   required={"code","redirect_with_code"},
 *   @OA\Property(property="code",               type="string", example="57bpy27qfk7n7i6o"),
 *   @OA\Property(property="state",              type="string", nullable=true, example="optional-state"),
 *   @OA\Property(property="cid",                type="string", nullable=true, example="7kyx8crjpnqxa52zz83e9kp724"),
 *   @OA\Property(property="redirect_with_code", type="string", example="http://127.0.0.1:3000/Personal?code=57bpy27qfk7n7i6o&cid=7kyx8c...&state=optional-state")
 * )
 *
 *
 * @OA\Schema(
 *   schema="AuthTokenPairResponse",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
 *     @OA\Schema(ref="#/components/schemas/TokenPair")
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="MeResponse",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
 *     @OA\Schema(ref="#/components/schemas/User")
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="YandexAuthUrlResponse",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
 *     @OA\Schema(ref="#/components/schemas/YandexAuthUrl")
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="YandexCallbackResponse",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
 *     @OA\Schema(ref="#/components/schemas/YandexCallbackPayload")
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="YandexExchangeRequest",
 *   type="object",
 *   required={"code"},
 *   @OA\Property(property="code", type="string", example="57bpy27qfk7n7i6o")
 * )
 * 
 * @OA\Schema(
 *   schema="YandexUrlResponse",
 *   type="object",
 *   required={"statusCode","message"},
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
 *     @OA\Schema(
 *       type="object",
 *       @OA\Property(
 *         property="auth_url",
 *         type="string",
 *         nullable=true,
 *         example="https://oauth.yandex.ru/authorize?response_type=code&client_id=...&redirect_uri=...&scope=login:info&force_confirm=yes"
 *       ),
 *       @OA\Property(
 *         property="redirect_with_code",
 *         type="string",
 *         nullable=true,
 *         example="http://127.0.0.1:3000/Personal?code=57bpy27qfk7n7i6o&cid=7kyx8c..."
 *       )
 *     )
 *   }
 * )
 */
class Schemas {}