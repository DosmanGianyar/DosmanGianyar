<?php

// Endpoint pembersih OPcache & View Cache secara masif
if (function_exists('opcache_reset')) {
    opcache_reset();
    $opcacheCleared = true;
} else {
    $opcacheCleared = false;
}

$viewsPath = __DIR__ . '/../storage/framework/views';
$filesDeleted = 0;

if (is_dir($viewsPath)) {
    $files = glob($viewsPath . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
            $filesDeleted++;
        }
    }
}

$bootstrapCachePath = __DIR__ . '/../bootstrap/cache';
if (is_dir($bootstrapCachePath)) {
    $files = glob($bootstrapCachePath . '/*.php');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'opcache_cleared' => $opcacheCleared,
    'views_deleted' => $filesDeleted,
    'time' => date('Y-m-d H:i:s'),
]);
