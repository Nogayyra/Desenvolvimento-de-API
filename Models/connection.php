<?php

// Lê o .env e guarda cada configuração no ambiente.

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
