<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Triagem\StoreTriagemRequest;
use App\Http\Requests\Triagem\UpdateTriagemRequest;
use App\Models\Triagem;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TriagemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Triagem::with(['paciente', 'enfermeiro']);

        // Filtro por nível de risco
        if ($request->has('nivel_risco')) {
            $query->where('nivel_risco', $request->nivel_risco);
        }

        // Filtro por data
        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $query->whereBetween('created_at', [$request->data_inicio, $request->data_fim]);
        }

        // Filtro por paciente
        if ($request->has('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
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
    public function store(StoreTriagemRequest $request)
    {
        $data = $request->validated();
        
        // Adiciona o ID do médico que está realizando a triagem
        $data['user_id'] = auth()->id();
        
        // Gera QR Code se o nível de risco for alto
        if ($data['nivel_risco'] === 'alto') {
            $data['qr_code'] = $this->generateQRCode($data);
        }

        $triagem = Triagem::create($data);

        // Se for alto risco, já prepara para encaminhamento
        if ($triagem->nivel_risco === 'alto') {
            $this->criarEncaminhamento($triagem);
        }

        return response()->json([
            'message' => 'Triagem realizada com sucesso',
            'triagem' => $triagem->load(['paciente', 'enfermeiro'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Triagem $triagem)
    {
        return response()->json([
            'triagem' => $triagem->load(['paciente', 'enfermeiro', 'encaminhamento.hospital'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTriagemRequest $request, Triagem $triagem)
    {
        // Verifica se o usuário é médico
        if (!auth()->user()->isEnfermeiro()) {
            return response()->json([
                'message' => 'Apenas médicos podem alterar triagens'
            ], 403);
        }

        $data = $request->validated();

        // Se mudou para alto risco e não tinha QR Code, gera
        if (($data['nivel_risco'] ?? $triagem->nivel_risco) === 'alto' && !$triagem->qr_code) {
            $data['qr_code'] = $this->generateQRCode($data);
        }

        $triagem->update($data);

        // Se mudou para alto risco, verifica necessidade de encaminhamento
        if (($data['nivel_risco'] ?? $triagem->nivel_risco) === 'alto' && !$triagem->encaminhamento) {
            $this->criarEncaminhamento($triagem);
        }

        return response()->json([
            'message' => 'Triagem atualizada com sucesso',
            'triagem' => $triagem->fresh(['paciente', 'enfermeiro', 'encaminhamento.hospital'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Triagem $triagem)
    {
        // Verifica se o usuário é médico ou admin
        if (!auth()->user()->isEnfermeiro() && !auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'Apenas médicos e administradores podem excluir triagens'
            ], 403);
        }

        $triagem->delete();

        return response()->json([
            'message' => 'Triagem excluída com sucesso'
        ]);
    }

    public function restore($id)
    {
        $triagem = Triagem::withTrashed()->findOrFail($id);
        $triagem->restore();

        return response()->json([
            'message' => 'Triagem restaurada com sucesso',
            'triagem' => $triagem->load(['paciente', 'enfermeiro'])
        ]);
    }

    /**
     * Retorna os dados para geração do QR Code (apenas para alto risco)
     */
    public function qr(Triagem $triagem)
    {
        if ($triagem->nivel_risco !== 'alto') {
            return response()->json(['message' => 'QR Code disponível apenas para triagens de alto risco.'], 403);
        }
        $paciente = $triagem->paciente;
        $dados = [
            'nome' => $paciente->nome,
            'bi' => $paciente->bi_numero,
            'telefone' => $paciente->telefone,
            'resultado' => $triagem->nivel_risco
        ];
        return response()->json([
            'dados' => $dados
        ]);
    }

    protected function generateQRCode(array $data): string
    {
        // Gera string JSON com os dados do paciente e resultado
        $paciente = \App\Models\Paciente::find($data['paciente_id'] ?? null);
        if (!$paciente) return '';
        $conteudo = [
            'nome' => $paciente->nome,
            'bi' => $paciente->bi_numero,
            'telefone' => $paciente->telefone,
            'resultado' => $data['nivel_risco'] ?? ''
        ];
        // Retorna o JSON (pode ser usado para gerar o QR real depois)
        return json_encode($conteudo);
    }

    protected function criarEncaminhamento(Triagem $triagem)
    {
        // Verifica se já tem encaminhamento
        if ($triagem->encaminhamento()->exists()) {
            return;
        }

        // Busca hospitais próximos com leitos disponíveis
        $paciente = $triagem->paciente;
        if (!$paciente->latitude || !$paciente->longitude) {
            return;
        }

        $hospitais = Hospital::ativos()
            ->comLeitosDisponiveis()
            ->get()
            ->map(function ($hospital) use ($paciente) {
                $hospital->distancia = $hospital->distanciaAte($paciente->latitude, $paciente->longitude);
                return $hospital;
            })
            ->sortBy('distancia')
            ->take(1)
            ->first();

        if (!$hospitais) {
            return;
        }

        // Cria o encaminhamento
        $triagem->encaminhamento()->create([
            'hospital_id' => $hospitais->id,
            'status' => 'pendente',
            'data_encaminhamento' => now()
        ]);

        return $hospitais;
    }

    public function porRisco(Request $request)
    {
        $query = Triagem::select('nivel_risco')
            ->selectRaw('count(*) as total')
            ->groupBy('nivel_risco');

        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $query->whereBetween('created_at', [$request->data_inicio, $request->data_fim]);
        }

        return response()->json([
            'estatisticas' => $query->get()
        ]);
    }
}
