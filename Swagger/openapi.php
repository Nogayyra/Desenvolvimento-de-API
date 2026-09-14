<?php

/**
 * Swagger/openapi.php
 *
 * Anotações em PHP Attributes (padrão do pacote zircote/swagger-php)
 * que documentam os endpoints reais da API.
 *
 * Para gerar o arquivo openapi.json a partir destas anotações:
 *   1) composer require zircote/swagger-php
 *   2) vendor/bin/openapi . -o public/openapi.json
 *
 * Um openapi.json já gerado manualmente está disponível em
 * public/openapi.json para uso imediato no Swagger UI, sem
 * precisar instalar o pacote.
 */

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'FixAPI',
    version: '1.0.0',
    description: 'API REST para gerenciamento de ordens de manutenção de computadores em uma assistência técnica.'
)]
#[OA\Schema(
    schema: 'OrdemManutencao',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'cliente_nome', type: 'string', example: 'João Silva'),
        new OA\Property(property: 'cliente_telefone', type: 'string', example: '(71) 99999-0000'),
        new OA\Property(property: 'equipamento', type: 'string', example: 'Notebook'),
        new OA\Property(property: 'marca', type: 'string', example: 'Dell'),
        new OA\Property(property: 'modelo', type: 'string', nullable: true, example: 'Inspiron 15'),
        new OA\Property(property: 'problema_relatado', type: 'string', example: 'Não liga'),
        new OA\Property(property: 'diagnostico', type: 'string', nullable: true, example: 'Fonte queimada'),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['recebido', 'em_analise', 'em_manutencao', 'aguardando_peca', 'concluido', 'entregue', 'cancelado'],
            example: 'recebido'
        ),
        new OA\Property(property: 'valor', type: 'number', format: 'float', nullable: true, example: 250.00),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class DocumentacaoApi
{
    #[OA\Get(
        path: '/api/maintenance-orders',
        summary: 'Lista todas as ordens de manutenção',
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filtra as ordens por status',
                schema: new OA\Schema(type: 'string', enum: ['recebido', 'em_analise', 'em_manutencao', 'aguardando_peca', 'concluido', 'entregue', 'cancelado'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de ordens de manutenção',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/OrdemManutencao'))
            ),
        ]
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/api/maintenance-orders/{id}',
        summary: 'Busca uma ordem de manutenção pelo ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ordem encontrada', content: new OA\JsonContent(ref: '#/components/schemas/OrdemManutencao')),
            new OA\Response(response: 404, description: 'Ordem não encontrada'),
        ]
    )]
    public function show(): void
    {
    }

    #[OA\Post(
        path: '/api/maintenance-orders',
        summary: 'Cadastra uma nova ordem de manutenção',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/OrdemManutencao')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Ordem criada', content: new OA\JsonContent(ref: '#/components/schemas/OrdemManutencao')),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ]
    )]
    public function store(): void
    {
    }

    #[OA\Put(
        path: '/api/maintenance-orders/{id}',
        summary: 'Atualiza uma ordem de manutenção existente',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/OrdemManutencao')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Ordem atualizada', content: new OA\JsonContent(ref: '#/components/schemas/OrdemManutencao')),
            new OA\Response(response: 404, description: 'Ordem não encontrada'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ]
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/api/maintenance-orders/{id}',
        summary: 'Exclui uma ordem de manutenção',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Ordem excluída'),
            new OA\Response(response: 404, description: 'Ordem não encontrada'),
        ]
    )]
    public function destroy(): void
    {
    }
}
