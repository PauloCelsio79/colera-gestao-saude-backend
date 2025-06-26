<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Triagem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'triagens';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'sintomas',
        'nivel_risco',
        'qr_code',
        'observacoes'
    ];

    protected $casts = [
        'sintomas' => 'json'
    ];

    // Relacionamentos
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function encaminhamento()
    {
        return $this->hasOne(Encaminhamento::class);
    }

    // Escopo para triagens de alto risco
    public function scopeAltoRisco($query)
    {
        return $query->where('nivel_risco', 'alto');
    }

    // Método para verificar se precisa de encaminhamento
    public function precisaEncaminhamento()
    {
        return $this->nivel_risco === 'alto' && !$this->encaminhamento()->exists();
    }
} 