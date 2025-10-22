<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlacesProxyController extends Controller
{
    public function handle(Request $request, ?string $path = null)
    {
        // Куда проксируем
        $base = rtrim(config('services.places.base_url'), '/');

        // Мы хотим маппить /api/places -> /places, /api/places/{...} -> /places/{...}
        $apiPath = 'places' . ($path ? '/'.ltrim($path, '/') : '');
        $url = $base . '/' . $apiPath;

        // Метод исходного запроса
        $method = strtolower($request->method());

        // Заголовки для проксирования (без hop-by-hop)
        $forwardHeaders = collect($request->headers->all())
            ->map(fn($v) => is_array($v) ? implode(',', $v) : $v)
            ->except([
                'host','content-length','accept-encoding','connection','cookie',
                'x-forwarded-for','x-forwarded-host','x-forwarded-proto'
            ])
            ->all();

        $client = Http::withHeaders($forwardHeaders)->acceptJson();

        // Опции запроса: query + тело
        $options = ['query' => $request->query()];

        if ($request->isJson()) {
            $options['json'] = $request->json()->all();
        } elseif (in_array($method, ['post','put','patch','delete'])) {
            // Передадим как сырой body (например, для form-urlencoded)
            $options['body'] = $request->getContent();
        }

        // Отправляем исходным методом на FastAPI
        $res = $client->send($method, $url, $options);

        // Возвращаем ответ как есть (со статусом и безопасными заголовками)
        $respHeaders = collect($res->headers())
            ->map(fn($v) => is_array($v) ? implode(',', $v) : $v)
            ->except(['transfer-encoding','content-encoding','connection'])
            ->all();

        return response($res->body(), $res->status())->withHeaders($respHeaders);
    }
}
