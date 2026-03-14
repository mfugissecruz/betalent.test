# BeTalent — Teste Prático Back-end

## Nota pessoal

Embora formalmente ainda esteja atuando como desenvolvedor júnior e tenha me candidatado a uma vaga nesse nível, decidi ir além: por puro desafio, implementei o projeto completo no **nível 3**. Agradeço imensamente a oportunidade e espero que o resultado demonstre dedicação e comprometimento com qualidade.

---

## Sobre o projeto

API RESTful de gestão de pagamentos com múltiplos gateways, construída em **Laravel 12** com PHP 8.5. O sistema suporta autenticação via Sanctum, controle de acesso por papéis (RBAC), fallback automático entre gateways e estorno de transações.

**Stack principal:**

- PHP 8.5 / Laravel 12
- MySQL 8.4
- Laravel Sanctum (autenticação por token)
- Laravel Sail (Docker)
- Pest 4 (testes)

---

## Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) e Docker Compose instalados
- Nenhuma outra dependência local necessária — tudo roda dentro dos containers

---

## Configuração e execução

### 1. Clonar o repositório

```bash
git clone https://github.com/mfugissecruz/betalent.test.git
cd betalent.test
```

### 2. Copiar o arquivo de ambiente

```bash
cp .env.example .env
```

### 3. Configurar as variáveis dos gateways

Abra o `.env` e preencha as credenciais dos dois gateways mock:

```dotenv
# Gateway 1 — autenticação via token (porta 3001)
GATEWAY_1_PORT=3001
GATEWAY_1_ENDPOINT=http://gateways_mock:3001
GATEWAY_1_EMAIL=<email-fornecido>
GATEWAY_1_TOKEN=<token-fornecido>

# Gateway 2 — autenticação via header (porta 3002)
GATEWAY_2_PORT=3002
GATEWAY_2_ENDPOINT=http://gateways_mock:3002
GATEWAY_AUTH_TOKEN=<token-fornecido>
GATEWAY_AUTH_SECRET=<secret-fornecido>

# Desabilita autenticação nos mocks (útil apenas para desenvolvimento)
GATEWAYS_REMOVE_AUTH=false
```

> **Sobre as URLs dos gateways:** a aplicação Laravel roda dentro de um container Docker e se comunica com os mocks pela rede interna do Sail, onde cada serviço é identificado pelo nome definido no `compose.yaml` (`gateways_mock`).

Também configure o banco de dados no `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### 4. Instalar dependências PHP

Este passo é necessário antes de iniciar o Sail, pois o Docker precisa da pasta `vendor/` para construir a imagem da aplicação.

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    composer:latest \
    install --ignore-platform-reqs
```

### 5. Subir os containers

```bash
./vendor/bin/sail up -d
```

Na primeira execução, o Docker constrói a imagem da aplicação a partir do Dockerfile do Sail — isso pode levar alguns minutos. As próximas inicializações serão mais rápidas.

Isso inicia: aplicação Laravel, MySQL 8.4 e os dois gateways mock.

### 6. Gerar a chave da aplicação

```bash
./vendor/bin/sail artisan key:generate
```

### 7. Executar migrations e seeders

```bash
./vendor/bin/sail artisan migrate --seed
```

O seeder cria:

| O que         | Valor                               |
| ------------- | ----------------------------------- |
| Usuário ADMIN | `contact@marcelocruz.dev`           |
| Senha         | `password`                          |
| Gateway 1     | `gateway_1` — ativo, prioridade 1   |
| Gateway 2     | `gateway_2` — inativo, prioridade 2 |

---

## Executar os testes

```bash
./vendor/bin/sail artisan test --compact
```

Para filtrar por suite:

```bash
./vendor/bin/sail artisan test --compact --filter=PurchaseTest
```

---

## Papéis e permissões (RBAC)

| Recurso                                | ADMIN | MANAGER | FINANCE | USER |
| -------------------------------------- | :---: | :-----: | :-----: | :--: |
| Gateways (ativar/desativar/prioridade) |   ✓   |    —    |    —    |  —   |
| Users CRUD                             |   ✓   |    ✓    |    —    |  —   |
| Products CRUD                          |   ✓   |    ✓    |    ✓    |  —   |
| Products (listagem/detalhe)            |   ✓   |    ✓    |    ✓    |  ✓   |
| Clients (listagem/detalhe)             |   ✓   |    ✓    |    ✓    |  ✓   |
| Transactions (listagem/detalhe)        |   ✓   |    ✓    |    ✓    |  ✓   |
| Refund                                 |   ✓   |    —    |    ✓    |  —   |

---

## Rotas da API

O arquivo `docs/BeTalent.postman_collection.json` contém todas as rotas prontas para importar no Postman. A requisição **Login** salva o token automaticamente na variável `{{token}}`, que é usada por todas as demais requisições.

A URL base é `http://localhost/api`. Todos os exemplos usam `curl`.

### Autenticação

#### `POST /api/login`

**Corpo:**

```json
{
    "email": "contact@marcelocruz.dev",
    "password": "password"
}
```

