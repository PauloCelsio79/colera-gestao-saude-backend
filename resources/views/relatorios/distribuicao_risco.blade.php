@extends('relatorios.layout', ['titulo' => 'Relatório de Distribuição por Nível de Risco'])

@section('content')
<table>
    <thead>
        <tr>
            <th>Nível de Risco</th>
            <th>Total de Casos</th>
            <th>Percentual</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dados as $item)
        <tr>
            <td>{{ ucfirst($item->nivel_risco) }}</td>
            <td>{{ $item->total }}</td>
            <td>{{ number_format($item->percentual, 2) }}%</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th>{{ $dados->sum('total') }}</th>
            <th>100%</th>
        </tr>
    </tfoot>
</table>
@endsection 