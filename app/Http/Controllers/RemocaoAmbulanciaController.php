<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RemocaoAmbulancia;
use App\Models\Ambulancia;
use Illuminate\Support\Facades\DB;

class RemocaoAmbulanciaController extends Controller
{
    // Criar solicitação de remoção
    public function store(Request $request)
    {
        $request->validate([
            'ambulancia_id' => 'required|exists:ambulancias,id',
            'motivo' => 'nullable|string',
        ]);
        $solicitacao = RemocaoAmbulancia::create([
            'ambulancia_id' => $request->ambulancia_id,
            'solicitante_id' => $request->user()->id,
            'motivo' => $request->motivo,
            'status' => 'pendente',
        ]);
        return response()->json(['message' => 'Solicitação de remoção criada.', 'solicitacao' => $solicitacao], 201);
    }

    // Listar solicitações pendentes
    public function pendentes()
    {
        $solicitacoes = RemocaoAmbulancia::with(['ambulancia', 'solicitante'])
            ->where('status', 'pendente')
            ->get();
        return response()->json($solicitacoes);
    }

    // Aprovar solicitação e remover ambulância e registros relacionados
    public function aprovar($id)
    {
        $solicitacao = RemocaoAmbulancia::where('id', $id)->where('status', 'pendente')->firstOrFail();
        DB::transaction(function () use ($solicitacao) {
            $ambulancia = $solicitacao->ambulancia;
            // Apaga encaminhamentos relacionados à ambulância
            $ambulancia->encaminhamentos()->delete();
            $ambulancia->delete();
            $solicitacao->status = 'aprovada';
            $solicitacao->save();
        });
        return response()->json(['message' => 'Ambulância e registros removidos. Solicitação aprovada.']);
    }

    // Recusar solicitação
    public function recusar($id)
    {
        $solicitacao = RemocaoAmbulancia::where('id', $id)->where('status', 'pendente')->firstOrFail();
        $solicitacao->status = 'recusada';
        $solicitacao->save();
        return response()->json(['message' => 'Solicitação recusada.']);
    }

    public function minhas(Request $request)
    {
        $solicitacoes = \App\Models\RemocaoAmbulancia::with(['ambulancia'])
            ->where('solicitante_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($solicitacoes);
    }
}
