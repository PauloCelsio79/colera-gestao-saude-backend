@extends('relatorios.layout', ['titulo' => 'Relatório de Ocupação dos Hospitais'])

@section('content')
<table>
    <thead>
        <tr>
            <th>Hospital</th>
            <th>Leitos Totais</th>
            <th>Leitos Disponíveis</th>
            <th>Taxa de Ocupação</th>
            <th>Total de Encaminhamentos</th>
            <th>Encaminhamentos Ativos</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dados as $item)
        <tr>
            <td>{{ $item->nome }}</td>
            <td>{{ $item->leitos_totais }}</td>
            <td>{{ $item->leitos_disponiveis }}</td>
            <td>{{ number_format($item->taxa_ocupacao, 2) }}%</td>
            <td>{{ $item->total_encaminhamentos }}</td>
            <td>{{ $item->encaminhamentos_ativos }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th>{{ $dados->sum('leitos_totais') }}</th>
            <th>{{ $dados->sum('leitos_disponiveis') }}</th>
            <th>{{ number_format($dados->sum('leitos_totais') > 0 ? (($dados->sum('leitos_totais') - $dados->sum('leitos_disponiveis')) / $dados->sum('leitos_totais') * 100) : 0, 2) }}%</th>
            <th>{{ $dados->sum('total_encaminhamentos') }}</th>
            <th>{{ $dados->sum('encaminhamentos_ativos') }}</th>
        </tr>
    </tfoot>
</table>
@endsection 