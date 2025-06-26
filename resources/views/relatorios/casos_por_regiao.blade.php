@extends('relatorios.layout', ['titulo' => 'Relatório de Casos por Região'])

@section('content')
<table>
    <thead>
        <tr>
            <th>Município</th>
            <th>Total de Casos</th>
            <th>Casos Graves</th>
            <th>% Casos Graves</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dados as $item)
        <tr>
            <td>{{ $item->municipio }}</td>
            <td>{{ $item->total_casos }}</td>
            <td>{{ $item->casos_graves }}</td>
            <td>{{ number_format(($item->casos_graves / $item->total_casos) * 100, 2) }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection 