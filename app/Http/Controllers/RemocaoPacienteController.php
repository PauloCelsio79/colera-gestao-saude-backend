<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RemocaoPaciente;
use App\Models\Paciente;
use App\Models\Triagem;
use App\Models\Encaminhamento;
use Illuminate\Support\Facades\DB;

class RemocaoPacienteController extends Controller
{
    // Criar solicitação de remoção
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'motivo' => 'nullable|string',
        ]);
        $solicitacao = RemocaoPaciente::create([
            'paciente_id' => $request->paciente_id,
            'solicitante_id' => $request->user()->id,
            'motivo' => $request->motivo,
            'status' => 'pendente',
        ]);
        return response()->json(['message' => 'Solicitação de remoção criada.', 'solicitacao' => $solicitacao], 201);
    }

    // Listar solicitações pendentes
    public function pendentes()
    {
        $solicitacoes = RemocaoPaciente::with(['paciente.triagens', 'solicitante'])
            ->where('status', 'pendente')
            ->get();
        return response()->json($solicitacoes);
    }

    // Aprovar solicitação e remover paciente e registros relacionados
    public function aprovar($id)
    {
        $solicitacao = RemocaoPaciente::where('id', $id)->where('status', 'pendente')->firstOrFail();
        DB::transaction(function () use ($solicitacao) {
            $paciente = $solicitacao->paciente;
            // Apaga triagens e encaminhamentos relacionados
            foreach ($paciente->triagens as $triagem) {
                $triagem->encaminhamento()->delete();
                $triagem->delete();
            }
            $paciente->delete();
            $solicitacao->status = 'aprovada';
            $solicitacao->save();
        });
        return response()->json(['message' => 'Paciente e registros removidos. Solicitação aprovada.']);
    }

    // Recusar solicitação
    public function recusar($id)
    {
        $solicitacao = RemocaoPaciente::where('id', $id)->where('status', 'pendente')->firstOrFail();
        $solicitacao->status = 'recusada';
        $solicitacao->save();
        return response()->json(['message' => 'Solicitação recusada.']);
    }

    public function minhas(Request $request)
    {
        $solicitacoes = \App\Models\RemocaoPaciente::with(['paciente'])
            ->where('solicitante_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($solicitacoes);
    }
}
