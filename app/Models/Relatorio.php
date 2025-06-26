<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Relatorio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tipo',
        'filtros',
        'dados',
        'periodo_inicio',
        'periodo_fim',
        'formato',
        'arquivo_path'
    ];

    protected $casts = [
        'filtros' => 'json',
        'dados' => 'json',
        'periodo_inicio' => 'datetime',
        'periodo_fim' => 'datetime'
    ];

    // Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Escopos
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('periodo_inicio', [$inicio, $fim]);
    }

    // Métodos
    public function gerarArquivo()
    {
        // Implementação da geração do arquivo baseado no formato
        if ($this->formato === 'pdf') {
            // Gerar PDF
        } else {
            // Gerar Excel/CSV
        }
    }

    public function getDadosFormatados()
    {
        return json_decode($this->dados, true);
    }
} 