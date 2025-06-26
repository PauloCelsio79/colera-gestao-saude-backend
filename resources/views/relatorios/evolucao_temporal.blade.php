@extends('relatorios.layout', ['titulo' => 'Relatório de Evolução Temporal'])

@section('content')
<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Total de Casos</th>
            <th>Casos Graves</th>
            <th>Casos Médios</th>
            <th>Casos Leves</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dados as $item)
        <tr>
            <td>{{ \Carbon\Carbon::parse($item->data)->format('d/m/Y') }}</td>
            <td>{{ $item->total_casos }}</td>
            <td>{{ $item->casos_graves }}</td>
            <td>{{ $item->casos_medios }}</td>
            <td>{{ $item->casos_leves }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th>{{ $dados->sum('total_casos') }}</th>
            <th>{{ $dados->sum('casos_graves') }}</th>
            <th>{{ $dados->sum('casos_medios') }}</th>
            <th>{{ $dados->sum('casos_leves') }}</th>
        </tr>
    </tfoot>
</table>
@endsection 