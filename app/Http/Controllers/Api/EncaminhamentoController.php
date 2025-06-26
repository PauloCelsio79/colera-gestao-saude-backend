<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Encaminhamento\StoreEncaminhamentoRequest;
use App\Http\Requests\Encaminhamento\UpdateEncaminhamentoRequest;
use App\Models\Encaminhamento;
use App\Models\Hospital;
use App\Models\Triagem;
use App\Models\Ambulancia;
use Illuminate\Http\Request;

class EncaminhamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Encaminhamento::with(['triagem.paciente', 'hospital']);

        // Filtro por status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por hospital
        if ($request->has('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }

        // Filtro por data
        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $query->whereBetween('created_at', [$request->data_inicio, $request->data_fim]);
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($orderBy, $order);

        // Paginação
        $perPage = $request->get('per_page', 15);
        
        return response()->json($query->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEncaminhamentoRequest $request)
    {
        $data = $request->validated();

        // Verifica se a triagem já tem encaminhamento
        $triagem = Triagem::findOrFail($data['triagem_id']);
        if ($triagem->encaminhamento()->exists()) {
            return response()->json([
                'message' => 'Esta triagem já possui um encaminhamento'
            ], 422);
        }

        // Verifica se o hospital tem leitos disponíveis
        $hospital = Hospital::findOrFail($data['hospital_id']);
        if ($hospital->leitos_disponiveis <= 0) {
            return response()->json([
                'message' => 'Hospital não possui leitos disponíveis'
            ], 422);
        }

        // Se for caso grave, busca ambulância disponível
        if ($triagem->nivel_risco === 'alto') {
            $paciente = $triagem->paciente;
            $ambulancias = Ambulancia::buscarDisponiveis(
                $paciente->latitude,
                $paciente->longitude
            );

            if ($ambulancias->isEmpty()) {
                return response()->json([
                    'message' => 'Não há ambulâncias disponíveis próximas ao paciente'
                ], 422);
            }

            // Pega a ambulância mais próxima
            $ambulancia = $ambulancias->first();
            $data['ambulancia_id'] = $ambulancia->id;
            
            // Atualiza status da ambulância
            $ambulancia->update([
                'status' => 'em_deslocamento',
                'ultima_atualizacao' => now()
            ]);
        }

        // Adiciona a data de encaminhamento
        $data['data_encaminhamento'] = now();
        $data['status'] = 'em_deslocamento';

        // Cria o encaminhamento
        $encaminhamento = Encaminhamento::create($data);

        // Atualiza leitos disponíveis do hospital
            $hospital->decrement('leitos_disponiveis');

        return response()->json([
            'message' => 'Encaminhamento criado com sucesso',
            'data' => $encaminhamento->load(['hospital', 'ambulancia', 'triagem.paciente'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Encaminhamento $encaminhamento)
    {
        $encaminhamento->load([
            'triagem.paciente',
            'hospital',
            'ambulancia'
        ]);
        return response()->json([
            'encaminhamento' => $encaminhamento
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEncaminhamentoRequest $request, Encaminhamento $encaminhamento)
    {
        $data = $request->validated();
        $oldStatus = $encaminhamento->status;

        // Se está mudando para concluído, verifica leitos
        if (($data['status'] ?? $oldStatus) === 'concluido' && $oldStatus !== 'concluido') {
            $hospital = $encaminhamento->hospital;
            if ($hospital->leitos_disponiveis <= 0) {
                return response()->json([
                    'message' => 'Hospital não possui leitos disponíveis'
                ], 422);
            }
            $hospital->decrement('leitos_disponiveis');
        }

        // Se está mudando de concluído para outro status, incrementa leitos
        if ($oldStatus === 'concluido' && ($data['status'] ?? $oldStatus) !== 'concluido') {
            $encaminhamento->hospital->increment('leitos_disponiveis');
        }

        $encaminhamento->update($data);

        return response()->json([
            'message' => 'Encaminhamento atualizado com sucesso',
            'encaminhamento' => $encaminhamento->fresh(['triagem.paciente', 'hospital'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Encaminhamento $encaminhamento)
    {
        // Se estava concluído, devolve o leito
        if ($encaminhamento->status === 'concluido') {
            $encaminhamento->hospital->increment('leitos_disponiveis');
        }

        $encaminhamento->delete();

        return response()->json([
            'message' => 'Encaminhamento excluído com sucesso'
        ]);
    }

    public function restore($id)
    {
        $encaminhamento = Encaminhamento::withTrashed()->findOrFail($id);
        $encaminhamento->restore();

        // Se estava concluído, decrementa leito novamente
        if ($encaminhamento->status === 'concluido') {
            $hospital = $encaminhamento->hospital;
            if ($hospital->leitos_disponiveis <= 0) {
                return response()->json([
                    'message' => 'Hospital não possui leitos disponíveis para restaurar este encaminhamento'
                ], 422);
            }
            $hospital->decrement('leitos_disponiveis');
        }

        return response()->json([
            'message' => 'Encaminhamento restaurado com sucesso',
            'encaminhamento' => $encaminhamento->load(['triagem.paciente', 'hospital'])
        ]);
    }

    public function estatisticas()
    {
        $total = Encaminhamento::count();
        $pendentes = Encaminhamento::where('status', 'pendente')->count();
        $concluidos = Encaminhamento::where('status', 'concluido')->count();
        $cancelados = Encaminhamento::where('status', 'cancelado')->count();

        $porHospital = Hospital::withCount(['encaminhamentos as total_encaminhamentos'])
            ->withCount(['encaminhamentos as encaminhamentos_pendentes' => function ($query) {
                $query->where('status', 'pendente');
            }])
            ->withCount(['encaminhamentos as encaminhamentos_concluidos' => function ($query) {
                $query->where('status', 'concluido');
            }])
            ->get();

        return response()->json([
            'total' => $total,
            'por_status' => [
                'pendentes' => $pendentes,
                'concluidos' => $concluidos,
                'cancelados' => $cancelados
            ],
            'por_hospital' => $porHospital
        ]);
    }
}
