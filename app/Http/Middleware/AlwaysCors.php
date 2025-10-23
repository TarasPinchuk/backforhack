<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AlwaysCors
{
    public function handle(Request $request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'  => '*',
            'Vary'                         => 'Origin',
            'Access-Control-Allow-Methods' => 'GET,POST,PATCH,PUT,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers', '*'),
            'Access-Control-Max-Age'       => '86400',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->noContent(204)->withHeaders($headers);
        }

        $response = $next($request);
        foreach ($headers as $k => $v) {
            $response->headers->set($k, $v);
        }
        return $response;
    }
}
