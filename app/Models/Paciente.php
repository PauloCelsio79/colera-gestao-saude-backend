<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'bi_numero',
        'data_nascimento',
        'genero',
        'telefone',
        'endereco',
        'municipio',
        'direcao_municipal_id',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'ativo' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    // Relacionamentos
    public function triagens()
    {
        return $this->hasMany(Triagem::class);
    }

    // Relacionamento com DirecaoMunicipal
    public function direcaoMunicipal()
    {
        return $this->belongsTo(DirecaoMunicipal::class);
    }

    // Escopo para pacientes ativos
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Método para calcular idade
    public function getIdadeAttribute()
    {
        return $this->data_nascimento->age;
    }
} 