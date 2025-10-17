<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    protected $internalDontReport = [
        UnauthorizedHttpException::class,
        AuthenticationException::class,
        ValidationException::class,
        NotFoundHttpException::class,
        MethodNotAllowedHttpException::class,
        JWTException::class,
        TokenExpiredException::class,
        TokenInvalidException::class,
    ];

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {

            if ($e instanceof ValidationException) {
                return $this->json(422, 'Ошибка валидации', null, $e->errors());
            }

            if ($e instanceof UnauthorizedHttpException) {
                $prev = $e->getPrevious();
                if ($prev instanceof TokenExpiredException) return $this->json(401, 'Токен истёк');
                if ($prev instanceof TokenInvalidException) return $this->json(401, 'Некорректный токен');
                if ($prev instanceof JWTException) {
                    $msg  = stripos($prev->getMessage(), 'not provided') !== false ? 'Токен не передан' : 'Ошибка токена';
                    $code = stripos($prev->getMessage(), 'not provided') !== false ? 400 : 401;
                    return $this->json($code, $msg);
                }
                $msg = $e->getMessage();
                if (stripos($msg, 'not provided') !== false) return $this->json(400, 'Токен не передан');
                return $this->json(401, 'Неавторизован');
            }

            if ($e instanceof AuthenticationException)     return $this->json(401, 'Неавторизован');
            if ($e instanceof NotFoundHttpException)       return $this->json(404, 'Маршрут не найден');
            if ($e instanceof MethodNotAllowedHttpException) return $this->json(405, 'Метод не поддерживается');
            if ($e instanceof TokenExpiredException)       return $this->json(401, 'Токен истёк');
            if ($e instanceof TokenInvalidException)       return $this->json(401, 'Некорректный токен');
            if ($e instanceof JWTException)                return $this->json(401, 'Ошибка токена');

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $msg    = $status >= 500 ? 'Внутренняя ошибка сервера' : ($e->getMessage() ?: 'Ошибка запроса');
                return $this->json($status, $msg);
            }

            return $this->json(500, 'Внутренняя ошибка сервера');
        }

        return parent::render($request, $e);
    }

    protected function json(int $status, string $message, $data = null, $errors = null)
    {
        $payload = ['statusCode' => $status, 'message' => $message];
        if (!is_null($data))   $payload['data']   = $data;
        if (!is_null($errors)) $payload['errors'] = $errors;
        return response()->json($payload, $status);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->json(401, 'Неавторизован');
        }
        return redirect()->guest('/');
    }
}
