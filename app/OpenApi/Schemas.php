<?php

namespace App\OpenApi;

/**
 * @OA\Schema(
 *   schema="AuthToken",
 *   type="object",
 *   required={"access_token","token_type","expires_in"},
 *   @OA\Property(property="access_token", type="string"),
 *   @OA\Property(property="token_type",   type="string", example="bearer"),
 *   @OA\Property(property="expires_in",   type="integer", example=3600)
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
 *   schema="User",
 *   type="object",
 *   required={"login","created_at","updated_at","id"},
 *   @OA\Property(property="login", type="string", example="ZOV"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-12T11:31:47.661000Z"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-12T11:31:47.661000Z"),
 *	 @OA\Property(
 *     property="id",
 *     type="string",
 *     description="Строковый ObjectId пользователя",
 *     example="68eb91a3b5bc2188710e0e92"
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="MessageResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="Logged out")
 * )
 */
class Schemas {}