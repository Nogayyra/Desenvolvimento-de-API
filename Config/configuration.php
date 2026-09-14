<?php

/**
 * configuration.php
 *
 * Responsabilidade única: carregar o arquivo .env e expor as
 * configurações da aplicação como constantes.
 */

function fixapi_load_env(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $linhas = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '' || str_starts_with($linha, '#')) {
            continue;
        }

        if (!str_contains($linha, '=')) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor);

        if (getenv($chave) === false) {
            putenv("{$chave}={$valor}");
        }
    }
}

fixapi_load_env(__DIR__ . '/../.env');

// ---------- Banco de dados ----------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'fixapi');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ---------- Aplicação ----------
define('APP_NAME', 'FixAPI');
define('APP_ENV', getenv('APP_ENV') ?: 'local');
