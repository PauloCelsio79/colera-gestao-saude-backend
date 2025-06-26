<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paciente\StorePacienteRequest;
use App\Http\Requests\Paciente\UpdatePacienteRequest;
use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Paciente::query();

        // Filtro por nome
        if ($request->has('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        // Filtro por BI
        if ($request->has('bi')) {
            $query->where('bi_numero', 'like', '%' . $request->bi . '%');
        }

        // Filtro por status (ativo/inativo)
        if ($request->has('ativo')) {
            $query->where('ativo', $request->boolean('ativo'));
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($orderBy, $order);

        // Paginação
        $perPage = $request->get('per_page', 15);
        
        return response()->json($query->paginate($perPage));
    }

    public function store(StorePacienteRequest $request)
    {
        $paciente = Paciente::create($request->validated());

        return response()->json([
            'message' => 'Paciente cadastrado com sucesso',
            'paciente' => $paciente
        ], 201);
    }

    public function show(Paciente $paciente)
    {
        return response()->json([
            'paciente' => $paciente->load('triagens')
        ]);
    }

    public function update(UpdatePacienteRequest $request, Paciente $paciente)
    {
        $paciente->update($request->validated());

        return response()->json([
            'message' => 'Paciente atualizado com sucesso',
            'paciente' => $paciente->fresh()
        ]);
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return response()->json([
            'message' => 'Paciente excluído com sucesso'
        ]);
    }

    public function restore($id)
    {
        $paciente = Paciente::withTrashed()->findOrFail($id);
        $paciente->restore();

        return response()->json([
            'message' => 'Paciente restaurado com sucesso',
            'paciente' => $paciente
        ]);
    }

    public function triagens(Paciente $paciente)
    {
        return response()->json([
            'triagens' => $paciente->triagens()->with('medico')->paginate(15)
        ]);
    }
}
