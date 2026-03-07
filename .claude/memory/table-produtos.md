# Tabela: `produtos`

**Model:** `app/Models/Produto.php`
**Total de registros:** 477 (todos ativos)

---

## Estrutura de Colunas

| Coluna | Tipo | Null | Default | Descrição |
|--------|------|------|---------|-----------|
| `id` | `bigint unsigned` | NO | auto_increment | PK |
| `descricao` | `varchar(255)` | NO | — | Nome/descrição do produto |
| `valor_unitario` | `decimal(8,2)` | YES | null | Valor de venda legado (usar `preco_venda`) |
| `preco_custo` | `decimal(8,2)` | YES | null | Preço de custo |
| `preco_venda` | `decimal(8,2)` | YES | null | Preço de venda atual |
| `estoque_minimo` | `int(11)` | YES | null | Gatilho de alerta de estoque baixo |
| `estoque_maximo` | `int(11)` | YES | null | Capacidade máxima de estoque |
| `categoria_produto` | `varchar(255)` | NO | — | FK → `categorias.id` (armazenado como string) |
| `codigo_barras_produto` | `varchar(255)` | YES | null | EAN/código de barras |
| `codigo_interno` | `varchar(255)` | YES | null | Código interno do estabelecimento |
| `impressora` | `varchar(255)` | YES | null | ID da impressora destino (0, 1 ou 2) |
| `unidade` | `varchar(255)` | NO | — | Unidade de medida (ver enum abaixo) |
| `ativo` | `tinyint(1)` | NO | 1 | 1 = ativo, 0 = inativo |
| `criado_por` | `varchar(255)` | NO | — | Nome do usuário criador |
| `complemento` | `varchar(255)` | YES | null | Informação adicional livre |
| `produto_servico` | `varchar(255)` | NO | — | `'produto'` ou `'servico'` |
| `created_at` | `timestamp` | YES | null | Criado em |
| `updated_at` | `timestamp` | YES | null | Atualizado em |

---

## Enums / Valores Controlados

### `unidade` — `Produto::UNIDADES`
| Código | Descrição |
|--------|-----------|
| `UN` | Unidade |
| `KG` | Quilograma |
| `LT` | Litro |
| `CX` | Caixa |
| `PC` | Peça |
| `MT` | Metro |
| `FD` | Fardo |
| `SC` | Saco |
| `BD` | Balde |
| `DS` | Dose |
| `CP` | Copo |
| `TC` | Taça |

### `impressora` — `Produto::IMPRESSORA`
| Valor | Destino |
|-------|---------|
| `null` / `''` | Sem impressora (285 produtos) |
| `0` | COZINHA (27 produtos) |
| `1` | BAR (105 produtos) |
| `2` | RECEPCAO (60 produtos) |

### `produto_servico`
| Valor | Total |
|-------|-------|
| `'produto'` | 476 |
| `'servico'` | 1 |

---

## Relacionamentos (Model)

| Método | Tipo | Tabela/Model |
|--------|------|--------------|
| `categoria()` | `belongsTo` | `Categoria` via `categoria_produto` |
| `estoques()` | `hasMany` | `Estoque` via `produto_id` |
| `itens()` | `hasMany` | `Bar\ItemPedido` |
| `composicoes()` | `hasMany` | `ProdutoComposicao` via `produto_id` |
| `usuarioCriador()` | `belongsTo` | `Usuario` via `criado_por` |

---

## Estatísticas dos Dados

- **Total:** 477 registros (477 ativos, 0 inativos)
- **Preço de venda:** mín R$ 0,00 / máx R$ 1.000,00 / média R$ 18,34
- **Preço de custo:** mín R$ 0,00 / máx R$ 11.025,00
- **Unidade dominante:** `UN` (465 de 477)
- **Categorias distintas:** 26

---

## Notas

- `categoria_produto` é `varchar` mas armazena o `id` inteiro de `categorias` — FK sem constraint no banco.
- `valor_unitario` parece ser um campo legado; `preco_venda` é o campo atual de preço.
- `criado_por` armazena o nome (string) do usuário, não o ID — sem FK real para `usuarios`.
- A impressora `0` (COZINHA) é distinta de `null`/`''` (sem impressora).
