<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GabineteProvincial\StoreGabineteProvincialRequest;
use App\Http\Requests\GabineteProvincial\UpdateGabineteProvincialRequest;
use App\Models\GabineteProvincial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GabineteProvincialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $gabinetes = GabineteProvincial::with('direcoesMunicipais')
            ->withCount(['direcoesMunicipais', 'hospitais'])
            ->get();

        return response()->json($gabinetes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGabineteProvincialRequest $request): JsonResponse
    {
        $gabinete = GabineteProvincial::create($request->validated());

        return response()->json($gabinete, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GabineteProvincial $gabineteProvincial): JsonResponse
    {
        $gabineteProvincial->load('direcoesMunicipais.hospitais');
        $gabineteProvincial->loadCount(['direcoesMunicipais', 'hospitais']);

        return response()->json($gabineteProvincial);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGabineteProvincialRequest $request, GabineteProvincial $gabineteProvincial): JsonResponse
    {
        $gabineteProvincial->update($request->validated());

        return response()->json($gabineteProvincial);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GabineteProvincial $gabineteProvincial): JsonResponse
    {
        if ($gabineteProvincial->direcoesMunicipais()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir um gabinete provincial que possui direções municipais'
            ], 422);
        }

        $gabineteProvincial->delete();

        return response()->json(null, 204);
    }

    public function estatisticas(GabineteProvincial $gabineteProvincial): JsonResponse
    {
        $estatisticas = [
            'total_direcoes' => $gabineteProvincial->total_direcoes_municipais,
            'total_hospitais' => $gabineteProvincial->total_hospitais,
            'total_leitos' => $gabineteProvincial->total_leitos,
            'total_leitos_disponiveis' => $gabineteProvincial->total_leitos_disponiveis,
            'ocupacao' => $gabineteProvincial->total_leitos > 0 
                ? round(($gabineteProvincial->total_leitos - $gabineteProvincial->total_leitos_disponiveis) / $gabineteProvincial->total_leitos * 100, 2)
                : 0
        ];

        return response()->json($estatisticas);
    }
}
