# Proposta de Controle de Impressão - Sistema de Pedidos

## Problema Atual

Atualmente, o sistema não possui um controle efetivo de quais pedidos já foram impressos. O método `marcarComoImpresso()` no `PrintController` está vazio, apenas retornando uma resposta de sucesso sem persistir nenhuma informação.

## Soluções Propostas

## Implementação

### 1. Migration da Tabela de Impressões

```php
<?php
// database/migrations/xxxx_create_impressoes_pedidos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impressoes_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->string('agente_impressao')->default('sistema'); // sistema, agente_externo
            $table->string('ip_origem')->nullable();
            $table->enum('status_impressao', ['pendente', 'processando', 'sucesso', 'erro'])->default('pendente');
            $table->text('detalhes_erro')->nullable();
            $table->json('dados_impressao')->nullable(); // dados extras como impressora usada, etc.
            $table->timestamps();
            
            $table->index(['pedido_id', 'status_impressao']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impressoes_pedidos');
    }
};
```

### 2. Model ImpressaoPedido

```php
<?php
// app/Models/Bar/ImpressaoPedido.php

namespace App\Models\Bar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImpressaoPedido extends Model
{
    use HasFactory;

    protected $table = 'impressoes_pedidos';
    
    protected $fillable = [
        'pedido_id',
        'agente_impressao',
        'ip_origem',
        'status_impressao',
        'detalhes_erro',
        'dados_impressao'
    ];

    protected $casts = [
        'dados_impressao' => 'array'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    // Scopes úteis
    public function scopePendentes($query)
    {
        return $query->where('status_impressao', 'pendente');
    }

    public function scopeSucesso($query)
    {
        return $query->where('status_impressao', 'sucesso');
    }
}
```

### 3. Atualização do Model Pedido

```php
// Adicionar ao Model Pedido
public function impressoes()
{
    return $this->hasMany(ImpressaoPedido::class);
}

public function ultimaImpressao()
{
    return $this->hasOne(ImpressaoPedido::class)->latest();
}

public function foiImpresso()
{
    return $this->impressoes()->where('status_impressao', 'sucesso')->exists();
}

public function temImpressaoPendente()
{
    return $this->impressoes()->where('status_impressao', 'pendente')->exists();
}
```

### 4. Atualização do PrintController

```php
// Método getPedidosPendentes atualizado
public function getPedidosPendentes(): JsonResponse
{
    try {
        $pedidosPendentes = Pedido::with(['mesa', 'cliente', 'reserva.quarto'])
            ->where('status', 'aberto')
            ->whereHas('itens')
            ->whereDoesntHave('impressoes', function($query) {
                $query->where('status_impressao', 'sucesso');
            })
            ->orderBy('updated_at', 'desc')
            ->get();
            
        // ... resto do código
    }
}

// Método marcarComoImpresso atualizado
public function marcarComoImpresso(Request $request, int $pedidoId): JsonResponse
{
    try {
        $pedido = Pedido::findOrFail($pedidoId);
        
        // Criar registro de impressão
        $impressao = $pedido->impressoes()->create([
            'agente_impressao' => $request->input('agente', 'agente_externo'),
            'ip_origem' => $request->ip(),
            'status_impressao' => 'sucesso',
            'dados_impressao' => [
                'user_agent' => $request->userAgent(),
                'timestamp_processamento' => now()->toISOString()
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido marcado como impresso com sucesso',
            'pedido_id' => $pedidoId,
            'impressao_id' => $impressao->id,
            'timestamp' => $impressao->created_at->format('d/m/Y H:i:s')
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao marcar pedido como impresso: ' . $e->getMessage(),
            'error_code' => 'MARK_PRINTED_ERROR'
        ], 500);
    }
}
```

### 5. Novos Endpoints Úteis

```php
// Histórico de impressões de um pedido
public function getHistoricoImpressoes(int $pedidoId): JsonResponse
{
    $pedido = Pedido::with('impressoes')->findOrFail($pedidoId);
    
    return response()->json([
        'success' => true,
        'data' => $pedido->impressoes,
        'total_impressoes' => $pedido->impressoes->count()
    ]);
}

// Estatísticas de impressão
public function getEstatisticasImpressao(): JsonResponse
{
    $stats = [
        'pedidos_pendentes' => Pedido::whereDoesntHave('impressoes', function($q) {
            $q->where('status_impressao', 'sucesso');
        })->count(),
        'pedidos_impressos_hoje' => ImpressaoPedido::where('status_impressao', 'sucesso')
            ->whereDate('created_at', today())->count(),
        'total_impressoes' => ImpressaoPedido::count()
    ];
    
    return response()->json(['success' => true, 'data' => $stats]);
}
```

## Fluxo de Trabalho Completo

### 1. Usuário Gera Cupom Parcial
```
1. Usuário clica em "Gerar Cupom Parcial"
2. Sistema cria registro na tabela impressoes_pedidos com status 'pendente'
3. Sistema disponibiliza dados via API
4. Sistema gera PDF (funcionalidade original)
```

### 2. Agente de Impressão
```
1. Agente consulta /api/print/pedidos-pendentes
2. Sistema retorna apenas pedidos SEM impressão bem-sucedida
3. Agente processa impressão
4. Agente chama /api/print/pedido/{id}/impresso
5. Sistema atualiza status para 'sucesso'
```

### 3. Consulta de Status
```
1. Sistema pode consultar facilmente quais pedidos foram impressos
2. Relatórios de impressão disponíveis
3. Auditoria completa do processo
```

## Vantagens da Solução Proposta

1. **Controle Total**: Sabe exatamente quais pedidos foram impressos
2. **Histórico Completo**: Mantém registro de todas as tentativas
3. **Auditoria**: Rastreabilidade completa para compliance
4. **Performance**: Índices otimizados para consultas rápidas
5. **Flexibilidade**: Permite diferentes tipos de agentes de impressão
6. **Escalabilidade**: Suporta múltiplas impressoras e agentes
7. **Monitoramento**: Facilita identificação de problemas

## Próximos Passos

1. Criar a migration da tabela `impressoes_pedidos`
2. Criar o model `ImpressaoPedido`
3. Atualizar o model `Pedido` com os relacionamentos
4. Atualizar o `PrintController` com a lógica de controle
5. Testar o fluxo completo
6. Documentar para a equipe

Esta solução garante que o sistema tenha controle total sobre o processo de impressão, mantendo histórico completo e permitindo auditoria detalhada de todas as operações.
