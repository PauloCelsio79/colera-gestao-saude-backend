<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class RelatorioExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $dados;
    protected $tipo;
    protected $headings;

    public function __construct($dados, string $tipo)
    {
        $this->dados = $dados;
        $this->tipo = $tipo;
        $this->setHeadings();
    }

    public function collection()
    {
        return new Collection($this->dados);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        switch ($this->tipo) {
            case 'casos_por_regiao':
                return [
                    $row->municipio,
                    $row->total_casos,
                    $row->casos_graves
                ];

            case 'evolucao_temporal':
                return [
                    $row->data,
                    $row->total_casos,
                    $row->casos_graves,
                    $row->casos_medios,
                    $row->casos_leves
                ];

            case 'distribuicao_risco':
                return [
                    $row->nivel_risco,
                    $row->total,
                    number_format($row->percentual, 2) . '%'
                ];

            case 'ocupacao_hospitais':
                return [
                    $row->nome,
                    $row->leitos_totais,
                    $row->leitos_disponiveis,
                    number_format($row->taxa_ocupacao, 2) . '%',
                    $row->total_encaminhamentos,
                    $row->encaminhamentos_ativos
                ];

            case 'eficiencia_encaminhamentos':
                return [
                    $row->hospital->nome,
                    $row->total_encaminhamentos,
                    $row->concluidos,
                    $row->cancelamentos,
                    number_format($row->taxa_sucesso, 2) . '%',
                    $row->tempo_medio_chegada_minutos ? $row->tempo_medio_chegada_minutos . ' min' : 'N/A'
                ];

            default:
                return [];
        }
    }

    public function title(): string
    {
        $titulos = [
            'casos_por_regiao' => 'Casos por Região',
            'evolucao_temporal' => 'Evolução Temporal',
            'distribuicao_risco' => 'Distribuição por Nível de Risco',
            'ocupacao_hospitais' => 'Ocupação dos Hospitais',
            'eficiencia_encaminhamentos' => 'Eficiência dos Encaminhamentos'
        ];

        return $titulos[$this->tipo] ?? 'Relatório';
    }

    protected function setHeadings(): void
    {
        switch ($this->tipo) {
            case 'casos_por_regiao':
                $this->headings = ['Município', 'Total de Casos', 'Casos Graves'];
                break;

            case 'evolucao_temporal':
                $this->headings = ['Data', 'Total de Casos', 'Casos Graves', 'Casos Médios', 'Casos Leves'];
                break;

            case 'distribuicao_risco':
                $this->headings = ['Nível de Risco', 'Total', 'Percentual'];
                break;

            case 'ocupacao_hospitais':
                $this->headings = ['Hospital', 'Leitos Totais', 'Leitos Disponíveis', 'Taxa de Ocupação', 'Total de Encaminhamentos', 'Encaminhamentos Ativos'];
                break;

            case 'eficiencia_encaminhamentos':
                $this->headings = ['Hospital', 'Total de Encaminhamentos', 'Concluídos', 'Cancelados', 'Taxa de Sucesso', 'Tempo Médio de Chegada'];
                break;

            default:
                $this->headings = [];
        }
    }
}
