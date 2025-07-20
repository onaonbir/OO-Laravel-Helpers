<?php

declare(strict_types=1);

return [

    'admin_access_middleware' => [
        /**
         * İzin verilen IP adresleri
         * Bu IP'lerden gelen istekler otomatik olarak onaylanır
         */
        'allowed_ips' => [
            '127.0.0.1',
            '::1', // IPv6 localhost
            // '192.168.1.100', // Örnek: Ofis IP'si
            // '10.0.0.5',      // Örnek: VPN IP'si
        ],

        /**
         * Admin erişim secret key'i
         * URL'de ?key=SECRET_KEY veya header'da X-Admin-Key ile gönderilebilir
         */
        'secret_key' => env('ADMIN_ACCESS_SECRET_KEY', 'i283srWPjPquTpaRp0gBesbokitERnC8Bo6JgbED1huDEsYNcd'),

        /**
         * Cache timeout süresi (dakika)
         * Başarılı erişimler bu süre boyunca cache'lenir
         */
        'cache_timeout' => env('ADMIN_ACCESS_CACHE_TIMEOUT', 30),

        /**
         * Rate limiting ayarları (opsiyonel - gelecekte eklenebilir)
         */
        'rate_limiting' => [
            'enabled' => env('ADMIN_ACCESS_RATE_LIMITING', false),
            'max_attempts' => env('ADMIN_ACCESS_MAX_ATTEMPTS', 5),
            'decay_minutes' => env('ADMIN_ACCESS_DECAY_MINUTES', 15),
        ],

        /**
         * Logging ayarları
         */
        'logging' => [
            'log_successful_access' => env('ADMIN_ACCESS_LOG_SUCCESS', true),
            'log_failed_attempts' => env('ADMIN_ACCESS_LOG_FAILURES', true),
            'log_channel' => env('ADMIN_ACCESS_LOG_CHANNEL', 'default'),
        ],
    ],

];
