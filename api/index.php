<?php
require __DIR__.'/../vendor/autoload.php';
echo "Autoload OK<br>";

$app = require_once __DIR__.'/../bootstrap/app.php';
echo "Bootstrap OK<br>";

try {
    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    echo "<pre>";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "</pre>";
}