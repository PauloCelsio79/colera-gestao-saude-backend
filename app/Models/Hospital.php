<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospital extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hospitais';

    protected $fillable = [
        'direcao_municipal_id',
        'nome',
        'tipo',
        'endereco',
        'latitude',
        'longitude',
        'telefone',
        'email',
        'diretor',
        'leitos_totais',
        'leitos_disponiveis',
        'observacoes',
        'ponto_emergencia',
        'servicos_emergencia',
        'capacidade_emergencia',
        'ativo'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'leitos_totais' => 'integer',
        'leitos_disponiveis' => 'integer',
        'ponto_emergencia' => 'boolean',
        'capacidade_emergencia' => 'integer',
        'ativo' => 'boolean'
    ];

    // Relacionamentos
    public function direcaoMunicipal()
    {
        return $this->belongsTo(DirecaoMunicipal::class);
    }

    public function encaminhamentos()
    {
        return $this->hasMany(Encaminhamento::class);
    }

    // Escopo para hospitais ativos
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Escopo para hospitais com leitos disponíveis
    public function scopeComLeitosDisponiveis($query)
    {
        return $query->where('leitos_disponiveis', '>', 0);
    }

    // Escopo para hospitais que são pontos de emergência
    public function scopePontosEmergencia($query)
    {
        return $query->where('ponto_emergencia', true)
                     ->where('ativo', true);
    }

    // Método para calcular distância até coordenadas específicas
    public function distanciaAte($latitude, $longitude)
    {
        // Fórmula de Haversine para calcular distância entre coordenadas
        $earthRadius = 6371; // Raio da Terra em quilômetros

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    // Acessors
    public function getOcupacaoAttribute()
    {
        return $this->leitos_totais > 0 
            ? round(($this->leitos_totais - $this->leitos_disponiveis) / $this->leitos_totais * 100, 2)
            : 0;
    }
} 