<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemocaoAmbulancia extends Model
{
    use HasFactory;

    protected $fillable = [
        'ambulancia_id',
        'solicitante_id',
        'status',
        'motivo',
    ];

    public function ambulancia()
    {
        return $this->belongsTo(\App\Models\Ambulancia::class, 'ambulancia_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(\App\Models\User::class, 'solicitante_id');
    }
}
