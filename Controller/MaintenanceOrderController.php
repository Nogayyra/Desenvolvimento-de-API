<?php

/**
 * MaintenanceOrderController
 *
 * Responsabilidade: receber a requisição HTTP, validar os dados
 * (via MaintenanceOrder::validar), chamar o Model e devolver a
 * resposta em JSON com o código HTTP adequado. Não contém SQL.
 */
class MaintenanceOrderController
{
    public static function index(): void
    {
        $status = $_GET['status'] ?? null;

        if ($status !== null && !in_array($status, MaintenanceOrder::STATUSES, true)) {
            self::responderErro(422, "Status inválido. Valores permitidos: " . implode(', ', MaintenanceOrder::STATUSES) . '.');
            return;
        }

        $ordens = MaintenanceOrder::listarTodas($status);
        self::responderJson(200, $ordens);
    }

    public static function show(int $id): void
    {
        $ordem = MaintenanceOrder::buscarPorId($id);

        if (!$ordem) {
            self::responderErro(404, 'Ordem de manutenção não encontrada.');
            return;
        }

        self::responderJson(200, $ordem);
    }

    public static function store(): void
    {
        $dados = self::lerCorpoJson();

        if ($dados === null) {
            self::responderErro(400, 'Corpo da requisição inválido. Envie um JSON válido.');
            return;
        }

        $erros = MaintenanceOrder::validar($dados);

        if (!empty($erros)) {
            self::responderErro(422, 'Dados inválidos.', $erros);
            return;
        }

        $id = MaintenanceOrder::criar($dados);
        $ordem = MaintenanceOrder::buscarPorId($id);

        self::responderJson(201, $ordem);
    }

    public static function update(int $id): void
    {
        $ordemExistente = MaintenanceOrder::buscarPorId($id);

        if (!$ordemExistente) {
            self::responderErro(404, 'Ordem de manutenção não encontrada.');
            return;
        }

        $dados = self::lerCorpoJson();

        if ($dados === null) {
            self::responderErro(400, 'Corpo da requisição inválido. Envie um JSON válido.');
            return;
        }

        $erros = MaintenanceOrder::validar($dados, parcial: true);

        if (!empty($erros)) {
            self::responderErro(422, 'Dados inválidos.', $erros);
            return;
        }

        MaintenanceOrder::atualizar($id, $dados);
        $ordemAtualizada = MaintenanceOrder::buscarPorId($id);

        self::responderJson(200, $ordemAtualizada);
    }

    public static function destroy(int $id): void
    {
        $ordem = MaintenanceOrder::buscarPorId($id);

        if (!$ordem) {
            self::responderErro(404, 'Ordem de manutenção não encontrada.');
            return;
        }

        MaintenanceOrder::excluir($id);
        self::responderJson(204, null);
    }

    // ---------- Helpers privados ----------

    private static function lerCorpoJson(): ?array
    {
        $raw = file_get_contents('php://input');

        if (trim($raw) === '') {
            return [];
        }

        $dados = json_decode($raw, true);

        return is_array($dados) ? $dados : null;
    }

    private static function responderJson(int $codigoHttp, mixed $dados): void
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');

        if ($dados !== null) {
            echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    private static function responderErro(int $codigoHttp, string $mensagem, array $detalhes = []): void
    {
        $corpo = ['erro' => $mensagem];

        if (!empty($detalhes)) {
            $corpo['detalhes'] = $detalhes;
        }

        self::responderJson($codigoHttp, $corpo);
    }
}
