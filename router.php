<?php

// Uso: php -S localhost:8000 router.php
// Arquivos de public/ abrem direto; o resto cai no index.php (é o que permite o /api/...).

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$arquivo = __DIR__ . '/public' . $uri;

if ($uri !== '/' && is_file($arquivo)) {
    return false; // Deixa o servidor embutido servir o arquivo estático.
}

$_SERVER['SCRIPT_NAME'] = '/index.php';

require __DIR__ . '/public/index.php';
