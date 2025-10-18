<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function respond(int $statusCode, string $message = 'OK', $data = null, $errors = null): JsonResponse
    {
        $payload = [
            'statusCode' => $statusCode,
            'message'    => $message,
        ];

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        if (is_array($data)) {
            $payload += $data;
        } elseif (!is_null($data)) {
            $payload['data'] = $data;
        }

        if (!is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json(
            $payload,
            $statusCode,
            [], 
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE 
        );;
    }

    public function ok($data = null, string $message = 'OK'): JsonResponse
    {
        return $this->respond(200, $message, $data);
    }

    public function created($data = null, string $message = 'Created'): JsonResponse
    {
        return $this->respond(201, $message, $data);
    }

    public function error(int $statusCode, string $message, $errors = null): JsonResponse
    {
        return $this->respond($statusCode, $message, null, $errors);
    }

    protected function success(string $message = 'OK', $data = null, int $status = 200): JsonResponse
    {
        return $this->respond($status, $message, $data);
    }
    protected function fail(int $status, string $message, $errors = null): JsonResponse
    {
        return $this->error($status, $message, $errors);
    }
}
