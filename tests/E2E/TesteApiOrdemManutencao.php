<?php

// Testes de ponta a ponta: HTTP de verdade contra o servidor e o banco de testes.

use PHPUnit\Framework\TestCase;

final class TesteApiOrdemManutencao extends TestCase
{
    protected function setUp(): void
    {
        limpar_tabela();
    }

    public function testListaTodasAsOrdens(): void
    {
        $res = buscar('/api/maintenance-orders');

        $this->assertSame(200, $res['status']);
        $this->assertIsArray($res['body']);
        $this->assertCount(5, $res['body']);
        $this->assertArrayHasKey('id', $res['body'][0]);
        $this->assertArrayHasKey('cliente_nome', $res['body'][0]);
        $this->assertArrayHasKey('status', $res['body'][0]);
    }

    public function testListaOrdensFiltrandoPorStatus(): void
    {
        $res = buscar('/api/maintenance-orders?status=recebido');

        $this->assertSame(200, $res['status']);
        $this->assertCount(1, $res['body']);
        $this->assertSame('recebido', $res['body'][0]['status']);
        $this->assertSame('Maria Souza', $res['body'][0]['cliente_nome']);
    }

    public function testListaOrdensComStatusInvalido(): void
    {
        $res = buscar('/api/maintenance-orders?status=inexistente');

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testListaOrdensOrdenadasPorCriacaoDesc(): void
    {
        $res = buscar('/api/maintenance-orders');

        $this->assertSame(200, $res['status']);
        $datas = array_column($res['body'], 'created_at');
        $ordenadas = $datas;
        rsort($ordenadas); // ordem não-crescente de created_at
        $this->assertSame($ordenadas, $datas);
    }

    public function testBuscaOrdemExistente(): void
    {
        $res = buscar('/api/maintenance-orders/1');

        $this->assertSame(200, $res['status']);
        $this->assertSame(1, (int) $res['body']['id']);
        $this->assertSame('João Silva', $res['body']['cliente_nome']);
    }

    public function testBuscaOrdemInexistente(): void
    {
        $res = buscar('/api/maintenance-orders/999');

        $this->assertSame(404, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testBuscaOrdemComIdInvalido(): void
    {
        $res = buscar('/api/maintenance-orders/abc');

        $this->assertSame(400, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testCriaOrdemComSucesso(): void
    {
        $res = salvar('/api/maintenance-orders', [
            'cliente_nome' => 'Teste Cliente',
            'cliente_telefone' => '(71) 91234-5678',
            'equipamento' => 'Notebook',
            'marca' => 'Dell',
            'modelo' => 'XPS 13',
            'problema_relatado' => 'Não carrega bateria',
        ]);

        $this->assertSame(201, $res['status']);
        $this->assertArrayHasKey('id', $res['body']);
        $this->assertSame('Teste Cliente', $res['body']['cliente_nome']);
        $this->assertSame('recebido', $res['body']['status']);
    }

    public function testCriaOrdemComTodosOsCampos(): void
    {
        $res = salvar('/api/maintenance-orders', [
            'cliente_nome' => 'Cliente Completo',
            'cliente_telefone' => '(71) 99999-9999',
            'equipamento' => 'Desktop',
            'marca' => 'HP',
            'modelo' => 'EliteDesk',
            'problema_relatado' => 'Não liga',
            'diagnostico' => 'Fonte queimada',
            'status' => 'em_analise',
            'valor' => 300.50,
        ]);

        $this->assertSame(201, $res['status']);
        $this->assertSame('em_analise', $res['body']['status']);
        $this->assertEquals(300.50, (float) $res['body']['valor']);
    }

    public function testCriaOrdemSemCamposObrigatorios(): void
    {
        $res = salvar('/api/maintenance-orders', ['cliente_nome' => 'Incompleto']);

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
        $this->assertArrayHasKey('detalhes', $res['body']);
        $this->assertNotEmpty($res['body']['detalhes']);
    }

    public function testCriaOrdemComStatusInvalido(): void
    {
        $res = salvar('/api/maintenance-orders', [
            'cliente_nome' => 'Cliente Teste',
            'cliente_telefone' => '(71) 99999-9999',
            'equipamento' => 'Notebook',
            'marca' => 'Dell',
            'problema_relatado' => 'Teste',
            'status' => 'status_invalido',
        ]);

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testCriaOrdemComValorInvalido(): void
    {
        $res = salvar('/api/maintenance-orders', [
            'cliente_nome' => 'Cliente Teste',
            'cliente_telefone' => '(71) 99999-9999',
            'equipamento' => 'Notebook',
            'marca' => 'Dell',
            'problema_relatado' => 'Teste',
            'valor' => 'invalido',
        ]);

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testCriaOrdemComJsonInvalido(): void
    {
        $res = requisitar('POST', '/api/maintenance-orders', 'json inválido{', true);

        $this->assertSame(400, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testAtualizaOrdemComSucesso(): void
    {
        $res = alterar('/api/maintenance-orders/1', [
            'status' => 'concluido',
            'valor' => 200.00,
            'diagnostico' => 'Memória RAM defeituosa',
        ]);

        $this->assertSame(200, $res['status']);
        $this->assertSame('concluido', $res['body']['status']);
        $this->assertEquals(200.00, (float) $res['body']['valor']);
        $this->assertSame('Memória RAM defeituosa', $res['body']['diagnostico']);
    }

    public function testAtualizaOrdemParcial(): void
    {
        $res = alterar('/api/maintenance-orders/2', ['status' => 'em_manutencao']);

        $this->assertSame(200, $res['status']);
        $this->assertSame('em_manutencao', $res['body']['status']);
        $this->assertSame('Maria Souza', $res['body']['cliente_nome']);
    }

    public function testAtualizaOrdemInexistente(): void
    {
        $res = alterar('/api/maintenance-orders/999', ['status' => 'concluido']);

        $this->assertSame(404, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testAtualizaOrdemComStatusInvalido(): void
    {
        $res = alterar('/api/maintenance-orders/1', ['status' => 'invalido']);

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testAtualizaOrdemComIdInvalido(): void
    {
        $res = alterar('/api/maintenance-orders/abc', ['status' => 'concluido']);

        $this->assertSame(400, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testAtualizaOrdemSemNenhumCampo(): void
    {
        $res = alterar('/api/maintenance-orders/1', []);

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testExcluiOrdemComSucesso(): void
    {
        $res = remover('/api/maintenance-orders/3');

        $this->assertSame(204, $res['status']);
        $this->assertNull($res['body']);

        $depois = buscar('/api/maintenance-orders/3');
        $this->assertSame(404, $depois['status']);
    }

    public function testExcluiOrdemInexistente(): void
    {
        $res = remover('/api/maintenance-orders/999');

        $this->assertSame(404, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testExcluiOrdemComIdInvalido(): void
    {
        $res = remover('/api/maintenance-orders/abc');

        $this->assertSame(400, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testMetodoNaoPermitido(): void
    {
        $res = requisitar('PATCH', '/api/maintenance-orders/1', ['status' => 'concluido']);

        $this->assertSame(405, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testRotaNaoEncontrada(): void
    {
        $res = buscar('/api/rota-inexistente');

        $this->assertSame(404, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testFluxoCompletoCrud(): void
    {
        $criada = salvar('/api/maintenance-orders', [
            'cliente_nome' => 'Fluxo Completo',
            'cliente_telefone' => '(71) 91111-2222',
            'equipamento' => 'Notebook',
            'marca' => 'Lenovo',
            'problema_relatado' => 'Teste CRUD completo',
        ]);
        $this->assertSame(201, $criada['status']);
        $id = $criada['body']['id'];

        $lida = buscar("/api/maintenance-orders/{$id}");
        $this->assertSame(200, $lida['status']);
        $this->assertSame('Fluxo Completo', $lida['body']['cliente_nome']);

        $atualizada = alterar("/api/maintenance-orders/{$id}", ['status' => 'em_manutencao', 'valor' => 150.00]);
        $this->assertSame(200, $atualizada['status']);
        $this->assertSame('em_manutencao', $atualizada['body']['status']);

        $excluida = remover("/api/maintenance-orders/{$id}");
        $this->assertSame(204, $excluida['status']);

        $depois = buscar("/api/maintenance-orders/{$id}");
        $this->assertSame(404, $depois['status']);
    }
}