**Sucesso `200`:**

```json
{
    "token": "1|abc123..."
}
```

**Credenciais inválidas `401`:**

```json
{
    "message": "Invalid credentials."
}
```

**Rate limit excedido após 5 tentativas `429`:**

```json
{
    "message": "Too Many Attempts."
}
```

> Todas as rotas protegidas exigem o header: `Authorization: Bearer <token>`

---

### Compra (pública)

#### `POST /api/purchase`

Cria ou recupera o cliente pelo e-mail, calcula o valor total a partir dos produtos e quantidades, tenta os gateways ativos em ordem de prioridade com fallback automático.

**Corpo:**

```json
{
    "client": {
        "name": "João Silva",
        "email": "joao@example.com"
    },
    "card": {
        "number": "4111111111111111",
        "cvv": "123",
        "expiry_date": "12/2028",
        "holder_name": "JOAO SILVA"
    },
    "products": [
        { "id": 1, "quantity": 2 },
        { "id": 3, "quantity": 1 }
    ]
}
```

**Sucesso `201`:**

```json
{
    "data": {
        "id": 1,
        "client": {
            "id": 1,
            "name": "João Silva",
            "email": "joao@example.com",
            "created_at": "2026-03-14 10:00:00"
        },
        "gateway": {
            "id": 1,
            "name": "gateway_1",
            "is_active": true,
            "priority": 1
        },
        "external_id": "ext_abc123",
        "status": "paid",
        "amount": 120,
        "card_last_numbers": "1111",
        "products": [
            {
                "id": 1,
                "name": "Produto A",
                "amount": 60,
                "quantity": 2
            }
        ],
        "created_at": "2026-03-14 10:00:00"
    }
}
```

**Simulando falha de gateway com CVV especial:**

Os gateways mock reconhecem CVVs reservados para simular recusa de cartão:

| CVV   | Comportamento                                         |
| ----- | ----------------------------------------------------- |
| `100` | Gateway 1 recusa — fallback para Gateway 2 (se ativo) |
| `300` | Gateway 2 recusa                                      |
| `200` | Ambos os gateways recusam → retorna `502`             |

**Todos os gateways falharam `502`:**

```json
{
    "message": "All payment gateways are unavailable. Please try again later."
}
```

**Produto não encontrado `422`:**

```json
{
    "message": "The selected products.0.id is invalid.",
    "errors": {
        "products.0.id": ["The selected products.0.id is invalid."]
    }
}
```

**Campos obrigatórios ausentes `422`:**

```json
{
    "message": "The card.number field is required.",
    "errors": {
        "card.number": ["The card.number field is required."]
    }
}
```

---

### Clientes

> Requer autenticação. Qualquer papel pode acessar.

#### `GET /api/clients`

```bash
curl -H "Authorization: Bearer <token>" http://localhost/api/clients
```

**Sucesso `200`:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "João Silva",
            "email": "joao@example.com",
            "created_at": "2026-03-14 10:00:00"
        }
    ],
    "links": { "...": "paginação" },
    "meta": { "...": "metadados" }
}
```

#### `GET /api/clients/{id}`

Inclui histórico completo de transações com produtos e gateway.

**Sucesso `200`:**

```json
{
    "data": {
        "id": 1,
        "name": "João Silva",
        "email": "joao@example.com",
        "created_at": "2026-03-14 10:00:00",
        "transactions": [
            {
                "id": 1,
                "gateway": {
                    "id": 1,
                    "name": "gateway_1",
                    "is_active": true,
                    "priority": 1
                },
                "external_id": "ext_abc123",
                "status": "paid",
                "amount": 120,
                "card_last_numbers": "1111",
                "products": [
                    {
                        "id": 1,
                        "name": "Produto A",
                        "amount": 60,
                        "quantity": 2
                    }
                ],
                "created_at": "2026-03-14 10:00:00"
            }
        ]
    }
}
```

**Não encontrado `404`:**

```json
{
    "message": "No query results for model [App\\Models\\Client] 999"
}
```

---

### Transações

> Requer autenticação. Qualquer papel pode acessar.

#### `GET /api/transactions`

**Sucesso `200`:**

```json
{
    "data": [
        {
            "id": 1,
            "client": {
                "id": 1,
                "name": "João Silva",
                "email": "joao@example.com",
                "created_at": "2026-03-14 10:00:00"
            },
            "gateway": {
                "id": 1,
                "name": "gateway_1",
                "is_active": true,
                "priority": 1
            },
            "external_id": "ext_abc123",
            "status": "paid",
            "amount": 120,
            "card_last_numbers": "1111",
            "products": [
                { "id": 1, "name": "Produto A", "amount": 60, "quantity": 2 }
            ],
            "created_at": "2026-03-14 10:00:00"
        }
    ]
}
```

#### `GET /api/transactions/{id}`

Igual ao item acima, mas para uma única transação.

#### `POST /api/transactions/{id}/refund`

> Requer papel **ADMIN** ou **FINANCE**.

Solicita estorno ao gateway que processou a transação original e atualiza o status para `charged_back`.

**Sucesso `200`:**

```json
{
    "data": {
        "id": 1,
        "status": "charged_back",
        "...": "demais campos da transação"
    }
}
```

**Transação já estornada `422`:**

```json
{
    "message": "This transaction has already been refunded."
}
```

**Sem permissão `403`:**

```json
{
    "message": "This action is unauthorized."
}
```

---

### Usuários

> Requer papel **ADMIN** ou **MANAGER**.

#### `GET /api/users`

**Sucesso `200`:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Marcelo Cruz",
            "email": "contact@marcelocruz.dev",
            "role": "ADMIN",
            "created_at": "2026-03-14 10:00:00"
        }
    ]
}
```

