<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function respond(int $statusCode, string $message = 'OK', $data = null, $errors = null): JsonResponse
    {
        $payload = [
            'statusCode' => $statusCode,
            'message'    => $message,
        ];
        if (!is_null($data))   $payload['data']   = $data;
        if (!is_null($errors)) $payload['errors'] = $errors;

        return response()->json($payload, $statusCode);
    }

    protected function ok($data = null, string $message = 'OK'): JsonResponse
    {
        return $this->respond(200, $message, $data);
    }

    protected function created($data = null, string $message = 'Created'): JsonResponse
    {
        return $this->respond(201, $message, $data);
    }

    protected function error(int $statusCode, string $message, $errors = null): JsonResponse
    {
        return $this->respond($statusCode, $message, null, $errors);
    }

    // немножко легаси, я это потом уберу
    protected function success(string $message = 'OK', $data = null, int $status = 200): JsonResponse
    {
        return $this->respond($status, $message, $data);
    }
    protected function fail(int $status, string $message, $errors = null): JsonResponse
    {
        return $this->error($status, $message, $errors);
    }
}
