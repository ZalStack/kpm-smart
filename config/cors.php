<?php

$allowedOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
)));

$allowedOriginPatterns = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))
)));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | API KPM Smart (/api/v1, /api/user/v1, /api/admin/v1) dipanggil dari
    | aplikasi mobile dan mungkin dari domain berbeda. Tanpa konfigurasi ini,
    | browser akan memblokir setiap request lintas origin.
    |
    | Daftar origin diisi lewat env `CORS_ALLOWED_ORIGINS` (pisahkan dengan
    | koma), mis: "https://app.domain-anda.com,https://admin.domain-anda.com".
    |
    | Kalau env tersebut kosong, TIDAK ada origin luar yang diizinkan — hanya
    | same-origin. Jangan pernah memakai wildcard di sini: kombinasi
    | `allowed_origins => ['*']` dengan `supports_credentials => true` ditolak
    | browser dan juga longgar secara keamanan.
    |
    | Untuk origin dengan banyak subdomain, pakai `CORS_ALLOWED_ORIGIN_PATTERNS`
    | mis. "https://*.domain-anda.com".
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => $allowedOriginPatterns,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
