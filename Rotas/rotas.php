<?php

/**
 * Rotas/rotas.php
 *
 * Responsabilidade única: mapear método HTTP + URI para o método
 * correto do Controlador. Não contém regras de negócio.
 */

$metodo = $_SERVER['REQUEST_METHOD'];

// Remove a query string e barras finais para simplificar o match.
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Detecta o subdiretório caso a API não esteja na raiz do domínio.
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir));
}

$partes = explode('/', trim($uri, '/'));

// Espera: api / maintenance-orders / {id?}
if (($partes[0] ?? '') !== 'api' || ($partes[1] ?? '') !== 'maintenance-orders') {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => 'Rota não encontrada.'], JSON_UNESCAPED_UNICODE);
    return;
}

$id = $partes[2] ?? null;

if ($id !== null && !ctype_digit($id)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => 'ID inválido.'], JSON_UNESCAPED_UNICODE);
    return;
}

switch (true) {
    case $metodo === 'GET' && $id === null:
        ControladorOrdemManutencao::index();
        break;

    case $metodo === 'GET' && $id !== null:
        ControladorOrdemManutencao::show((int) $id);
        break;

    case $metodo === 'POST' && $id === null:
        ControladorOrdemManutencao::store();
        break;

    case $metodo === 'PUT' && $id !== null:
        ControladorOrdemManutencao::update((int) $id);
        break;

    case $metodo === 'DELETE' && $id !== null:
        ControladorOrdemManutencao::destroy((int) $id);
        break;

    default:
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Método HTTP não permitido para esta rota.'], JSON_UNESCAPED_UNICODE);
        break;
}
