<?php

/**
 * router.php — Router para o servidor embutido do PHP.
 *
 * Uso: php -S localhost:8000 router.php
 *
 * Arquivos estáticos dentro de public/ (docs.html, openapi.json)
 * são servidos diretamente; todo o resto cai no front controller
 * public/index.php, permitindo rotas como /api/maintenance-orders.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$arquivo = __DIR__ . '/public' . $uri;

if ($uri !== '/' && is_file($arquivo)) {
    return false; // Deixa o servidor embutido servir o arquivo estático.
}

$_SERVER['SCRIPT_NAME'] = '/index.php';

require __DIR__ . '/public/index.php';
