<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogService
{
    /**
     * Registra um novo log no sistema
     */
    public function registrar(
        string $tipo,
        string $mensagem,
        array $detalhes = [],
        string $nivel = 'info'
    ): Log {
        $request = request();

        return Log::create([
            'tipo' => $tipo,
            'mensagem' => $mensagem,
            'detalhes' => $detalhes,
            'usuario_id' => Auth::id(),
            'nivel' => $nivel,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'rota' => $request->route() ? $request->route()->getName() : null,
            'metodo' => $request->method()
        ]);
    }

    /**
     * Registra um log de erro
     */
    public function erro(string $mensagem, array $detalhes = []): Log
    {
        return $this->registrar('erro', $mensagem, $detalhes, 'error');
    }

    /**
     * Registra um log de triagem
     */
    public function triagem(string $acao, array $detalhes = []): Log
    {
        $mensagem = match($acao) {
            'create' => 'Nova triagem registrada',
            'update' => 'Triagem atualizada',
            'delete' => 'Triagem removida',
            'restore' => 'Triagem restaurada',
            default => 'Ação realizada na triagem'
        };

        return $this->registrar('triagem', $mensagem, $detalhes);
    }

    /**
     * Registra um log de encaminhamento
     */
    public function encaminhamento(string $acao, array $detalhes = []): Log
    {
        $mensagem = match($acao) {
            'create' => 'Novo encaminhamento registrado',
            'update' => 'Encaminhamento atualizado',
            'delete' => 'Encaminhamento removido',
            'restore' => 'Encaminhamento restaurado',
            default => 'Ação realizada no encaminhamento'
        };

        return $this->registrar('encaminhamento', $mensagem, $detalhes);
    }

    /**
     * Registra um log do sistema
     */
    public function sistema(string $acao, array $detalhes = []): Log
    {
        $mensagem = match($acao) {
            'login' => 'Usuário fez login no sistema',
            'logout' => 'Usuário fez logout do sistema',
            'create' => 'Novo registro criado',
            'update' => 'Registro atualizado',
            'delete' => 'Registro removido',
            'restore' => 'Registro restaurado',
            default => 'Ação realizada no sistema'
        };

        return $this->registrar('sistema', $mensagem, $detalhes);
    }

    /**
     * Busca logs com filtros
     */
    public function buscar(array $filtros = [])
    {
        $query = Log::query();

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['nivel'])) {
            $query->where('nivel', $filtros['nivel']);
        }

        if (!empty($filtros['usuario_id'])) {
            $query->where('usuario_id', $filtros['usuario_id']);
        }

        if (!empty($filtros['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['data_inicio']);
        }

        if (!empty($filtros['data_fim'])) {
            $query->whereDate('created_at', '<=', $filtros['data_fim']);
        }

        $perPage = $filtros['per_page'] ?? 15;
        $orderBy = $filtros['order_by'] ?? 'created_at';
        $order = $filtros['order'] ?? 'desc';

        return $query->orderBy($orderBy, $order)
            ->with('usuario')
            ->paginate($perPage);
    }
} 