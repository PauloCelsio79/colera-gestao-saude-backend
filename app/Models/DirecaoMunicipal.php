<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirecaoMunicipal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'direcao_municipais';

    protected $fillable = [
        'gabinete_provincial_id',
        'nome',
        'municipio',
        'endereco',
        'telefone',
        'email',
        'diretor',
        'observacoes'
    ];

    // Relacionamentos
    public function gabineteProvincial()
    {
        return $this->belongsTo(GabineteProvincial::class);
    }

    public function hospitais()
    {
        return $this->hasMany(Hospital::class);
    }

    // Acessors
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

    public function getTotalEncaminhamentosAttribute()
    {
        return $this->hospitais()->withCount('encaminhamentos')->get()->sum('encaminhamentos_count');
    }
}
