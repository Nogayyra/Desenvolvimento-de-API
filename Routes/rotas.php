<?php

// Diz qual método do controlador atende cada combinação de método HTTP + URL.

$metodo = $_SERVER['REQUEST_METHOD'];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Se o site estiver numa subpasta em vez da raiz, ignora essa parte da URL.
$pastaBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($pastaBase !== '' && str_starts_with($uri, $pastaBase)) {
    $uri = substr($uri, strlen($pastaBase));
}

$partes = explode('/', trim($uri, '/'));

// Só existe /api/maintenance-orders, com id opcional no final.
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
        ControladorOrdemManutencao::listar();
        break;

    case $metodo === 'GET' && $id !== null:
        ControladorOrdemManutencao::buscar((int) $id);
        break;

    case $metodo === 'POST' && $id === null:
        ControladorOrdemManutencao::criar();
        break;

    case $metodo === 'PUT' && $id !== null:
        ControladorOrdemManutencao::atualizar((int) $id);
        break;

    case $metodo === 'DELETE' && $id !== null:
        ControladorOrdemManutencao::excluir((int) $id);
        break;

    default:
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Método HTTP não permitido para esta rota.'], JSON_UNESCAPED_UNICODE);
        break;
}
