<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encaminhamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'triagem_id',
        'hospital_id',
        'ambulancia_id',
        'status',
        'motivo_cancelamento',
        'data_encaminhamento',
        'data_chegada',
        'observacoes'
    ];

    protected $casts = [
        'data_encaminhamento' => 'datetime',
        'data_chegada' => 'datetime'
    ];

    // Relacionamentos
    public function triagem()
    {
        return $this->belongsTo(Triagem::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function ambulancia()
    {
        return $this->belongsTo(Ambulancia::class);
    }

    public function paciente()
    {
        return $this->triagem->paciente();
    }

    // Escopos
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeConcluidos($query)
    {
        return $query->where('status', 'concluido');
    }

    // Métodos
    public function concluir()
    {
        $this->status = 'concluido';
        $this->data_chegada = now();
        $this->save();

        // Atualiza leitos disponíveis do hospital
        $this->hospital->decrement('leitos_disponiveis');
    }

    public function cancelar($motivo)
    {
        $this->status = 'cancelado';
        $this->motivo_cancelamento = $motivo;
        $this->save();
    }
} 