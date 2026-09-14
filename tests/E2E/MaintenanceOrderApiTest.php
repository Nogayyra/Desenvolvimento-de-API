<?php

/**
 * tests/E2E/MaintenanceOrderApiTest.php
 *
 * Testes de ponta a ponta (E2E) da FixAPI: requisições HTTP reais
 * contra o servidor (php -S + router.php) e banco MySQL de testes.
 *
 * Execução: composer e2e
 */

use PHPUnit\Framework\TestCase;

final class MaintenanceOrderApiTest extends TestCase
{
    protected function setUp(): void
    {
        e2e_reset_table();
    }

    // ---------- GET /api/maintenance-orders ----------

    public function testListaTodasAsOrdens(): void
    {
        $res = e2e_get('/api/maintenance-orders');

        $this->assertSame(200, $res['status']);
        $this->assertIsArray($res['body']);
        $this->assertCount(5, $res['body']);
        $this->assertArrayHasKey('id', $res['body'][0]);
        $this->assertArrayHasKey('cliente_nome', $res['body'][0]);
        $this->assertArrayHasKey('status', $res['body'][0]);
    }

    public function testListaOrdensFiltrandoPorStatus(): void
    {
        $res = e2e_get('/api/maintenance-orders?status=recebido');

        $this->assertSame(200, $res['status']);
        $this->assertCount(1, $res['body']);
        $this->assertSame('recebido', $res['body'][0]['status']);
        $this->assertSame('Maria Souza', $res['body'][0]['cliente_nome']);
    }

    public function testListaOrdensComStatusInvalido(): void
    {
        $res = e2e_get('/api/maintenance-orders?status=inexistente');

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testListaOrdensOrdenadasPorCriacaoDesc(): void
    {
        $res = e2e_get('/api/maintenance-orders');

        $this->assertSame(200, $res['status']);
        $datas = array_column($res['body'], 'created_at');
        $ordenadas = $datas;
        rsort($ordenadas); // ordem não-crescente de created_at
        $this->assertSame($ordenadas, $datas);
    }

    // ---------- GET /api/maintenance-orders/{id} ----------

    public function testBuscaOrdemExistente(): void
    {
        $res = e2e_get('/api/maintenance-orders/1');

        $this->assertSame(200, $res['status']);
        $this->assertSame(1, (int) $res['body']['id']);
        $this->assertSame('João Silva', $res['body']['cliente_nome']);
    }

    public function testBuscaOrdemInexistente(): void
    {
        $res = e2e_get('/api/maintenance-orders/999');

        $this->assertSame(404, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testBuscaOrdemComIdInvalido(): void
    {
        $res = e2e_get('/api/maintenance-orders/abc');

        $this->assertSame(400, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    // ---------- POST /api/maintenance-orders ----------

    public function testCriaOrdemComSucesso(): void
    {
        $res = e2e_post('/api/maintenance-orders', [
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
        $this->assertSame('recebido', $res['body']['status']); // status padrão
    }

    public function testCriaOrdemComTodosOsCampos(): void
    {
        $res = e2e_post('/api/maintenance-orders', [
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
        $res = e2e_post('/api/maintenance-orders', ['cliente_nome' => 'Incompleto']);

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
        $this->assertArrayHasKey('detalhes', $res['body']);
        $this->assertNotEmpty($res['body']['detalhes']);
    }

    public function testCriaOrdemComStatusInvalido(): void
    {
        $res = e2e_post('/api/maintenance-orders', [
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
        $res = e2e_post('/api/maintenance-orders', [
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
        $res = e2e_request('POST', '/api/maintenance-orders', 'json inválido{', true);

        $this->assertSame(400, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    // ---------- PUT /api/maintenance-orders/{id} ----------

    public function testAtualizaOrdemComSucesso(): void
    {
        $res = e2e_put('/api/maintenance-orders/1', [
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
        $res = e2e_put('/api/maintenance-orders/2', ['status' => 'em_manutencao']);

        $this->assertSame(200, $res['status']);
        $this->assertSame('em_manutencao', $res['body']['status']);
        $this->assertSame('Maria Souza', $res['body']['cliente_nome']); // demais campos preservados
    }

    public function testAtualizaOrdemInexistente(): void
    {
        $res = e2e_put('/api/maintenance-orders/999', ['status' => 'concluido']);

        $this->assertSame(404, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testAtualizaOrdemComStatusInvalido(): void
    {
        $res = e2e_put('/api/maintenance-orders/1', ['status' => 'invalido']);

        $this->assertSame(422, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testAtualizaOrdemComIdInvalido(): void
    {
        $res = e2e_put('/api/maintenance-orders/abc', ['status' => 'concluido']);

        $this->assertSame(400, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    // ---------- DELETE /api/maintenance-orders/{id} ----------

    public function testExcluiOrdemComSucesso(): void
    {
        $res = e2e_delete('/api/maintenance-orders/3');

        $this->assertSame(204, $res['status']);
        $this->assertNull($res['body']);

        $depois = e2e_get('/api/maintenance-orders/3');
        $this->assertSame(404, $depois['status']);
    }

    public function testExcluiOrdemInexistente(): void
    {
        $res = e2e_delete('/api/maintenance-orders/999');

        $this->assertSame(404, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testExcluiOrdemComIdInvalido(): void
    {
        $res = e2e_delete('/api/maintenance-orders/abc');

        $this->assertSame(400, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    // ---------- Rotas e métodos ----------

    public function testMetodoNaoPermitido(): void
    {
        $res = e2e_request('PATCH', '/api/maintenance-orders/1', ['status' => 'concluido']);

        $this->assertSame(405, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    public function testRotaNaoEncontrada(): void
    {
        $res = e2e_get('/api/rota-inexistente');

        $this->assertSame(404, $res['status']);
        $this->assertArrayHasKey('erro', $res['body']);
    }

    // ---------- Fluxo completo CRUD ----------

    public function testFluxoCompletoCrud(): void
    {
        $criada = e2e_post('/api/maintenance-orders', [
            'cliente_nome' => 'Fluxo Completo',
            'cliente_telefone' => '(71) 91111-2222',
            'equipamento' => 'Notebook',
            'marca' => 'Lenovo',
            'problema_relatado' => 'Teste CRUD completo',
        ]);
        $this->assertSame(201, $criada['status']);
        $id = $criada['body']['id'];

        $lida = e2e_get("/api/maintenance-orders/{$id}");
        $this->assertSame(200, $lida['status']);
        $this->assertSame('Fluxo Completo', $lida['body']['cliente_nome']);

        $atualizada = e2e_put("/api/maintenance-orders/{$id}", ['status' => 'em_manutencao', 'valor' => 150.00]);
        $this->assertSame(200, $atualizada['status']);
        $this->assertSame('em_manutencao', $atualizada['body']['status']);

        $excluida = e2e_delete("/api/maintenance-orders/{$id}");
        $this->assertSame(204, $excluida['status']);

        $depois = e2e_get("/api/maintenance-orders/{$id}");
        $this->assertSame(404, $depois['status']);
    }
}
