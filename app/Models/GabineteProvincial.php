<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GabineteProvincial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gabinete_provinciais';

    protected $fillable = [
        'nome',
        'provincia',
        'endereco',
        'telefone',
        'email',
        'diretor',
        'observacoes'
    ];

    // Relacionamentos
    public function direcoesMunicipais()
    {
        return $this->hasMany(DirecaoMunicipal::class);
    }

    public function hospitais()
    {
        return $this->hasManyThrough(Hospital::class, DirecaoMunicipal::class);
    }

    // Acessors
    public function getTotalDirecoesMunicipaisAttribute()
    {
        return $this->direcoesMunicipais()->count();
    }

    public function getTotalHospitaisAttribute()
    {
        return $this->hospitais()->count();
    }

    public function getTotalLeitosAttribute()
    {
        return $this->hospitais()->sum('leitos_totais');
    }

    public function getTotalLeitosDisponiveisAttribute()
    {
        return $this->hospitais()->sum('leitos_disponiveis');
    }
}
