# API de Impressão - Venturize Hotelaria

Esta documentação descreve a API criada para integração com o agente de impressão externo.

## Visão Geral

A API de impressão foi desenvolvida para permitir que um agente externo monitore e processe pedidos para impressão automática em múltiplas impressoras.

## Endpoints Disponíveis

### 1. Obter Dados do Pedido para Impressão

**Endpoint:** `GET /api/print/pedido/{pedidoId}`

**Descrição:** Retorna todos os dados estruturados de um pedido específico em formato JSON para impressão.

**Parâmetros:**
- `pedidoId` (int): ID do pedido

**Exemplo de Resposta:**
```json
{
  "success": true,
  "data": {
    "pedido": {
      "id": 174,
      "status": "aberto",
      "total": 45.50,
      "taxa_servico": 4.55,
      "total_com_taxa": 50.05,
      "pedido_apartamento": false,
      "observacoes": "Sem gelo",
      "created_at": "15/01/2025 14:30:00",
      "updated_at": "15/01/2025 15:45:00"
    },
    "mesa": {
      "numero": "05",
      "status": "ocupada"
    },
    "cliente": {
      "id": 123,
      "nome": "João Silva",
      "cpf": "123.456.789-00",
      "telefone": "(11) 99999-9999"
    },
    "reserva": {
      "id": 456,
      "quarto": {
        "numero": "101",
        "tipo": "Standard"
      }
    },
    "itens": [
      {
        "id": 1,
        "quantidade": 2,
        "preco": 15.00,
        "subtotal": 30.00,
        "produto": {
          "id": 10,
          "descricao": "Cerveja Heineken",
          "categoria": "Bebidas"
        },
        "operador": {
          "id": 5,
          "nome": "Maria Santos"
        },
        "created_at": "15/01/2025 15:30:00"
      }
    ],
    "empresa": {
      "nome": "Venturize Hotelaria",
      "endereco": "Endereço da empresa",
      "telefone": "Telefone da empresa",
      "cnpj": "CNPJ da empresa"
    },
    "timestamp_impressao": "15/01/2025 15:45:30",
    "tipo_cupom": "parcial"
  },
  "message": "Dados do pedido gerados com sucesso para impressão"
}
```

### 2. Listar Pedidos Pendentes

**Endpoint:** `GET /api/print/pedidos-pendentes`

**Descrição:** Retorna uma lista de todos os pedidos em aberto que possuem itens.

**Exemplo de Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 174,
      "mesa_numero": "05",
      "cliente_nome": "João Silva",
      "quarto_numero": "101",
      "total": 45.50,
      "pedido_apartamento": false,
      "updated_at": "15/01/2025 15:45:00",
      "itens_count": 3
    }
  ],
  "count": 1,
  "message": "Lista de pedidos pendentes obtida com sucesso"
}
```

### 3. Marcar Pedido como Impresso

**Endpoint:** `POST /api/print/pedido/{pedidoId}/impresso`

**Descrição:** Marca um pedido como impresso (para controle do agente).

**Parâmetros:**
- `pedidoId` (int): ID do pedido

**Exemplo de Resposta:**
```json
{
  "success": true,
  "message": "Pedido marcado como impresso com sucesso",
  "pedido_id": 174,
  "timestamp": "15/01/2025 15:45:30"
}
```

### 4. Endpoint de Compatibilidade

**Endpoint:** `GET /api/cupom-parcial/{pedidoId}`

**Descrição:** Endpoint alternativo que retorna os mesmos dados do endpoint principal (para compatibilidade).

## Integração com o Sistema

### Botão "Gerar Cupom Parcial"

Quando o usuário clica no botão "Gerar Cupom Parcial" na interface do sistema:

1. **Chama a API de impressão** (`/api/print/pedido/{pedidoId}`) para disponibilizar os dados
2. **Gera o PDF** como antes (funcionalidade original mantida)
3. **Exibe notificação** de sucesso quando os dados são enviados para o agente

### Fluxo de Trabalho

1. **Usuário clica em "Gerar Cupom Parcial"**
2. **Sistema chama a API** e disponibiliza os dados em JSON
3. **Agente de impressão** (NodeJS/Python) monitora a API
4. **Agente processa** os dados e envia para as impressoras
5. **Agente marca** o pedido como impresso (opcional)

## Configuração do Agente de Impressão

### Exemplo de Monitoramento (NodeJS)

```javascript
const axios = require('axios');

// Função para verificar pedidos pendentes
async function verificarPedidosPendentes() {
  try {
    const response = await axios.get('http://seu-dominio.com/api/print/pedidos-pendentes');
    const pedidos = response.data.data;
    
    for (const pedido of pedidos) {
      await processarPedido(pedido.id);
    }
  } catch (error) {
    console.error('Erro ao verificar pedidos:', error);
  }
}

// Função para processar um pedido específico
async function processarPedido(pedidoId) {
  try {
    const response = await axios.get(`http://seu-dominio.com/api/print/pedido/${pedidoId}`);
    const dadosPedido = response.data.data;
    
    // Aqui você implementa a lógica de impressão
    await imprimirPedido(dadosPedido);
    
    // Marcar como impresso
    await axios.post(`http://seu-dominio.com/api/print/pedido/${pedidoId}/impresso`);
  } catch (error) {
    console.error(`Erro ao processar pedido ${pedidoId}:`, error);
  }
}

// Executar verificação a cada 30 segundos
setInterval(verificarPedidosPendentes, 30000);
```

## Tratamento de Erros

Todos os endpoints retornam erros no formato:

```json
{
  "success": false,
  "message": "Descrição do erro",
  "error_code": "CODIGO_DO_ERRO"
}
```

### Códigos de Erro

- `PRINT_DATA_ERROR`: Erro ao gerar dados para impressão
- `PENDING_ORDERS_ERROR`: Erro ao obter pedidos pendentes
- `MARK_PRINTED_ERROR`: Erro ao marcar pedido como impresso

## Segurança

- As rotas da API são públicas para facilitar a integração
- Para produção, considere implementar autenticação via token
- Monitore os logs de acesso para detectar uso indevido

---

**Versão:** 1.0  
**Data:** Outubro 2025  
**Autor:** Ferraz Tecnologia
