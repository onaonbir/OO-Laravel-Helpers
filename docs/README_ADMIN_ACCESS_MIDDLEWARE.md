# Admin Access Control Middleware

Laravel uygulamalarınız için gelişmiş admin erişim kontrolü sağlayan güvenli ve esnek middleware.

## 📋 İçindekiler

- [Özellikler](#-özellikler)
- [Kurulum](#-kurulum)
- [Konfigürasyon](#-konfigürasyon)
- [Kullanım](#-kullanım)
- [API & Web Modu](#-api--web-modu)
- [Güvenlik](#-güvenlik)
- [Logging](#-logging)
- [Örnekler](#-örnekler)
- [Sorun Giderme](#-sorun-giderme)

## 🚀 Özellikler

- **Çift Katmanlı Güvenlik**: IP whitelist ve secret key kontrolü
- **Akıllı Cache Sistemi**: Performans için otomatik cache yönetimi
- **API & Web Desteği**: Farklı response formatları
- **Proxy Desteği**: Load balancer ve proxy arkasında çalışır
- **Esnek Secret Key**: Query param, header veya form data
- **Güvenli Logging**: Detaylı erişim logları
- **Timing Attack Koruması**: `hash_equals()` kullanımı

## 📦 Kurulum

### 1. Middleware'i Kaydet

`app/Http/Kernel.php` dosyasına middleware'i ekleyin:

```php
protected $middlewareAliases = [
    // ... diğer middleware'ler
    'admin.access' => \OnaOnbir\OOLaravelHelpers\Http\Middlewares\AdminAccessControl::class,
];
```

### 2. Konfigürasyon Dosyasını Yayınla

```bash
php artisan vendor:publish --provider="OnaOnbir\OOLaravelHelpers\ServiceProvider" --tag="config"
```

### 3. Environment Değişkenlerini Ayarla

`.env` dosyanıza ekleyin:

```env
# Admin Access Control
ADMIN_ACCESS_SECRET_KEY=your-super-secret-key-here
ADMIN_ACCESS_CACHE_TIMEOUT=30
ADMIN_ACCESS_LOG_SUCCESS=true
ADMIN_ACCESS_LOG_FAILURES=true
```

## ⚙️ Konfigürasyon

`config/oo-laravel-helpers.php` dosyasında ayarları düzenleyebilirsiniz:

```php
'admin_access_middleware' => [
    // İzin verilen IP adresleri
    'allowed_ips' => [
        '127.0.0.1',
        '::1',
        // '192.168.1.100', // Ofis IP'si
    ],

    // Secret key (environment'dan alınır)
    'secret_key' => env('ADMIN_ACCESS_SECRET_KEY', 'default-key'),

    // Cache süresi (dakika)
    'cache_timeout' => env('ADMIN_ACCESS_CACHE_TIMEOUT', 30),

    // Logging ayarları
    'logging' => [
        'log_successful_access' => env('ADMIN_ACCESS_LOG_SUCCESS', true),
        'log_failed_attempts' => env('ADMIN_ACCESS_LOG_FAILURES', true),
        'log_channel' => env('ADMIN_ACCESS_LOG_CHANNEL', 'default'),
    ],
],
```

## 🎯 Kullanım

### Basit Kullanım

```php
Route::middleware(['admin.access'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/users', [AdminController::class, 'users']);
});
```

### Manuel Mod Belirleme

```php
// API modu zorla
Route::middleware(['admin.access:api'])->prefix('api')->group(function () {
    Route::get('/admin/stats', [ApiAdminController::class, 'stats']);
});

// Web modu zorla
Route::middleware(['admin.access:web'])->group(function () {
    Route::get('/admin/settings', [AdminController::class, 'settings']);
});
```

### Route Model Binding ile

```php
Route::middleware(['admin.access'])->group(function () {
    Route::resource('admin.users', AdminUserController::class);
});
```

## 🔄 API & Web Modu

Middleware otomatik olarak istek tipini algılar, ancak manuel olarak da belirleyebilirsiniz.

### Otomatik Algılama

Middleware şu kriterlere göre modu belirler:

1. **API Modu**:
    - `Accept: application/json` header'ı varsa
    - `Content-Type: application/json` varsa
    - Route `/api/` ile başlıyorsa

2. **Web Modu**: Diğer tüm durumlar

### Response Formatları

**API Modu (JSON)**:
```json
{
  "error": "Access denied",
  "message": "You do not have permission to access this resource.",
  "code": "ADMIN_ACCESS_DENIED"
}
```

**Web Modu**: HTTP 404 Not Found (güvenlik için)

## 🔐 Güvenlik

### Erişim Yöntemleri

1. **IP Whitelist**: Belirlenen IP'lerden otomatik erişim
2. **Secret Key**: Dinamik key ile erişim

### Secret Key Kullanımı

Üç farklı yöntemle secret key gönderebilirsiniz:

```php
// 1. Query Parameter
/admin/dashboard?key=your-secret-key

// 2. HTTP Header
X-Admin-Key: your-secret-key

// 3. Form Data
admin_key=your-secret-key
```

### Proxy Desteği

Middleware şu header'ları kontrol eder:
- `X-Forwarded-For`
- `X-Real-IP`
- `REMOTE_ADDR`

### Cache Güvenliği

- Her IP için ayrı cache entry
- Configurable timeout süresi
- Otomatik cache temizleme

## 📊 Logging

### Log Seviyeleri

- **INFO**: Başarılı erişimler
- **WARNING**: Reddedilen erişimler

### Log Formatı

```php
// Başarılı erişim
[INFO] Admin access granted {
    "reason": "IP whitelist approved",
    "ip": "192.168.1.100",
    "timestamp": "2025-07-21T10:30:00Z"
}

// Reddedilen erişim
[WARNING] Admin access denied {
    "ip": "1.2.3.4",
    "user_agent": "Mozilla/5.0...",
    "url": "https://site.com/admin/dashboard",
    "method": "GET",
    "timestamp": "2025-07-21T10:30:00Z"
}
```

## 💡 Örnekler

### 1. Temel Admin Panel

```php
Route::middleware(['admin.access', 'auth'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::resource('users', AdminUserController::class);
    Route::resource('posts', AdminPostController::class);
});
```

### 2. API Endpoints

```php
Route::middleware(['admin.access:api'])->prefix('api/admin')->group(function () {
    Route::get('/analytics', [ApiController::class, 'analytics']);
    Route::post('/cache/clear', [ApiController::class, 'clearCache']);
});
```

### 3. Mixed Mode (Otomatik Algılama)

```php
Route::middleware(['admin.access'])->group(function () {
    // Web sayfaları
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    
    // AJAX endpoints (otomatik olarak API modu algılanır)
    Route::post('/admin/ajax/users', [AdminController::class, 'ajaxUsers']);
});
```

### 4. Secret Key ile Dinamik Erişim

```php
// URL'de key ile erişim
https://yoursite.com/admin/dashboard?key=your-secret-key

// JavaScript ile header kullanımı
fetch('/admin/api/data', {
    headers: {
        'X-Admin-Key': 'your-secret-key',
        'Content-Type': 'application/json'
    }
});

// Form ile key gönderimi
<form method="POST" action="/admin/action">
    <input type="hidden" name="admin_key" value="your-secret-key">
    <!-- form fields -->
</form>
```

## 🔧 Sorun Giderme

### Cache Problemleri

```bash
# Cache'i temizle
php artisan cache:clear

# Sadece admin access cache'ini temizle
php artisan tinker
>>> Cache::forget('admin_access_ip_127.0.0.1');
>>> Cache::forget('admin_access_key_127.0.0.1');
```

### Log Kontrolü

```bash
# Laravel log dosyasını izle
tail -f storage/logs/laravel.log | grep "Admin access"
```

### IP Adresi Problemleri

Gerçek IP adresinizi öğrenmek için:

```php
Route::get('/debug-ip', function (Request $request) {
    return [
        'ip()' => $request->ip(),
        'X-Forwarded-For' => $request->header('X-Forwarded-For'),
        'X-Real-IP' => $request->header('X-Real-IP'),
        'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'not set',
    ];
})->middleware(['admin.access']);
```

### Yaygın Sorunlar

1. **404 Alıyorum**:
    - IP adresiniz whitelist'te mi?
    - Secret key doğru mu?
    - Cache timeout dolmuş olabilir

2. **API'de JSON response alamıyorum**:
    - `Accept: application/json` header'ı ekleyin
    - Veya `:api` parametresi ile zorla API modu kullanın

3. **Proxy arkasında çalışmıyor**:
    - Proxy'nin `X-Forwarded-For` header'ını gönderdiğinden emin olun
    - Trusted proxy ayarlarını kontrol edin

### Debug Modu

Geliştirme ortamında daha detaylı log için:

```env
LOG_LEVEL=debug
ADMIN_ACCESS_LOG_SUCCESS=true
ADMIN_ACCESS_LOG_FAILURES=true
```

## 📈 Performance Tips

1. **Cache timeout'u ayarlayın**: Sık erişilen IP'ler için cache süresini artırın
2. **IP whitelist kullanın**: Bilinen güvenli IP'leri whitelist'e ekleyin
3. **Log seviyesini ayarlayın**: Production'da sadece error logları tutun

## 🤝 Katkıda Bulunma

1. Fork'layın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit'leyin (`git commit -m 'Add amazing feature'`)
4. Push'layın (`git push origin feature/amazing-feature`)
5. Pull Request açın

## 📄 Lisans

Bu paket MIT lisansı altında yayınlanmıştır.
