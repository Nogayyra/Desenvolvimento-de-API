<?php

// Tudo que envolve ordem de manutenção: validação e o SQL da tabela.

class OrdemManutencao
{
    public const STATUS_VALIDOS = [
        'recebido',
        'em_analise',
        'em_manutencao',
        'aguardando_peca',
        'concluido',
        'entregue',
        'cancelado',
    ];

    // Devolve a lista de erros (vazia = válido). Com $parcial, campo ausente não é erro (PUT).
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

        if (isset($dados['status']) && !in_array($dados['status'], self::STATUS_VALIDOS, true)) {
            $erros[] = "O campo 'status' é inválido. Valores permitidos: " . implode(', ', self::STATUS_VALIDOS) . '.';
        }

        if (isset($dados['valor']) && $dados['valor'] !== null && $dados['valor'] !== '' && !is_numeric($dados['valor'])) {
            $erros[] = "O campo 'valor' deve ser numérico.";
        }

        return $erros;
    }

    public static function listarTodas(?string $status = null): array
    {
        $banco = Conexao::conectar();

        $sql = 'SELECT * FROM maintenance_orders';
        $parametros = [];

        if (!empty($status) && in_array($status, self::STATUS_VALIDOS, true)) {
            $sql .= ' WHERE status = :status';
            $parametros['status'] = $status;
        }

        $sql .= ' ORDER BY created_at DESC';

        $consulta = $banco->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $banco = Conexao::conectar();
        $consulta = $banco->prepare('SELECT * FROM maintenance_orders WHERE id = :id LIMIT 1');
        $consulta->execute(['id' => $id]);
        $ordem = $consulta->fetch();

        return $ordem ?: null;
    }

    public static function criar(array $dados): int
    {
        $banco = Conexao::conectar();

        $consulta = $banco->prepare(
            'INSERT INTO maintenance_orders
                (cliente_nome, cliente_telefone, equipamento, marca, modelo, problema_relatado, diagnostico, status, valor)
             VALUES
                (:cliente_nome, :cliente_telefone, :equipamento, :marca, :modelo, :problema_relatado, :diagnostico, :status, :valor)'
        );

        $consulta->execute([
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

        return (int) $banco->lastInsertId();
    }

    // Monta o UPDATE só com os campos que vieram no $dados.
    public static function atualizar(int $id, array $dados): bool
    {
        $campos = [];
        $parametros = ['id' => $id];

        $permitidos = [
            'cliente_nome', 'cliente_telefone', 'equipamento', 'marca',
            'modelo', 'problema_relatado', 'diagnostico', 'status', 'valor',
        ];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $campos[] = "{$campo} = :{$campo}";
                $parametros[$campo] = $dados[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $banco = Conexao::conectar();
        $sql = 'UPDATE maintenance_orders SET ' . implode(', ', $campos) . ' WHERE id = :id';
        $consulta = $banco->prepare($sql);

        return $consulta->execute($parametros);
    }

    public static function excluir(int $id): bool
    {
        $banco = Conexao::conectar();
        $consulta = $banco->prepare('DELETE FROM maintenance_orders WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }
}
