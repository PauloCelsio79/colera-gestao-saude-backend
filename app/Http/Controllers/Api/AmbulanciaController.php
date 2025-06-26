<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ambulancia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AmbulanciaController extends Controller
{
    public function index(Request $request)
    {
        $query = Ambulancia::with(['hospital']);

        // Filtro por status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por hospital
        if ($request->has('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($orderBy, $order);

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placa' => 'required|string|unique:ambulancias',
            'modelo' => 'required|string',
            'hospital_id' => 'required|exists:hospitais,id',
            'status' => 'required|in:disponivel,em_deslocamento,ocupada,manutencao,inativa',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ambulancia = Ambulancia::create($request->all());

        return response()->json([
            'message' => 'Ambulância cadastrada com sucesso',
            'data' => $ambulancia->load('hospital')
        ], 201);
    }

    public function show(Ambulancia $ambulancia)
    {
        return response()->json([
            'data' => $ambulancia->load('hospital')
        ]);
    }

    public function update(Request $request, Ambulancia $ambulancia)
    {
        $validator = Validator::make($request->all(), [
            'placa' => 'sometimes|required|string|unique:ambulancias,placa,' . $ambulancia->id,
            'modelo' => 'sometimes|required|string',
            'hospital_id' => 'sometimes|required|exists:hospitais,id',
            'status' => 'sometimes|required|in:disponivel,em_deslocamento,ocupada,manutencao,inativa',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('status')) {
            $request->merge(['ultima_atualizacao' => now()]);
        }

        $ambulancia->update($request->all());

        return response()->json([
            'message' => 'Ambulância atualizada com sucesso',
            'data' => $ambulancia->fresh(['hospital'])
        ]);
    }

    public function destroy(Ambulancia $ambulancia)
    {
        // Verifica se a ambulância está em uso
        if ($ambulancia->status === 'em_deslocamento') {
            return response()->json([
                'message' => 'Não é possível excluir uma ambulância em deslocamento'
            ], 422);
        }

        $ambulancia->delete();

        return response()->json([
            'message' => 'Ambulância excluída com sucesso'
        ]);
    }

    public function buscarDisponiveis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'raio_km' => 'nullable|numeric|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ambulancias = Ambulancia::buscarDisponiveis(
            $request->latitude,
            $request->longitude,
            $request->get('raio_km', 10)
        );

        return response()->json([
            'data' => $ambulancias->values()
        ]);
    }
} 