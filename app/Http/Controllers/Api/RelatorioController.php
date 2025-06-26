<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorio\GerarRelatorioRequest;
use App\Models\Triagem;
use App\Models\Hospital;
use App\Models\Encaminhamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RelatorioExport;

class RelatorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function gerar(GerarRelatorioRequest $request)
    {
        $data = $request->validated();
        $resultado = [];

        // Aplica filtros de período em todas as queries
        $periodo = [
            Carbon::parse($data['periodo_inicio'])->startOfDay(),
            Carbon::parse($data['periodo_fim'])->endOfDay()
        ];

        switch ($data['tipo']) {
            case 'casos_por_regiao':
                $resultado = $this->casosPorRegiao($periodo, $data['filtros'] ?? []);
                break;
            case 'evolucao_temporal':
                $resultado = $this->evolucaoTemporal($periodo, $data['filtros'] ?? []);
                break;
            case 'distribuicao_risco':
                $resultado = $this->distribuicaoRisco($periodo, $data['filtros'] ?? []);
                break;
            case 'ocupacao_hospitais':
                $resultado = $this->ocupacaoHospitais($periodo, $data['filtros'] ?? []);
                break;
            case 'eficiencia_encaminhamentos':
                $resultado = $this->eficienciaEncaminhamentos($periodo, $data['filtros'] ?? []);
                break;
        }

        // Retorna no formato solicitado
        return $this->formatarSaida($resultado, $data['formato'], $data['tipo']);
    }

    protected function casosPorRegiao(array $periodo, array $filtros)
    {
        $query = Triagem::join('pacientes', 'triagens.paciente_id', '=', 'pacientes.id')
            ->whereBetween('triagens.created_at', $periodo)
            ->select(
                'pacientes.municipio',
                DB::raw('count(*) as total_casos'),
                DB::raw('count(case when triagens.nivel_risco = "alto" then 1 end) as casos_graves')
            )
            ->groupBy('pacientes.municipio');

        if (isset($filtros['nivel_risco'])) {
            $query->where('triagens.nivel_risco', $filtros['nivel_risco']);
        }

        return $query->get();
    }

    protected function evolucaoTemporal(array $periodo, array $filtros)
    {
        return Triagem::whereBetween('created_at', $periodo)
            ->select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('count(*) as total_casos'),
                DB::raw('count(case when nivel_risco = "alto" then 1 end) as casos_graves'),
                DB::raw('count(case when nivel_risco = "medio" then 1 end) as casos_medios'),
                DB::raw('count(case when nivel_risco = "baixo" then 1 end) as casos_leves')
            )
            ->when(isset($filtros['nivel_risco']), function ($query) use ($filtros) {
                return $query->where('nivel_risco', $filtros['nivel_risco']);
            })
            ->groupBy('data')
            ->orderBy('data')
            ->get();
    }

    protected function distribuicaoRisco(array $periodo, array $filtros)
    {
        $query = Triagem::whereBetween('created_at', $periodo)
            ->select(
                'nivel_risco',
                DB::raw('count(*) as total'),
                DB::raw('(count(*) * 100.0 / (select count(*) from triagens where deleted_at is null)) as percentual')
            )
            ->groupBy('nivel_risco');

        if (isset($filtros['hospital_id'])) {
            $query->whereHas('encaminhamento', function ($q) use ($filtros) {
                $q->where('hospital_id', $filtros['hospital_id']);
            });
        }

        return $query->get();
    }

    protected function ocupacaoHospitais(array $periodo, array $filtros)
    {
        return Hospital::withCount([
            'encaminhamentos as total_encaminhamentos' => function ($query) use ($periodo) {
                $query->whereBetween('created_at', $periodo);
            },
            'encaminhamentos as encaminhamentos_ativos' => function ($query) use ($periodo) {
                $query->where('status', 'concluido')
                    ->whereBetween('created_at', $periodo);
            }
        ])
        ->select('id', 'nome', 'leitos_totais', 'leitos_disponiveis')
        ->when(isset($filtros['hospital_id']), function ($query) use ($filtros) {
            return $query->where('id', $filtros['hospital_id']);
        })
        ->get()
        ->map(function ($hospital) {
            $hospital->taxa_ocupacao = $hospital->leitos_totais > 0 
                ? (($hospital->leitos_totais - $hospital->leitos_disponiveis) / $hospital->leitos_totais) * 100 
                : 0;
            return $hospital;
        });
    }

    protected function eficienciaEncaminhamentos(array $periodo, array $filtros)
    {
        $query = Encaminhamento::whereBetween('created_at', $periodo)
            ->select(
                'hospital_id',
                DB::raw('count(*) as total_encaminhamentos'),
                DB::raw('avg(case when status = "concluido" then time_to_sec(timediff(data_chegada, created_at)) end) as tempo_medio_chegada'),
                DB::raw('count(case when status = "cancelado" then 1 end) as cancelamentos'),
                DB::raw('count(case when status = "concluido" then 1 end) as concluidos')
            )
            ->groupBy('hospital_id');

        if (isset($filtros['hospital_id'])) {
            $query->where('hospital_id', $filtros['hospital_id']);
        }

        if (isset($filtros['status_encaminhamento'])) {
            $query->where('status', $filtros['status_encaminhamento']);
        }

        return $query->with('hospital:id,nome')->get()
            ->map(function ($item) {
                $item->taxa_sucesso = $item->total_encaminhamentos > 0 
                    ? ($item->concluidos / $item->total_encaminhamentos) * 100 
                    : 0;
                $item->tempo_medio_chegada_minutos = $item->tempo_medio_chegada 
                    ? round($item->tempo_medio_chegada / 60, 2) 
                    : null;
                return $item;
            });
    }

    protected function formatarSaida($dados, string $formato, string $tipo)
    {
        switch ($formato) {
            case 'json':
                return response()->json([
                    'tipo' => $tipo,
                    'dados' => $dados
                ]);

            case 'pdf':
                $pdf = PDF::loadView('relatorios.' . $tipo, [
                    'dados' => $dados,
                    'tipo' => $tipo,
                    'gerado_em' => now()->format('d/m/Y H:i:s')
                ]);
                return $pdf->download("relatorio_{$tipo}.pdf");

            case 'excel':
                return Excel::download(
                    new RelatorioExport($dados, $tipo),
                    "relatorio_{$tipo}.xlsx"
                );
        }
    }
}
