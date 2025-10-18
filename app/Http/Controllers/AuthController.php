<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="legacy-hakaton-mvp0 API",
 *   description="я мурчал пока писал эту аннотацию (тихонечко)"
 * )
 * @OA\Server(url="http://127.0.0.1:8000", description="Local server")
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth", type="http", scheme="bearer", bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    use ApiResponse;

    private function issueTokenPair(User $user): array
    {
        $guard = auth('api');

        $accessToken = $guard->login($user);
        $accessTtl   = $guard->factory()->getTTL(); 

        $refreshTtl   = (int) config('jwt.refresh_ttl');
        $refreshToken = $guard
            ->setTTL($refreshTtl)
            ->claims(['typ' => 'refresh'])
            ->fromUser($user);

        $guard->factory()->setTTL((int) config('jwt.ttl'));

        return [
            'access_token'       => $accessToken,
            'expires_in'         => $accessTtl * 60,
            'refresh_token'      => $refreshToken,
            'refresh_expires_in' => $refreshTtl * 60,
        ];
    }

    /**
     * @OA\Post(
     *   path="/api/auth/register", tags={"Auth"}, summary="Регистрация",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RegisterRequest")),
     *   @OA\Response(response=201, description="Успешно",
     *     @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
     *       @OA\Schema(@OA\Property(property="data", ref="#/components/schemas/TokenPair"))
     *     })
     *   ),
     *   @OA\Response(response=422, description="Ошибка валидации",
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponseBase"))
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'login'    => ['required','string','min:3','max:64','regex:/^[a-zA-Z0-9._-]+$/','unique:users,login'],
            'password' => ['required','string','min:8','max:128'],
        ]);

        $user = User::create([
            'login'    => $data['login'],
            'password' => Hash::make($data['password']),
        ]);

        return $this->created($this->issueTokenPair($user), 'Registered');
    }

    /**
     * @OA\Post(
     *   path="/api/auth/login", tags={"Auth"}, summary="Логин",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoginRequest")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
     *       @OA\Schema(@OA\Property(property="data", ref="#/components/schemas/TokenPair"))
     *     })
     *   ),
     *   @OA\Response(response=422, description="Неверные учётные данные",
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponseBase"))
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required','string'],
            'password' => ['required','string'],
        ]);

        if (!auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages(['login' => ['Неверные учётные данные.']]);
        }

        /** @var User $user */
        $user = auth('api')->user();
        return $this->ok($this->issueTokenPair($user), 'Logged in');
    }

    /**
     * @OA\Get(
     *   path="/api/auth/me", tags={"Auth"}, summary="Текущий пользователь",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
     *       @OA\Schema(@OA\Property(property="data", ref="#/components/schemas/User"))
     *     })
     *   ),
     *   @OA\Response(response=401, description="Неавторизован",
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponseBase"))
     * )
     */
    public function me()
    {
        return $this->ok(auth('api')->user(), 'OK');
    }

    /**
     * @OA\Post(
     *   path="/api/auth/logout", tags={"Auth"}, summary="Выход (инвалидация токенов)",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=false,
     *     @OA\JsonContent(@OA\Property(property="refresh_token", type="string"))),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiResponseBase")),
     *   @OA\Response(response=400, description="Нет токена", @OA\JsonContent(ref="#/components/schemas/ApiResponseBase"))
     * )
     */
    public function logout(Request $request)
    {
        $access  = $request->bearerToken();
        $refresh = (string) $request->input('refresh_token', '');

        if (!$access && !$refresh) {
            return $this->fail(400, 'Токен не передан');
        }

        if ($access)  { try { JWTAuth::setToken($access)->invalidate(true); }  catch (\Throwable $e) {} }
        if ($refresh) { try { JWTAuth::setToken($refresh)->invalidate(true); } catch (\Throwable $e) {} }

        return $this->ok(null, 'Logged out');
    }

    /**
     * @OA\Post(
     *   path="/api/auth/refresh", tags={"Auth"},
     *   summary="Обновление access по refresh",
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/RefreshRequest")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
     *       @OA\Schema(@OA\Property(property="data", ref="#/components/schemas/TokenPair"))
     *     })
     *   ),
     *   @OA\Response(response=400, description="Некорректный/отсутствующий токен",
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponseBase")),
     *   @OA\Response(response=401, description="Истёк/невалиден",
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponseBase"))
     * )
     */
    public function refresh(Request $request)
    {
        $token = $request->bearerToken() ?: (string) $request->input('refresh_token', '');
        if (!$token) {
            return $this->fail(400, 'Refresh token is required');
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
        } catch (TokenExpiredException $e) {
            return $this->fail(401, 'Refresh token expired');
        } catch (JWTException $e) {
            return $this->fail(400, 'Invalid refresh token');
        }

        if (($payload['typ'] ?? null) !== 'refresh') {
            return $this->fail(400, 'Provided token is not a refresh token');
        }

        $userId = $payload['sub'] ?? null;
        $user   = $userId ? User::find($userId) : null;
        if (!$user) {
            return $this->fail(404, 'User not found');
        }

        if (config('jwt.blacklist_enabled')) {
            try { JWTAuth::invalidate($token); } catch (\Throwable $e) {}
        }

        return $this->ok($this->issueTokenPair($user), 'Refreshed');
    }

    /**
     * @OA\Get(
     *   path="/api/auth/yandex/url", tags={"Auth"}, summary="Получить URL для авторизации в Яндекс",
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
     *       @OA\Schema(@OA\Property(property="data", ref="#/components/schemas/YandexAuthUrl"))
     *     })
     *   )
     * )
     */
    public function yandexUrl(Request $request)
{
    if ($code = $request->query('code')) {
        $state = $request->query('state');
        $cid   = $request->query('cid');

        $front = rtrim(
            config('services.yandex.front_redirect', env('FRONT_REDIRECT_URI', 'http://127.0.0.1:3000/Personal')),
            '/'
        );

        $qs = http_build_query(array_filter([
            'code'  => $code,
            'state' => $state,
            'cid'   => $cid,
        ], fn ($v) => $v !== null && $v !== ''));

        $redirectWithCode = $front . (str_contains($front, '?') ? '&' : '?') . $qs;

        return $this->ok([
            'code'               => $code,
            'state'              => $state,
            'cid'                => $cid,
            'redirect_with_code' => $redirectWithCode,
        ], 'OK');
    }

    $clientId = config('services.yandex.client_id');
    $redirect = config('services.yandex.redirect');               
    $scope    = config('services.yandex.scope', 'login:info');
    $state    = $request->query('state');                          

    $authUrl = 'https://oauth.yandex.ru/authorize?' . http_build_query(array_filter([
        'response_type' => 'code',
        'client_id'     => $clientId,
        'redirect_uri'  => $redirect,
        'scope'         => $scope,
        'force_confirm' => 'yes',
        'state'         => $state,
    ], fn ($v) => $v !== null && $v !== ''));

    return $this->ok(['auth_url' => $authUrl], 'OK');
}


    /**
     * @OA\Post(
     *   path="/api/auth/yandex/exchange", tags={"Auth"}, summary="Обмен кода на токены Яндекс и вход",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/YandexExchangeRequest")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponseBase"),
     *       @OA\Schema(@OA\Property(property="data", ref="#/components/schemas/TokenPair"))
     *     })
     *   ),
     *   @OA\Response(response=400, description="Ошибка обмена кода/получения профайла",
     *     @OA\JsonContent(ref="#/components/schemas/ApiResponseBase"))
     * )
     */
    public function yandexExchange(Request $request)
    {
        $code = (string) $request->input('code', '');
        if (!$code) return $this->fail(400, 'code is required');

        $cid     = config('services.yandex.client_id');
        $secret  = config('services.yandex.client_secret');
        $redir   = config('services.yandex.redirect');

        $tokenRes = Http::asForm()->post('https://oauth.yandex.ru/token', [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $cid,
            'client_secret' => $secret,
            'redirect_uri'  => $redir,
        ]);

        if (!$tokenRes->ok()) {
            return $this->fail(400, 'Yandex token exchange failed');
        }

        $accessToken = $tokenRes->json('access_token');

        $infoRes = Http::withHeaders(['Authorization' => 'OAuth ' . $accessToken])
            ->get('https://login.yandex.ru/info?format=json');

        if (!$infoRes->ok()) {
            return $this->fail(400, 'Yandex profile request failed');
        }

        $login = $infoRes->json('login');
        if (!$login) {
            return $this->fail(400, 'Yandex login not returned');
        }

        $base  = 'ya_' . Str::lower($login);
        $final = $base;
        $i = 1;
        while (User::where('login', $final)->exists()) {
            $final = $base . $i++;
        }

        $user = User::firstOrCreate(
            ['login' => $final],
            ['password' => Hash::make(Str::random(32))]
        );

        return $this->ok($this->issueTokenPair($user), 'Yandex login');
    }
}
