<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'mensagem',
        'detalhes',
        'usuario_id',
        'nivel',
        'ip',
        'user_agent',
        'rota',
        'metodo'
    ];

    protected $casts = [
        'detalhes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relacionamento com usuário
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Escopo para filtrar por tipo
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Escopo para filtrar por nível
    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    // Escopo para filtrar por usuário
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    // Escopo para filtrar por período
    public function scopePorPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('created_at', [$inicio, $fim]);
    }

    // Escopo para filtrar por método HTTP
    public function scopePorMetodo($query, $metodo)
    {
        return $query->where('metodo', $metodo);
    }

    // Escopo para filtrar por status
    public function scopePorStatus($query, $status)
    {
        return $query->whereJsonContains('detalhes->status', $status);
    }

    // Escopo para busca por texto
    public function scopeBuscar($query, $texto)
    {
        return $query->where(function($q) use ($texto) {
            $q->where('mensagem', 'like', "%{$texto}%")
              ->orWhere('rota', 'like', "%{$texto}%")
              ->orWhereHas('usuario', function($q) use ($texto) {
                  $q->where('name', 'like', "%{$texto}%")
                    ->orWhere('email', 'like', "%{$texto}%");
              });
        });
    }

    // Escopo para ordenação
    public function scopeOrdenar($query, $campo, $direcao = 'desc')
    {
        return $query->orderBy($campo, $direcao);
    }
} 