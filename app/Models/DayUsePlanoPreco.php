<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DayUsePlanoPreco extends Model
{
    protected $table = 'day_use_plano_precos';

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $fillable = [
        'data_inicio',
        'data_fim',
        'is_default',
        'preco_segunda',
        'preco_terca',
        'preco_quarta',
        'preco_quinta',
        'preco_sexta',
        'preco_sabado',
        'preco_domingo',
        'preco_cafe',
        'preco_cafe_semana',
        'preco_cafe_fim_semana',
    ];

    /**
     * Retorna o preço do dia informado considerando o dia da semana.
     */
    public function getPrecoDia(Carbon $data): ?float
    {
        $diaSemana = $data->dayOfWeekIso; // 1 (segunda) - 7 (domingo)

        switch ($diaSemana) {
            case 1:
                return $this->preco_segunda;
            case 2:
                return $this->preco_terca;
            case 3:
                return $this->preco_quarta;
            case 4:
                return $this->preco_quinta;
            case 5:
                return $this->preco_sexta;
            case 6:
                return $this->preco_sabado;
            case 7:
                return $this->preco_domingo;
            default:
                return null;
        }
    }

    /**
     * Retorna o preço do café da manhã para o dia informado,
     * diferenciando dia de semana (seg–sex) e fim de semana (sáb–dom).
     */
    public function getPrecoCafeDia(Carbon $data): float
    {
        $diaSemana = $data->dayOfWeekIso; // 1 (segunda) - 7 (domingo)

        // Dia de semana: 1–5
        if ($diaSemana >= 1 && $diaSemana <= 5) {
            if (!is_null($this->preco_cafe_semana)) {
                return (float) $this->preco_cafe_semana;
            }
        }

        // Fim de semana: 6–7
        if ($diaSemana >= 6 && $diaSemana <= 7) {
            if (!is_null($this->preco_cafe_fim_semana)) {
                return (float) $this->preco_cafe_fim_semana;
            }
        }

        // Fallback para o campo legado, se existir
        return (float) ($this->preco_cafe ?? 0.0);
    }

    /**
     * Escopo para retornar planos vigentes em um período.
     */
    public function scopeVigente(Builder $query, Carbon $dataInicio, ?Carbon $dataFim = null): Builder
    {
        $dataFim = $dataFim ?? $dataInicio;

        return $query->where(function (Builder $q) use ($dataInicio, $dataFim) {
            $q->where(function (Builder $q2) use ($dataInicio, $dataFim) {
                $q2->whereNotNull('data_inicio')
                    ->whereNotNull('data_fim')
                    ->where('data_inicio', '<=', $dataInicio->toDateString())
                    ->where('data_fim', '>=', $dataFim->toDateString());
            })->orWhere(function (Builder $q3) {
                // Planos default (sem período definido)
                $q3->whereNull('data_inicio')
                    ->whereNull('data_fim')
                    ->where('is_default', true);
            });
        });
    }
}

