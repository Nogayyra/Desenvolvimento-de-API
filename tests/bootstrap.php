<?php

// Helpers dos testes: chamadas HTTP de verdade contra a API e preparo do banco de testes.

foreach (['E2E_BASE_URL' => 'http://127.0.0.1:8001', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '3306', 'DB_NAME' => 'fixapi_test', 'DB_USER' => 'root', 'DB_PASS' => ''] as $chave => $padrao) {
    if (getenv($chave) === false) {
        putenv("{$chave}={$padrao}");
    }
}

require __DIR__ . '/../Configuracao/configuracao.php';
require __DIR__ . '/../Modelo/Conexao.php';

function url_base(): string
{
    return rtrim(getenv('E2E_BASE_URL') ?: 'http://127.0.0.1:8001', '/');
}

// Faz uma requisição HTTP de verdade e devolve o status e o corpo já convertido.
function requisitar(string $metodo, string $uri, mixed $corpo = null, bool $corpoCru = false): array
{
    $opcoes = [
        'http' => [
            'method' => $metodo,
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'ignore_errors' => true,
            'timeout' => 10,
        ],
    ];

    if ($corpo !== null) {
        $opcoes['http']['content'] = $corpoCru ? (string) $corpo : json_encode($corpo);
    }

    $resposta = @file_get_contents(url_base() . $uri, false, stream_context_create($opcoes));

    if ($resposta === false) {
        throw new RuntimeException('Falha ao conectar na API.');
    }

    // O PHP guarda os cabeçalhos da última requisição nessa variável.
    $status = 0;
    sscanf($http_response_header[0], 'HTTP/%*s %d', $status);

    return ['status' => $status, 'body' => $resposta !== '' ? json_decode($resposta, true) : null];
}

function buscar(string $uri): array
{
    return requisitar('GET', $uri);
}

function salvar(string $uri, array $corpo): array
{
    return requisitar('POST', $uri, $corpo);
}

function alterar(string $uri, array $corpo): array
{
    return requisitar('PUT', $uri, $corpo);
}

function remover(string $uri): array
{
    return requisitar('DELETE', $uri);
}

// Recria o banco de testes do zero. Não mexe no banco de desenvolvimento (fixapi).
function preparar_banco(): void
{
    $banco = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $banco->exec('DROP DATABASE IF EXISTS `' . DB_NAME . '`');
    $banco->exec('CREATE DATABASE `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $banco->exec('USE `' . DB_NAME . '`');

    $esquema = file_get_contents(__DIR__ . '/../banco_de_dados/esquema.sql');
    $esquema = preg_replace('/^\s*CREATE DATABASE.*$/mi', '', $esquema);
    $esquema = preg_replace('/^\s*USE\s+.*$/mi', '', $esquema);

    foreach (array_filter(array_map('trim', explode(';', $esquema))) as $comando) {
        if ($comando !== '' && !str_starts_with(ltrim($comando), '--')) {
            $banco->exec($comando);
        }
    }
}

// Volta a tabela para os 5 registros de exemplo.
function limpar_tabela(): void
{
    $banco = Conexao::conectar();
    $banco->exec('TRUNCATE TABLE maintenance_orders');
    $banco->exec(
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
