<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hospital\StoreHospitalRequest;
use App\Http\Requests\Hospital\UpdateHospitalRequest;
use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    public function index(Request $request)
    {
        $query = Hospital::query();

        // Filtro por tipo
        if ($request->has('tipo') && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por disponibilidade de leitos
        if ($request->has('com_leitos')) {
            $query->where('leitos_disponiveis', '>', 0);
        }

        // Filtro por status
        if ($request->has('ativo')) {
            $query->where('ativo', $request->boolean('ativo'));
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'nome');
        $order = $request->get('order', 'asc');
        $query->orderBy($orderBy, $order);

        // Paginação
        $perPage = $request->get('per_page', 15);
        
        return response()->json($query->paginate($perPage));
    }

    public function store(StoreHospitalRequest $request)
    {
        $hospital = Hospital::create($request->validated());

        return response()->json([
            'message' => 'Hospital cadastrado com sucesso',
            'hospital' => $hospital
        ], 201);
    }

    public function show(Hospital $hospital)
    {
        return response()->json([
            'hospital' => $hospital->load('encaminhamentos')
        ]);
    }

    public function update(UpdateHospitalRequest $request, Hospital $hospital)
    {
        $hospital->update($request->validated());

        return response()->json([
            'message' => 'Hospital atualizado com sucesso',
            'hospital' => $hospital->fresh()
        ]);
    }

    public function destroy(Hospital $hospital)
    {
        // Verifica se há encaminhamentos pendentes
        if ($hospital->encaminhamentos()->pendentes()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir um hospital com encaminhamentos pendentes'
            ], 422);
        }

        $hospital->delete();

        return response()->json([
            'message' => 'Hospital excluído com sucesso'
        ]);
    }

    public function restore($id)
    {
        $hospital = Hospital::withTrashed()->findOrFail($id);
        $hospital->restore();

        return response()->json([
            'message' => 'Hospital restaurado com sucesso',
            'hospital' => $hospital
        ]);
    }

    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:10']
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $limit = $request->get('limit', 3);

        // Busca hospitais ativos e com leitos disponíveis
        $hospitais = Hospital::ativos()
            ->comLeitosDisponiveis()
            ->get()
            ->map(function ($hospital) use ($latitude, $longitude) {
                $hospital->distancia = $hospital->distanciaAte($latitude, $longitude);
                return $hospital;
            })
            ->sortBy('distancia')
            ->take($limit)
            ->values();

        return response()->json([
            'hospitais' => $hospitais
        ]);
    }

    public function estatisticas(Request $request)
    {
        $stats = [
            'total_hospitais' => Hospital::count(),
            'total_leitos' => Hospital::sum('leitos_totais'),
            'leitos_disponiveis' => Hospital::sum('leitos_disponiveis'),
            'por_tipo' => Hospital::selectRaw('tipo, count(*) as total')
                ->groupBy('tipo')
                ->get()
        ];

        return response()->json([
            'estatisticas' => $stats
        ]);
    }
} 