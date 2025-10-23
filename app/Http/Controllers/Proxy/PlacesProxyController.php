<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlacesProxyController extends Controller
{
    private array $allowedOrigins = [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ];

    public function handle(Request $request, ?string $path = null)
    {
        // Базовый URL FastAPI
        $base = rtrim(config('services.places.base_url'), '/'); // напр. http://localhost:8001

        // --- Надёжно определяем, что именно проксируем: /api/{base}/{rest?} ---
        // Пример: /api/routes -> baseSegment=routes, rest=''
        //         /api/routes/123 -> baseSegment=routes, rest='123'
        //         /api/places?skip=0 -> baseSegment=places, rest=''
        $segments = $request->segments(); // ['api','routes', '123', ...] или ['api','places', ...]
        $baseSegment = $segments[1] ?? 'places'; // второй сегмент после 'api'
        $rest = implode('/', array_slice($segments, 2)); // всё после базового сегмента
        // Если Laravel передал {path} — оно приоритетнее (но часто пусто на ровном /api/routes)
        if (is_string($path) && $path !== '') {
            $rest = ltrim($path, '/');
        }

        // Итоговый путь в FastAPI
        $apiPath = $baseSegment . ($rest !== '' ? '/' . $rest : '');
        $url = $base . '/' . $apiPath;

        // CORS
        $origin = $request->headers->get('Origin', '*');
        $corsOrigin = in_array($origin, $this->allowedOrigins, true) ? $origin : '*';

        // Preflight
        if ($request->isMethod('OPTIONS')) {
            return response()->noContent(204)->withHeaders([
                'Access-Control-Allow-Origin'  => $corsOrigin,
                'Vary'                         => 'Origin',
                'Access-Control-Allow-Methods' => 'GET,POST,PATCH,PUT,DELETE,OPTIONS',
                'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers', '*'),
                'Access-Control-Max-Age'       => '86400',
            ]);
        }

        $method = strtolower($request->method());

        // Проксируем безопасные заголовки
        $forwardHeaders = collect($request->headers->all())
            ->map(fn($v) => is_array($v) ? implode(',', $v) : $v)
            ->except([
                'host','content-length','accept-encoding','connection','cookie',
                'x-forwarded-for','x-forwarded-host','x-forwarded-proto'
            ])
            ->all();

        $client = Http::withHeaders($forwardHeaders)
            ->acceptJson()
            ->timeout(10)
            ->connectTimeout(5);

        $options = ['query' => $request->query()];

        if ($request->isJson()) {
            $options['json'] = $request->json()->all();
        } elseif (in_array($method, ['post','put','patch','delete'])) {
            $options['body'] = $request->getContent();
        }

        // (временный лог, если нужно отладить)
        // \Log::info('PROXY → '.$method.' '.$url, ['query' => $options['query']]);

        $res = $client->send($method, $url, $options);

        $respHeaders = collect($res->headers())
            ->map(fn($v) => is_array($v) ? implode(',', $v) : $v)
            ->except(['transfer-encoding','content-encoding','connection'])
            ->all();

        // Гарантируем CORS
        $respHeaders['Access-Control-Allow-Origin']  = $corsOrigin;
        $respHeaders['Vary']                         = trim(($respHeaders['Vary'] ?? '') . ', Origin', ' ,');
        $respHeaders['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $respHeaders['Access-Control-Allow-Headers'] = $request->header('Access-Control-Request-Headers', '*');
        $respHeaders['Access-Control-Max-Age']       = '86400';

        return response($res->body(), $res->status())->withHeaders($respHeaders);
    }
}
