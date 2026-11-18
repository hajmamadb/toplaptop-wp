<?php
/**
 * Router for PHP built-in web server (local dev only).
 * Serves existing files directly and routes everything else through WordPress.
 */
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if ($path !== '/' && file_exists($file)) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            require $file;
            return true;
        }
        return false;
    }
}
require __DIR__ . '/index.php';