#### `GET /api/users/{id}`

#### `POST /api/users`

**Corpo:**

```json
{
    "name": "Ana Lima",
    "email": "ana@example.com",
    "password": "secret123",
    "role": "FINANCE"
}
```

**Papéis válidos:** `ADMIN`, `MANAGER`, `FINANCE`, `USER`

**Sucesso `201`:**

```json
{
    "data": {
        "id": 2,
        "name": "Ana Lima",
        "email": "ana@example.com",
        "role": "FINANCE",
        "created_at": "2026-03-14 10:00:00"
    }
}
```

**E-mail duplicado `422`:**

```json
{
    "message": "The email has already been taken.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

**Papel inválido `422`:**

```json
{
    "message": "The selected role is invalid.",
    "errors": {
        "role": ["The selected role is invalid."]
    }
}
```

#### `PUT /api/users/{id}`

Todos os campos são opcionais.

**Corpo:**

```json
{
    "name": "Ana Lima Souza",
    "password": "novaSenha456"
}
```

**Sucesso `200`** — retorna o recurso atualizado.

#### `DELETE /api/users/{id}`

**Sucesso `204`** — sem corpo.

**Sem permissão (papel FINANCE tentando) `403`:**

```json
{
    "message": "This action is unauthorized."
}
```

---

### Produtos

> Listagem/detalhe: qualquer autenticado. CRUD: **ADMIN**, **MANAGER** ou **FINANCE**.

#### `GET /api/products`

**Sucesso `200`:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Produto A",
            "amount": 60,
            "created_at": "2026-03-14 10:00:00"
        }
    ]
}
```

#### `GET /api/products/{id}`

#### `POST /api/products`

**Corpo:**

```json
{
    "name": "Produto B",
    "amount": 99
}
```

**Sucesso `201`:**

```json
{
    "data": {
        "id": 2,
        "name": "Produto B",
        "amount": 99,
        "created_at": "2026-03-14 10:00:00"
    }
}
```

**Campos inválidos `422`:**

```json
{
    "message": "The amount field must be at least 0.01.",
    "errors": {
        "amount": ["The amount field must be at least 0.01."]
    }
}
```

#### `PUT /api/products/{id}`

Todos os campos são opcionais.

#### `DELETE /api/products/{id}`

**Sucesso `204`** — sem corpo.

**Sem permissão (papel USER tentando) `403`:**

```json
{
    "message": "This action is unauthorized."
}
```

---

### Gateways

> Requer papel **ADMIN**.

#### `PATCH /api/gateways/{id}/activate`

```bash
curl -X PATCH \
     -H "Authorization: Bearer <token>" \
     http://localhost/api/gateways/2/activate
```

**Sucesso `200`:**

```json
{
    "data": {
        "id": 2,
        "name": "gateway_2",
        "is_active": true,
        "priority": 2
    }
}
```

#### `PATCH /api/gateways/{id}/deactivate`

**Sucesso `200`:**

```json
{
    "data": {
        "id": 1,
        "name": "gateway_1",
        "is_active": false,
        "priority": 1
    }
}
```

#### `PATCH /api/gateways/{id}/priority`

**Corpo:**

```json
{
    "priority": 1
}
```

**Sucesso `200`:**

```json
{
    "data": {
        "id": 2,
        "name": "gateway_2",
        "is_active": true,
        "priority": 1
    }
}
```

**Sem autenticação `401`:**

```json
{
    "message": "Unauthenticated."
}
```

**Sem permissão (não ADMIN) `403`:**

```json
{
    "message": "This action is unauthorized."
}
```

---

## Estrutura do banco de dados

```
users               — autenticação e controle de acesso
gateways            — gateways de pagamento (nome, status, prioridade)
clients             — clientes que realizaram compras
products            — produtos disponíveis para venda
transactions        — transações processadas pelos gateways
transaction_products — tabela pivot: produtos por transação (com quantity)
personal_access_tokens — tokens Sanctum
```

---

## Fluxo de compra

1. O sistema busca ou cria o cliente pelo `email`
2. Calcula o `amount` total somando `produto.amount × quantity` para cada item
3. Tenta o gateway com menor `priority` que esteja ativo
4. Se falhar, tenta o próximo gateway ativo
5. Se todos falharem, retorna `502`
6. Em caso de sucesso, persiste `Transaction` e `TransactionProduct`

---

## Parar os containers

```bash
./vendor/bin/sail down
```
