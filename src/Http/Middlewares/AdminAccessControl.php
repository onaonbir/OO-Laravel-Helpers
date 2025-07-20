<?php

namespace OnaOnbir\OOLaravelHelpers\Http\Middlewares;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AdminAccessControl
{
    private const DEFAULT_SECRET_KEY = 'i283srWPjPquTpaRp0gBesbokitERnC8Bo6JgbED1huDEsYNcd';

    private const DEFAULT_CACHE_TIMEOUT = 30; // minutes

    private const IP_CACHE_PREFIX = 'admin_access_ip_';

    private const KEY_CACHE_PREFIX = 'admin_access_key_';

    /**
     * Handle an incoming request.
     *
     * @param  string|null  $mode  API veya WEB modu (api|web)
     */
    public function handle(Request $request, Closure $next, ?string $mode = null): SymfonyResponse
    {
        $clientIp = $this->getClientIp($request);
        $accessMode = $this->determineAccessMode($request, $mode);

        // 1. IP cache kontrolü
        if ($this->isIpCached($clientIp)) {
            $this->logAccess('IP cache hit', $clientIp);

            return $next($request);
        }

        // 2. IP whitelist kontrolü
        if ($this->isIpWhitelisted($clientIp)) {
            $this->cacheIpAccess($clientIp);
            $this->logAccess('IP whitelist approved', $clientIp);

            return $next($request);
        }

        // 3. Secret key cache kontrolü
        if ($this->isSecretKeyCached($clientIp)) {
            $this->logAccess('Secret key cache hit', $clientIp);

            return $next($request);
        }

        // 4. Secret key doğrulaması
        $secretKey = $this->getSecretKeyFromRequest($request);
        if ($this->isValidSecretKey($secretKey)) {
            $this->cacheSecretKeyAccess($clientIp);
            $this->logAccess('Secret key approved', $clientIp);

            // Web modunda key'i URL'den temizle
            if ($accessMode === 'web') {
                return $this->redirectWithoutKey($request);
            }

            return $next($request);
        }

        // 5. Erişim reddedildi
        $this->logAccessDenied($request, $clientIp);

        return $this->createAccessDeniedResponse($accessMode);
    }

    /**
     * Client IP adresini güvenli şekilde al
     */
    private function getClientIp(Request $request): string
    {
        // Proxy'ler arkasındaki gerçek IP'yi almaya çalış
        $ip = $request->header('X-Forwarded-For')
            ?? $request->header('X-Real-IP')
            ?? $request->ip();

        // İlk IP'yi al (comma separated olabilir)
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }

        return $ip;
    }

    /**
     * Erişim modunu belirle (API mi Web mi)
     */
    private function determineAccessMode(Request $request, ?string $mode): string
    {
        if ($mode) {
            return strtolower($mode);
        }

        // Content-Type veya Accept header'ına göre otomatik algıla
        if ($request->expectsJson() || $request->isJson()) {
            return 'api';
        }

        // Route prefix'ine göre algıla
        if (str_starts_with($request->path(), 'api/')) {
            return 'api';
        }

        return 'web';
    }

    /**
     * IP cache kontrolü
     */
    private function isIpCached(string $clientIp): bool
    {
        return Cache::has(self::IP_CACHE_PREFIX.$clientIp);
    }

    /**
     * IP whitelist kontrolü
     */
    private function isIpWhitelisted(string $clientIp): bool
    {
        $allowedIps = $this->getConfig('allowed_ips', []);

        return ! empty($allowedIps) && in_array($clientIp, $allowedIps, true);
    }

    /**
     * Secret key cache kontrolü
     */
    private function isSecretKeyCached(string $clientIp): bool
    {
        return Cache::has(self::KEY_CACHE_PREFIX.$clientIp);
    }

    /**
     * Request'ten secret key'i al
     */
    private function getSecretKeyFromRequest(Request $request): ?string
    {
        // Query parameter, header veya form data'dan al
        return $request->get('key')
            ?? $request->header('X-Admin-Key')
            ?? $request->input('admin_key');
    }

    /**
     * Secret key doğrulaması
     */
    private function isValidSecretKey(?string $secretKey): bool
    {
        if (empty($secretKey)) {
            return false;
        }

        $validSecretKey = $this->getConfig('secret_key', self::DEFAULT_SECRET_KEY);

        return hash_equals($validSecretKey, $secretKey);
    }

    /**
     * IP erişimini cache'le
     */
    private function cacheIpAccess(string $clientIp): void
    {
        $timeout = $this->getConfig('cache_timeout', self::DEFAULT_CACHE_TIMEOUT);
        Cache::put(
            self::IP_CACHE_PREFIX.$clientIp,
            true,
            now()->addMinutes($timeout)
        );
    }

    /**
     * Secret key erişimini cache'le
     */
    private function cacheSecretKeyAccess(string $clientIp): void
    {
        $timeout = $this->getConfig('cache_timeout', self::DEFAULT_CACHE_TIMEOUT);
        Cache::put(
            self::KEY_CACHE_PREFIX.$clientIp,
            true,
            now()->addMinutes($timeout)
        );
    }

    /**
     * Key olmadan redirect et (web modu için)
     */
    private function redirectWithoutKey(Request $request): SymfonyResponse
    {
        $url = $request->url();
        $queryParams = $request->query();

        // 'key' parametresini kaldır
        unset($queryParams['key'], $queryParams['admin_key']);

        if (! empty($queryParams)) {
            $url .= '?'.http_build_query($queryParams);
        }

        return redirect()->to($url);
    }

    /**
     * Erişim reddedildi response'u oluştur
     */
    private function createAccessDeniedResponse(string $mode): SymfonyResponse
    {
        if ($mode === 'api') {
            return new JsonResponse([
                'error' => 'Access denied',
                'message' => 'You do not have permission to access this resource.',
                'code' => 'ADMIN_ACCESS_DENIED',
            ], Response::HTTP_FORBIDDEN);
        }

        // Web modu için 404 döndür (güvenlik için)
        abort(Response::HTTP_NOT_FOUND);
    }

    /**
     * Başarılı erişimi logla
     */
    private function logAccess(string $reason, string $clientIp): void
    {
        Log::info('Admin access granted', [
            'reason' => $reason,
            'ip' => $clientIp,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Reddedilen erişimi logla
     */
    private function logAccessDenied(Request $request, string $clientIp): void
    {
        Log::warning('Admin access denied', [
            'ip' => $clientIp,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Konfigürasyon değeri al
     */
    private function getConfig(string $key, mixed $default = null): mixed
    {
        return config("oo-laravel-helpers.admin_access_middleware.{$key}", $default);
    }
}
