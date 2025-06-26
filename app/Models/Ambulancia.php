<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ambulancia extends Model
{
    protected $fillable = [
        'placa',
        'modelo',
        'hospital_id',
        'status',
        'latitude',
        'longitude',
        'ultima_atualizacao'
    ];

    protected $casts = [
        'ultima_atualizacao' => 'datetime',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function encaminhamentos()
    {
        return $this->hasMany(Encaminhamento::class);
    }

    public function distanciaAte($lat, $long)
    {
        if (!$this->latitude || !$this->longitude) {
            return PHP_FLOAT_MAX;
        }

        // Fórmula de Haversine para calcular distância
        $earthRadius = 6371; // Raio da Terra em km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($long);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public static function buscarDisponiveis($lat, $long, $raioMaxKm = 10)
    {
        return self::where('status', 'disponivel')
            ->get()
            ->map(function ($ambulancia) use ($lat, $long) {
                $ambulancia->distancia = $ambulancia->distanciaAte($lat, $long);
                return $ambulancia;
            })
            ->filter(function ($ambulancia) use ($raioMaxKm) {
                return $ambulancia->distancia <= $raioMaxKm;
            })
            ->sortBy('distancia');
    }
} 