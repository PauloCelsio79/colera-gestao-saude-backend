@extends('relatorios.layout', ['titulo' => 'Relatório de Eficiência dos Encaminhamentos'])

@section('content')
<table>
    <thead>
        <tr>
            <th>Hospital</th>
            <th>Total de Encaminhamentos</th>
            <th>Concluídos</th>
            <th>Cancelados</th>
            <th>Taxa de Sucesso</th>
            <th>Tempo Médio de Chegada</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dados as $item)
        <tr>
            <td>{{ $item->hospital->nome }}</td>
            <td>{{ $item->total_encaminhamentos }}</td>
            <td>{{ $item->concluidos }}</td>
            <td>{{ $item->cancelamentos }}</td>
            <td>{{ number_format($item->taxa_sucesso, 2) }}%</td>
            <td>{{ $item->tempo_medio_chegada_minutos ? $item->tempo_medio_chegada_minutos . ' min' : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th>{{ $dados->sum('total_encaminhamentos') }}</th>
            <th>{{ $dados->sum('concluidos') }}</th>
            <th>{{ $dados->sum('cancelamentos') }}</th>
            <th>{{ number_format($dados->sum('total_encaminhamentos') > 0 ? ($dados->sum('concluidos') / $dados->sum('total_encaminhamentos') * 100) : 0, 2) }}%</th>
            <th>{{ number_format($dados->avg('tempo_medio_chegada_minutos'), 2) }} min</th>
        </tr>
    </tfoot>
</table>
@endsection 