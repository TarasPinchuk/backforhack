<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // 422 Валидация
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'statusCode' => 422,
                    'message'    => 'Validation failed',
                    'errors'     => $e->errors(),
                ], 422);
            }
        });

        // 401 Неавторизован
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'statusCode' => 401,
                    'message'    => 'Unauthenticated',
                ], 401);
            }
        });

        // 401/403 JWT/Unauthorized
        $this->renderable(function (UnauthorizedHttpException|JWTException $e, Request $request) {
            if ($request->expectsJson()) {
                $status  = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 401;
                $message = $e->getMessage() ?: 'Unauthorized';
                return response()->json([
                    'statusCode' => $status,
                    'message'    => $message,
                ], $status);
            }
        });

        // Любые прочие HTTP-исключения (404, 405, 429 и т.д.)
        $this->renderable(function (Throwable $e, Request $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                $status  = $e->getStatusCode();
                $message = $e->getMessage() ?: (Response::$statusTexts[$status] ?? 'Error');
                return response()->json([
                    'statusCode' => $status,
                    'message'    => $message,
                ], $status);
            }

            // Фоллбек 500
            return response()->json([
                'statusCode' => 500,
                'message'    => 'Internal Server Error',
            ], 500);
        });
    }
}
