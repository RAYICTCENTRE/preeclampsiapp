<?php
// Simple router for PHP built-in server
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the request is for a real file or directory, serve it
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route to templates folder
$phpFile = __DIR__ . '/templates' . $uri . '.php';
if (file_exists($phpFile)) {
    require $phpFile;
    exit;
}

// Route to root index
if ($uri === '/') {
    require __DIR__ . '/index.php';
    exit;
}

// 404
http_response_code(404);
echo "404 Not Found";
?>