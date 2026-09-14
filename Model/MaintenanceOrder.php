<?php

/**
 * MaintenanceOrder
 *
 * Responsabilidade: representar a ordem de manutenção e cuidar de
 * todas as consultas ao banco (listar, buscar, criar, atualizar, excluir).
 * Não conhece HTTP nem JSON.
 */
class MaintenanceOrder
{
    public const STATUSES = [
        'recebido',
        'em_analise',
        'em_manutencao',
        'aguardando_peca',
        'concluido',
        'entregue',
        'cancelado',
    ];

    /**
     * Valida os dados recebidos. Retorna um array de erros (vazio se ok).
     * $parcial = true ignora campos ausentes (usado no PUT com atualização parcial).
     */
    public static function validar(array $dados, bool $parcial = false): array
    {
        $erros = [];

        $obrigatorios = ['cliente_nome', 'cliente_telefone', 'equipamento', 'marca', 'problema_relatado'];

        foreach ($obrigatorios as $campo) {
            $presente = array_key_exists($campo, $dados) && trim((string) $dados[$campo]) !== '';

            if (!$presente && !$parcial) {
                $erros[] = "O campo '{$campo}' é obrigatório.";
            }
        }

        if (isset($dados['cliente_nome']) && mb_strlen((string) $dados['cliente_nome']) > 150) {
            $erros[] = "O campo 'cliente_nome' deve ter no máximo 150 caracteres.";
        }

        if (isset($dados['status']) && !in_array($dados['status'], self::STATUSES, true)) {
            $erros[] = "O campo 'status' é inválido. Valores permitidos: " . implode(', ', self::STATUSES) . '.';
        }

        if (isset($dados['valor']) && $dados['valor'] !== null && $dados['valor'] !== '' && !is_numeric($dados['valor'])) {
            $erros[] = "O campo 'valor' deve ser numérico.";
        }

        return $erros;
    }

    /**
     * Lista todas as ordens, com filtro opcional por status.
     */
    public static function listarTodas(?string $status = null): array
    {
        $pdo = Connection::get();

        $sql = 'SELECT * FROM maintenance_orders';
        $params = [];

        if (!empty($status) && in_array($status, self::STATUSES, true)) {
            $sql .= ' WHERE status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT * FROM maintenance_orders WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $ordem = $stmt->fetch();

        return $ordem ?: null;
    }

    public static function criar(array $dados): int
    {
        $pdo = Connection::get();

        $stmt = $pdo->prepare(
            'INSERT INTO maintenance_orders
                (cliente_nome, cliente_telefone, equipamento, marca, modelo, problema_relatado, diagnostico, status, valor)
             VALUES
                (:cliente_nome, :cliente_telefone, :equipamento, :marca, :modelo, :problema_relatado, :diagnostico, :status, :valor)'
        );

        $stmt->execute([
            'cliente_nome' => $dados['cliente_nome'],
            'cliente_telefone' => $dados['cliente_telefone'],
            'equipamento' => $dados['equipamento'],
            'marca' => $dados['marca'],
            'modelo' => $dados['modelo'] ?? null,
            'problema_relatado' => $dados['problema_relatado'],
            'diagnostico' => $dados['diagnostico'] ?? null,
            'status' => $dados['status'] ?? 'recebido',
            'valor' => $dados['valor'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Atualiza apenas os campos presentes em $dados (atualização parcial).
     */
    public static function atualizar(int $id, array $dados): bool
    {
        $campos = [];
        $params = ['id' => $id];

        $permitidos = [
            'cliente_nome', 'cliente_telefone', 'equipamento', 'marca',
            'modelo', 'problema_relatado', 'diagnostico', 'status', 'valor',
        ];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $campos[] = "{$campo} = :{$campo}";
                $params[$campo] = $dados[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $pdo = Connection::get();
        $sql = 'UPDATE maintenance_orders SET ' . implode(', ', $campos) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public static function excluir(int $id): bool
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare('DELETE FROM maintenance_orders WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
