# Sistema de Controle de Impressão - Implementado

## Visão Geral

O sistema de controle de impressão foi implementado com sucesso, utilizando uma tabela dedicada `impressoes_pedidos` para rastrear o status de impressão de cada pedido.

## Estrutura Implementada

### 1. Tabela `impressoes_pedidos`

```sql
CREATE TABLE impressoes_pedidos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id BIGINT UNSIGNED NOT NULL,
    agente_impressao VARCHAR(255) NOT NULL,
    status_impressao ENUM('pendente', 'processando', 'sucesso', 'erro') DEFAULT 'pendente',
    tentativas INT DEFAULT 0,
    erro_detalhes TEXT NULL,
    impresso_em TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_pedido_id (pedido_id),
    INDEX idx_status_impressao (status_impressao),
    INDEX idx_agente_impressao (agente_impressao),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);
```

### 2. Model `ImpressaoPedido`

Localizado em: `app/Models/Bar/ImpressaoPedido.php`

**Principais funcionalidades:**
- Relacionamento com o modelo `Pedido`
- Scopes para filtrar por status e data
- Métodos para atualizar status de impressão

### 3. Atualizações no Model `Pedido`

**Novos relacionamentos:**
- `impressoes()` - Todas as impressões do pedido
- `ultimaImpressao()` - Última tentativa de impressão

**Novos métodos:**
- `foiImpresso()` - Verifica se foi impresso com sucesso
- `temImpressaoPendente()` - Verifica se há impressão pendente
- `temErroImpressao()` - Verifica se há erro na última impressão

**Novos scopes:**
- `impressos()` - Pedidos já impressos
- `naoImpressos()` - Pedidos não impressos

## API Endpoints

### Endpoints Existentes (Atualizados)

#### 1. Listar Pedidos Pendentes
```
GET /api/print/pedidos-pendentes
```

**Resposta atualizada:**
```json
{
    "pedidos": [
        {
            "id": 123,
            "mesa": "Mesa 5",
            "total": "R$ 45,90",
            "itens": [...],
            "impressao": {
                "status": "pendente",
                "tentativas": 0,
                "ultima_tentativa": null,
                "erro_detalhes": null
            }
        }
    ]
}
```

#### 2. Marcar Como Impresso
```
POST /api/print/pedido/{pedidoId}/impresso
```

**Body:**
```json
{
    "agente_impressao": "AgentePrint_v1.0"
}
```

### Novos Endpoints

#### 3. Registrar Tentativa de Impressão
```
POST /api/print/pedido/{pedidoId}/tentativa
```

**Body:**
```json
{
    "agente_impressao": "AgentePrint_v1.0"
}
```

#### 4. Marcar Erro na Impressão
```
POST /api/print/pedido/{pedidoId}/erro
```

**Body:**
```json
{
    "agente_impressao": "AgentePrint_v1.0",
    "erro_detalhes": "Impressora offline"
}
```

#### 5. Histórico de Impressões
```
GET /api/print/pedido/{pedidoId}/historico
```

**Resposta:**
```json
{
    "pedido_id": 123,
    "historico": [
        {
            "id": 1,
            "status_impressao": "sucesso",
            "agente_impressao": "AgentePrint_v1.0",
            "tentativas": 1,
            "impresso_em": "2025-01-15 16:30:00",
            "created_at": "2025-01-15 16:29:45"
        }
    ]
}
```

#### 6. Estatísticas de Impressão
```
GET /api/print/estatisticas
```

**Resposta:**
```json
{
    "hoje": {
        "total_impressoes": 45,
        "sucessos": 42,
        "erros": 3,
        "pendentes": 5,
        "taxa_sucesso": "93.33%"
    },
    "por_agente": {
        "AgentePrint_v1.0": {
            "total": 30,
            "sucessos": 28,
            "erros": 2
        }
    }
}
```

## Fluxo de Trabalho

### Para o Agente de Impressão

1. **Buscar pedidos pendentes:**
   ```bash
   curl -X GET "http://localhost:8000/api/print/pedidos-pendentes"
   ```

2. **Registrar tentativa de impressão:**
   ```bash
   curl -X POST "http://localhost:8000/api/print/pedido/123/tentativa" \
        -H "Content-Type: application/json" \
        -d '{"agente_impressao": "AgentePrint_v1.0"}'
   ```

3. **Em caso de sucesso:**
   ```bash
   curl -X POST "http://localhost:8000/api/print/pedido/123/impresso" \
        -H "Content-Type: application/json" \
        -d '{"agente_impressao": "AgentePrint_v1.0"}'
   ```

4. **Em caso de erro:**
   ```bash
   curl -X POST "http://localhost:8000/api/print/pedido/123/erro" \
        -H "Content-Type: application/json" \
        -d '{"agente_impressao": "AgentePrint_v1.0", "erro_detalhes": "Impressora offline"}'
   ```

## Vantagens da Implementação

1. **Rastreabilidade Completa:** Histórico detalhado de todas as tentativas de impressão
2. **Controle de Erros:** Registro de falhas com detalhes para debugging
3. **Estatísticas:** Métricas de performance do sistema de impressão
4. **Múltiplos Agentes:** Suporte a diferentes agentes de impressão
5. **Retry Logic:** Controle de tentativas para reprocessamento
6. **Performance:** Índices otimizados para consultas rápidas

## Próximos Passos

1. Implementar lógica de retry automático para pedidos com erro
2. Adicionar notificações para administradores em caso de falhas recorrentes
3. Criar dashboard para monitoramento em tempo real
4. Implementar limpeza automática de registros antigos

## Resposta à Pergunta Original

**"Como a aplicação vai saber que precisa tirar esse pedido da lista de pedidos para a impressão?"**

✅ **Resposta:** A aplicação agora usa a tabela `impressoes_pedidos` para controlar o status de impressão. Quando um pedido é marcado como impresso com sucesso, ele automaticamente sai da lista de pedidos pendentes através do scope `naoImpressos()` no modelo `Pedido`.

✅ **Onde a informação está armazenada:** No banco de dados MySQL, na tabela `impressoes_pedidos`.

✅ **Eficiência:** O sistema é otimizado com índices apropriados e permite rastreamento completo sem impacto na performance.