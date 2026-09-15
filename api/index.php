<?php

define('LARAVEL_START', microtime(true));

// Temporary storage and cache directories in /tmp for serverless environment
$tmpDirs = [
    '/tmp/storage/app/public',
    '/tmp/storage/app/private',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Ensure essential serverless environment variables have valid fallbacks.
// This prevents ArgumentCountError in Illuminate\Support\Manager if SESSION_DRIVER or
// APP_MAINTENANCE_DRIVER is empty or undefined in Vercel.
$defaultEnvs = [
    'APP_ENV' => 'production',
    'SESSION_DRIVER' => 'cookie',
    'APP_MAINTENANCE_DRIVER' => 'file',
    'APP_MAINTENANCE_STORE' => 'database',
    'CACHE_STORE' => 'array',
    'LOG_CHANNEL' => 'stderr',
    'LARAVEL_STORAGE_PATH' => '/tmp/storage',
    'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
];

foreach ($defaultEnvs as $key => $defaultVal) {
    $current = getenv($key);
    if ($current === false || trim((string)$current) === '') {
        putenv("{$key}={$defaultVal}");
        $_ENV[$key] = $defaultVal;
        $_SERVER[$key] = $defaultVal;
    } else {
        $_ENV[$key] = $current;
        $_SERVER[$key] = $current;
    }
}

// Copy bootstrap cache files if available
$cacheFiles = ['packages.php', 'services.php'];
foreach ($cacheFiles as $file) {
    $source = __DIR__.'/../bootstrap/cache/'.$file;
    $dest = '/tmp/bootstrap/cache/'.$file;
    if (file_exists($source) && !file_exists($dest)) {
        @copy($source, $dest);
    }
}

if (!isset($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}

// Normalize HTTPS headers for Vercel reverse proxy
if ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
    (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'vercel.app'))) {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

$appUrl = getenv('APP_URL');
if ($appUrl && str_starts_with($appUrl, 'http://') && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
    $httpsUrl = preg_replace('/^http:\/\//', 'https://', $appUrl);
    putenv("APP_URL={$httpsUrl}");
    $_ENV['APP_URL'] = $httpsUrl;
    $_SERVER['APP_URL'] = $httpsUrl;
}

require __DIR__.'/../vendor/autoload.php';

try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');
    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    error_log($e->getMessage()."\n".$e->getTraceAsString());
    http_response_code(500);
    $isDebug = (getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1' || (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true'));
    if ($isDebug) {
        echo "<pre><h3>FinTrack Serverless Exception</h3>\n";
        echo "<strong>Message:</strong> ".htmlspecialchars($e->getMessage())."\n";
        echo "<strong>File:</strong> ".htmlspecialchars($e->getFile())." : ".htmlspecialchars($e->getLine())."\n\n";
        echo "<strong>Trace:</strong>\n".htmlspecialchars($e->getTraceAsString())."</pre>";
    } else {
        echo "<h1>500 Internal Server Error</h1><p>Terjadi kesalahan server saat memproses permintaan.</p>";
    }
}