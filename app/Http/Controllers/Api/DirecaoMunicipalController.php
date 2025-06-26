<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DirecaoMunicipal\StoreDirecaoMunicipalRequest;
use App\Http\Requests\DirecaoMunicipal\UpdateDirecaoMunicipalRequest;
use App\Models\DirecaoMunicipal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirecaoMunicipalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DirecaoMunicipal::with('gabineteProvincial')
            ->withCount('hospitais');

        if ($request->has('gabinete_provincial_id')) {
            $query->where('gabinete_provincial_id', $request->gabinete_provincial_id);
        }

        $direcoes = $query->get();

        return response()->json($direcoes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDirecaoMunicipalRequest $request): JsonResponse
    {
        $direcao = DirecaoMunicipal::create($request->validated());

        return response()->json($direcao, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DirecaoMunicipal $direcaoMunicipal): JsonResponse
    {
        $direcaoMunicipal->load(['gabineteProvincial', 'hospitais']);
        $direcaoMunicipal->loadCount('hospitais');

        return response()->json($direcaoMunicipal);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDirecaoMunicipalRequest $request, DirecaoMunicipal $direcaoMunicipal): JsonResponse
    {
        $direcaoMunicipal->update($request->validated());

        return response()->json($direcaoMunicipal);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DirecaoMunicipal $direcaoMunicipal): JsonResponse
    {
        if ($direcaoMunicipal->hospitais()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir uma direção municipal que possui hospitais'
            ], 422);
        }

        $direcaoMunicipal->delete();

        return response()->json(null, 204);
    }

    public function estatisticas(DirecaoMunicipal $direcaoMunicipal): JsonResponse
    {
        $estatisticas = [
            'total_hospitais' => $direcaoMunicipal->total_hospitais,
            'total_leitos' => $direcaoMunicipal->total_leitos,
            'total_leitos_disponiveis' => $direcaoMunicipal->total_leitos_disponiveis,
            'total_encaminhamentos' => $direcaoMunicipal->total_encaminhamentos,
            'ocupacao' => $direcaoMunicipal->total_leitos > 0 
                ? round(($direcaoMunicipal->total_leitos - $direcaoMunicipal->total_leitos_disponiveis) / $direcaoMunicipal->total_leitos * 100, 2)
                : 0
        ];

        return response()->json($estatisticas);
    }
}
