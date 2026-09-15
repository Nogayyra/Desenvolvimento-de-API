# FixAPI

API REST em PHP para gerenciamento de ordens de manutenção de computadores em uma assistência técnica.

> Projeto acadêmico da Unidade Curricular **Desenvolvimento de APIs — 3º TI B**.

## Documentação Swagger

- **Já pronta:** abra `public/docs.html` no navegador (ex: `http://localhost:8000/docs.html`). Ele carrega o `public/openapi.json` em uma interface Swagger UI.
- **Para regenerar a partir das anotações** em `Swagger/openapi.php`:
  ```bash
  composer require zircote/swagger-php
   vendor/bin/openapi Swagger -o public/openapi.json
  ```

## Quem fez o quê (Felipe e Arthur)

Os commits no GitHub saíram todos pela conta do Felipe, mas o código foi dividido entre a dupla como combinado e registrado no relatório do projeto:

| Felipe | Arthur |
|---|---|
| Configuração (`.env`, `Configuracao/`, `composer.json`) | Banco de dados (`banco_de_dados/esquema.sql`) |
| Conexão com o banco (`Modelo/Conexao.php`) | Controlador com o CRUD (`Controlador/`) |
| Modelo com SQL e validação (`Modelo/OrdemManutencao.php`) | Rotas (`Rotas/rotas.php`) |
| Validações e códigos de erro (400, 404, 422, 405) | Documentação Swagger (`Swagger/`, `openapi.json`, `docs.html`) |
| Testes E2E locais + banco `fixapi_test` | Coleção do Insomnia + testes manuais |
| Versionamento e envio ao GitHub | README e padronização em português |
