<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemocaoPaciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'solicitante_id',
        'status',
        'motivo',
    ];

    public function paciente()
    {
        return $this->belongsTo(\App\Models\Paciente::class, 'paciente_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(\App\Models\User::class, 'solicitante_id');
    }
}
