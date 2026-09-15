<?php

// Roda os testes E2E: prepara o banco, sobe o servidor, roda o PHPUnit e derruba o servidor.
// Uso: php tests/run-e2e.php

$root = dirname(__DIR__);
$port = getenv('E2E_PORT') ?: '8001';
$baseUrl = "http://127.0.0.1:{$port}";

putenv("E2E_BASE_URL={$baseUrl}");
putenv('DB_HOST=' . (getenv('DB_HOST') ?: '127.0.0.1'));
putenv('DB_PORT=' . (getenv('DB_PORT') ?: '3306'));
putenv('DB_NAME=fixapi_test');
putenv('DB_USER=' . (getenv('DB_USER') ?: 'root'));
if (getenv('DB_PASS') === false) {
    putenv('DB_PASS=');
}

require __DIR__ . '/bootstrap.php';

echo "==> Preparando banco de testes (fixapi_test)...\n";
preparar_banco();

$env = array_merge(getenv(), [
    'DB_HOST' => getenv('DB_HOST'),
    'DB_PORT' => getenv('DB_PORT'),
    'DB_NAME' => 'fixapi_test',
    'DB_USER' => getenv('DB_USER'),
    'DB_PASS' => getenv('DB_PASS'),
    'APP_ENV' => 'testing',
]);

echo "==> Subindo servidor em {$baseUrl}...\n";
$server = proc_open(
    [PHP_BINARY, '-S', "127.0.0.1:{$port}", 'router.php'],
    [0 => ['file', 'NUL', 'r'], 1 => ['file', 'NUL', 'w'], 2 => ['file', 'NUL', 'w']],
    $pipes,
    $root,
    $env
);

if (!is_resource($server)) {
    fwrite(STDERR, "Não foi possível iniciar o servidor PHP.\n");
    exit(1);
}

// Espera o servidor responder (até ~15s).
$pronto = false;
for ($i = 0; $i < 75; $i++) {
    usleep(200000);
    if (@file_get_contents($baseUrl . '/api/maintenance-orders') !== false) {
        $pronto = true;
        break;
    }
}

if (!$pronto) {
    fwrite(STDERR, "O servidor não respondeu em {$baseUrl}.\n");
    proc_terminate($server);
    proc_close($server);
    exit(1);
}

echo "==> Executando PHPUnit...\n";
passthru(PHP_BINARY . ' vendor/phpunit/phpunit/phpunit --colors=always', $exitCode);

proc_terminate($server);
proc_close($server);

exit($exitCode ?? 1);
