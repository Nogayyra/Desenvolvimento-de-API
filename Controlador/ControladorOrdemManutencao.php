<?php

// Recebe o HTTP, valida, chama o modelo e responde em JSON.

class ControladorOrdemManutencao
{
    public static function listar(): void
    {
        $status = $_GET['status'] ?? null;

        if ($status !== null && !in_array($status, OrdemManutencao::STATUS_VALIDOS, true)) {
            self::responderErro(422, "Status inválido. Valores permitidos: " . implode(', ', OrdemManutencao::STATUS_VALIDOS) . '.');
            return;
        }

        $ordens = OrdemManutencao::listarTodas($status);
        self::responderJson(200, $ordens);
    }

    public static function buscar(int $id): void
    {
        $ordem = OrdemManutencao::buscarPorId($id);

        if (!$ordem) {
            self::responderErro(404, 'Ordem de manutenção não encontrada.');
            return;
        }

        self::responderJson(200, $ordem);
    }

    public static function criar(): void
    {
        $dados = self::lerCorpoJson();

        if ($dados === null) {
            self::responderErro(400, 'Corpo da requisição inválido. Envie um JSON válido.');
            return;
        }

        $erros = OrdemManutencao::validar($dados);

        if (!empty($erros)) {
            self::responderErro(422, 'Dados inválidos.', $erros);
            return;
        }

        $id = OrdemManutencao::criar($dados);
        $ordem = OrdemManutencao::buscarPorId($id);

        self::responderJson(201, $ordem);
    }

    public static function atualizar(int $id): void
    {
        $ordemExistente = OrdemManutencao::buscarPorId($id);

        if (!$ordemExistente) {
            self::responderErro(404, 'Ordem de manutenção não encontrada.');
            return;
        }

        $dados = self::lerCorpoJson();

        if ($dados === null) {
            self::responderErro(400, 'Corpo da requisição inválido. Envie um JSON válido.');
            return;
        }

        $erros = OrdemManutencao::validar($dados, parcial: true);

        if (!empty($erros)) {
            self::responderErro(422, 'Dados inválidos.', $erros);
            return;
        }

        OrdemManutencao::atualizar($id, $dados);
        $ordemAtualizada = OrdemManutencao::buscarPorId($id);

        self::responderJson(200, $ordemAtualizada);
    }

    public static function excluir(int $id): void
    {
        $ordem = OrdemManutencao::buscarPorId($id);

        if (!$ordem) {
            self::responderErro(404, 'Ordem de manutenção não encontrada.');
            return;
        }

        OrdemManutencao::excluir($id);
        self::responderJson(204, null);
    }

    private static function lerCorpoJson(): ?array
    {
        $texto = file_get_contents('php://input');

        if (trim($texto) === '') {
            return [];
        }

        $dados = json_decode($texto, true);

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
