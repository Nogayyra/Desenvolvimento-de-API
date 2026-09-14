# FixAPI

API REST em **PHP 8.3+ puro** para gerenciamento de ordens de manutenção de computadores em uma assistência técnica.

> Projeto acadêmico da Unidade Curricular **Desenvolvimento de APIs — 3º TI B**.

## Tecnologias

- PHP 8.3+
- MySQL (PDO)
- POO / MVC simples
- Composer
- Swagger-PHP / OpenAPI (documentação)
- Git / GitHub
- Insomnia (testes manuais)
- Laravel Herd (ambiente local)

Sem Laravel Framework, sem sessão, sem login/JWT — apenas PHP puro organizado.

## Estrutura do projeto

```
FixAPI/
├── Configuracao/
│   └── configuracao.php         # Carrega o .env e define constantes de config
├── Controlador/
│   └── ControladorOrdemManutencao.php
├── Modelo/
│   ├── Conexao.php              # Conexão PDO (única responsabilidade)
│   └── OrdemManutencao.php      # Todo o SQL da entidade + validação
├── Rotas/
│   └── rotas.php                # Roteamento: método HTTP + URI -> Controlador
├── Swagger/
│   └── openapi.php              # Anotações OpenAPI (PHP Attributes)
├── banco_de_dados/
│   └── esquema.sql              # Criação do banco + dados de exemplo
├── public/
│   ├── index.php                # Front controller (único ponto de entrada)
│   ├── openapi.json             # Documentação OpenAPI já gerada
│   └── docs.html                # Swagger UI (lê o openapi.json)
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

## Arquitetura

- **Modelo** (`Conexao`, `OrdemManutencao`): conexão PDO e todo o SQL com *prepared statements*. Também concentra a validação dos dados da entidade.
- **Controlador** (`ControladorOrdemManutencao`): lê a requisição (JSON do corpo, query string), chama o Modelo, monta a resposta em JSON com o código HTTP correto. Não tem SQL.
- **Rotas/rotas.php**: interpreta a URI e o método HTTP e despacha para o método certo do Controlador.
- **public/index.php**: front controller único — carrega as classes e delega para as rotas.

## Instalação

```bash
git clone <url-do-repositorio>
cd FixAPI
composer install
```

> `composer install` é opcional para rodar a API (ela não tem dependências obrigatórias em produção). Só é necessária se você quiser usar o `zircote/swagger-php` para regenerar o `openapi.json` a partir das anotações em `Swagger/openapi.php`.

## Configuração do banco

1. Crie o banco e a tabela executando o script:
   ```bash
   mysql -u root -p < banco_de_dados/esquema.sql
   ```
   Isso cria o banco `fixapi`, a tabela `maintenance_orders` e 5 registros de exemplo.

## Configuração do .env

1. Copie o arquivo de exemplo:
   ```bash
   cp .env.example .env
   ```
2. Ajuste as credenciais em `.env` conforme seu ambiente:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=fixapi
   DB_USER=root
   DB_PASS=
   APP_ENV=local
   ```

O `.env` nunca deve ser versionado (já está no `.gitignore`).

## Como executar

### Com o servidor embutido do PHP
```bash
php -S localhost:8000 -t public
```
A API ficará disponível em `http://localhost:8000/api/maintenance-orders`.

### Com Laravel Herd
1. Abra o Herd e aponte um site para a pasta do projeto.
2. Configure o **document root** do site para a pasta `public/`.
3. Acesse `http://fixapi.test/api/maintenance-orders` (ou o domínio que o Herd atribuir).

## Endpoints

| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/maintenance-orders` | Lista todas as ordens |
| GET | `/api/maintenance-orders?status=em_manutencao` | Filtra ordens por status |
| GET | `/api/maintenance-orders/{id}` | Busca uma ordem por ID |
| POST | `/api/maintenance-orders` | Cadastra uma ordem |
| PUT | `/api/maintenance-orders/{id}` | Atualiza uma ordem (parcial) |
| DELETE | `/api/maintenance-orders/{id}` | Exclui uma ordem |

Status permitidos: `recebido`, `em_analise`, `em_manutencao`, `aguardando_peca`, `concluido`, `entregue`, `cancelado`.

Campos obrigatórios no `POST`: `cliente_nome`, `cliente_telefone`, `equipamento`, `marca`, `problema_relatado`.

## Exemplos de JSON

**POST /api/maintenance-orders**
```json
{
  "cliente_nome": "João Silva",
  "cliente_telefone": "(71) 99999-0000",
  "equipamento": "Notebook",
  "marca": "Dell",
  "modelo": "Inspiron 15",
  "problema_relatado": "Não liga",
  "status": "recebido"
}
```

**Resposta (201 Created)**
```json
{
  "id": 6,
  "cliente_nome": "João Silva",
  "cliente_telefone": "(71) 99999-0000",
  "equipamento": "Notebook",
  "marca": "Dell",
  "modelo": "Inspiron 15",
  "problema_relatado": "Não liga",
  "diagnostico": null,
  "status": "recebido",
  "valor": null,
  "created_at": "2026-09-14 12:00:00",
  "updated_at": "2026-09-14 12:00:00"
}
```

**PUT /api/maintenance-orders/6** (atualização parcial)
```json
{
  "status": "em_manutencao",
  "diagnostico": "Fonte queimada",
  "valor": 250.00
}
```

**Erro de validação (422)**
```json
{
  "erro": "Dados inválidos.",
  "detalhes": [
    "O campo 'cliente_nome' é obrigatório."
  ]
}
```

## Como testar no Insomnia

1. Abra o Insomnia e importe a coleção pronta em `insomnia/FixAPI-insomnia.json` (*Import* > *From File*). Ela já traz as 6 requisições da API com a variável `base_url` (`http://localhost:8000` — ajuste para o domínio do Herd se necessário).
2. Ou crie manualmente uma nova coleção "FixAPI" com uma requisição para cada endpoint da tabela acima.
3. Para `POST` e `PUT`, defina o *Body* como `JSON` e cole os exemplos acima.
4. Confira os códigos de status retornados: `200`, `201`, `204`, `400`, `404` e `422`.

## Documentação Swagger

- **Já pronta:** abra `public/docs.html` no navegador (ex: `http://localhost:8000/docs.html`). Ele carrega o `public/openapi.json` em uma interface Swagger UI.
- **Para regenerar a partir das anotações** em `Swagger/openapi.php`:
  ```bash
  composer require zircote/swagger-php
  vendor/bin/openapi . -o public/openapi.json
  ```

## Git, GitHub e Jira

O projeto já está preparado com `.gitignore` (ignorando `.env`, `vendor/` e arquivos temporários).

Sugestão de tarefas para organizar no Jira:

1. Configuração do projeto
2. Banco de dados (esquema.sql)
3. Conexão PDO (Conexao.php)
4. Modelo (OrdemManutencao.php)
5. Controlador (ControladorOrdemManutencao.php)
6. Rotas (Rotas/rotas.php)
7. CRUD completo
8. Validações
9. Documentação Swagger
10. Testes no Insomnia
11. README
