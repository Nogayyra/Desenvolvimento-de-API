<?php

// Lê o .env e guarda cada configuração numa constante.

function carregar_env(string $caminho): void
{
    if (!file_exists($caminho)) {
        return;
    }

    foreach (file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);

        if ($linha === '' || $linha[0] === '#') {
            continue;
        }

        $pos = strpos($linha, '=');

        if ($pos === false) {
            continue;
        }

        $chave = trim(substr($linha, 0, $pos));
        $valor = trim(substr($linha, $pos + 1));

        if (getenv($chave) === false) {
            putenv("{$chave}={$valor}");
        }
    }
}

carregar_env(__DIR__ . '/../.env');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'fixapi');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('APP_ENV', getenv('APP_ENV') ?: 'local');
