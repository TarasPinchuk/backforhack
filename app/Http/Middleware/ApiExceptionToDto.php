<?php

namespace App\Http\Middleware;

use Closure;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class ApiExceptionToDto
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ValidationException $e) {
            return $this->json(422, 'Ошибка валидации', null, $e->errors());
        } catch (UnauthorizedHttpException $e) {
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
        } catch (AuthenticationException $e) {
            return $this->json(401, 'Неавторизован');
        } catch (NotFoundHttpException $e) {
            return $this->json(404, 'Маршрут не найден');
        } catch (MethodNotAllowedHttpException $e) {
            return $this->json(405, 'Метод не поддерживается');
        } catch (HttpExceptionInterface $e) {
            $status = $e->getStatusCode();
            $msg    = $status >= 500 ? 'Внутренняя ошибка сервера' : ($e->getMessage() ?: 'Ошибка запроса');
            return $this->json($status, $msg);
        } catch (Throwable $e) {
            return $this->json(500, 'Внутренняя ошибка сервера');
        }
    }

    private function json(int $status, string $message, $data = null, $errors = null)
    {
        $payload = ['statusCode' => $status, 'message' => $message];
        if (!is_null($data))   $payload['data']   = $data;
        if (!is_null($errors)) $payload['errors'] = $errors;

        return response()->json($payload, $status);
    }
}
