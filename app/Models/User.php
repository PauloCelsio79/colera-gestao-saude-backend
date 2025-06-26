<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tipo',
        'telefone',
        'ativo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'ativo' => 'boolean'
    ];

    // Relacionamentos
    public function triagens()
    {
        return $this->hasMany(Triagem::class);
    }

    public function relatorios()
    {
        return $this->hasMany(Relatorio::class);
    }

    // Escopos
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Métodos de verificação de tipo
    public function isAdmin()
    {
        return $this->tipo === 'admin';
    }

    public function isMedico()
    {
        return $this->tipo === 'medico';
    }

    public function isGestor()
    {
        return $this->tipo === 'gestor';
    }

    public function isTecnico()
    {
        return $this->tipo === 'tecnico';
    }
}
