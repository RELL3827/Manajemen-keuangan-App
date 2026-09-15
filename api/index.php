<?php

define('LARAVEL_START', microtime(true));

ini_set('display_errors', 1);
error_reporting(E_ALL);
putenv('APP_DEBUG=true');
$_ENV['APP_DEBUG'] = 'true';

$tmpDirs = [
    '/tmp/storage/app/public',
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

$cacheFiles = ['packages.php', 'services.php'];
foreach ($cacheFiles as $file) {
    $source = __DIR__.'/../bootstrap/cache/'.$file;
    $dest = '/tmp/bootstrap/cache/'.$file;
    if (file_exists($source) && !file_exists($dest)) {
        copy($source, $dest);
    }
}

putenv('LARAVEL_STORAGE_PATH=/tmp/storage');
$_ENV['LARAVEL_STORAGE_PATH'] = '/tmp/storage';
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes-v7.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

require __DIR__.'/../vendor/autoload.php';

try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    echo "<pre>Error: ".$e->getMessage()."\nFile: ".$e->getFile()."\nLine: ".$e->getLine()."</pre>";
}