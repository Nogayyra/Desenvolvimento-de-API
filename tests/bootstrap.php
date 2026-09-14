<?php

/**
 * tests/bootstrap.php
 *
 * Helpers dos testes E2E: chamadas HTTP reais contra a API
 * e preparo do banco de testes (fixapi_test).
 *
 * As variáveis de ambiente podem ser sobrescritas; os valores
 * padrão apontam para o servidor/orquestrador local.
 */

foreach (['E2E_BASE_URL' => 'http://127.0.0.1:8001', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '3306', 'DB_NAME' => 'fixapi_test', 'DB_USER' => 'root', 'DB_PASS' => ''] as $chave => $padrao) {
    if (getenv($chave) === false) {
        putenv("{$chave}={$padrao}");
    }
}

require __DIR__ . '/../Config/configuration.php';
require __DIR__ . '/../Model/Connection.php';

/** Base URL da API sob teste (ex.: http://127.0.0.1:8001). */
function e2e_base_url(): string
{
    return rtrim(getenv('E2E_BASE_URL') ?: 'http://127.0.0.1:8001', '/');
}

/**
 * Executa uma requisição HTTP real e devolve ['status' => int, 'body' => mixed].
 */
function e2e_request(string $method, string $uri, mixed $body = null, bool $rawBody = false): array
{
    $ch = curl_init(e2e_base_url() . $uri);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_HEADER => true,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody ? (string) $body : json_encode($body));
    }

    $response = curl_exec($ch);

    if ($response === false) {
        $erro = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("Falha ao conectar na API: {$erro}");
    }

    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $corpo = substr($response, $headerSize);

    return ['status' => $status, 'body' => $corpo !== '' ? json_decode($corpo, true) : null];
}

function e2e_get(string $uri): array
{
    return e2e_request('GET', $uri);
}

function e2e_post(string $uri, array $body): array
{
    return e2e_request('POST', $uri, $body);
}

function e2e_put(string $uri, array $body): array
{
    return e2e_request('PUT', $uri, $body);
}

function e2e_delete(string $uri): array
{
    return e2e_request('DELETE', $uri);
}

/**
 * (Re)cria o banco de testes aplicando o schema oficial + seeds.
 * Nunca toca no banco de desenvolvimento (fixapi).
 */
function e2e_setup_database(): void
{
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec('DROP DATABASE IF EXISTS `' . DB_NAME . '`');
    $pdo->exec('CREATE DATABASE `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');

    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');

    // Remove as linhas que apontam para o banco de desenvolvimento.
    $schema = preg_replace('/^\s*CREATE DATABASE.*$/mi', '', $schema);
    $schema = preg_replace('/^\s*USE\s+.*$/mi', '', $schema);

    foreach (array_filter(array_map('trim', explode(';', $schema))) as $stmt) {
        if ($stmt !== '' && !str_starts_with(ltrim($stmt), '--')) {
            $pdo->exec($stmt);
        }
    }
}

/** Reseta a tabela para os 5 registros de exemplo (IDs 1..5). */
function e2e_reset_table(): void
{
    $pdo = Connection::get();
    $pdo->exec('TRUNCATE TABLE maintenance_orders');
    $pdo->exec(
        "INSERT INTO maintenance_orders
            (cliente_nome, cliente_telefone, equipamento, marca, modelo, problema_relatado, diagnostico, status, valor)
        VALUES
            ('João Silva', '(71) 99999-0000', 'Notebook', 'Dell', 'Inspiron 15', 'Não liga', 'Fonte queimada', 'em_manutencao', 250.00),
            ('Maria Souza', '(71) 98888-1111', 'Desktop', 'HP', 'Pavilion', 'Muito lento', NULL, 'recebido', NULL),
            ('Carlos Pereira', '(71) 97777-2222', 'Notebook', 'Lenovo', 'IdeaPad 3', 'Tela quebrada', 'Troca de tela necessária', 'aguardando_peca', 480.00),
            ('Ana Costa', '(71) 96666-3333', 'All-in-One', 'Samsung', NULL, 'Não conecta ao Wi-Fi', 'Placa de rede com defeito', 'concluido', 150.00),
            ('Pedro Lima', '(71) 95555-4444', 'Notebook', 'Acer', 'Aspire 5', 'Superaquecendo', NULL, 'entregue', 90.00)"
    );
}
