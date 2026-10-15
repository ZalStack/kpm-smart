<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * Direct root requests to public/index.php
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? ''
);

// If the requested URI exists as a public file, let the server handle it
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
