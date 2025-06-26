<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogController extends Controller
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipo' => 'nullable|in:triagem,encaminhamento,erro,sistema',
                'nivel' => 'nullable|in:info,warning,error,critical',
                'usuario_id' => 'nullable|exists:users,id',
                'data_inicio' => 'nullable|date',
                'data_fim' => 'nullable|date|after_or_equal:data_inicio',
                'per_page' => 'nullable|integer|min:1|max:100',
                'order_by' => 'nullable|in:created_at,updated_at,tipo,nivel,metodo,usuario,mensagem,rota,ip',
                'order' => 'nullable|in:asc,desc',
                'busca' => 'nullable|string|max:255',
                'metodo' => 'nullable|in:GET,POST,PUT,DELETE,PATCH',
                'status' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Log::with('usuario');

            // Filtro por tipo
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            // Filtro por nível
            if ($request->filled('nivel')) {
                $query->where('nivel', $request->nivel);
            }

            // Filtro por usuário
            if ($request->filled('usuario_id')) {
                $query->where('usuario_id', $request->usuario_id);
            }

            // Filtro por período
            if ($request->filled('data_inicio')) {
                $query->whereDate('created_at', '>=', $request->data_inicio);
            }

            if ($request->filled('data_fim')) {
                $query->whereDate('created_at', '<=', $request->data_fim);
            }

            // Filtro por método HTTP
            if ($request->filled('metodo')) {
                $query->where('metodo', $request->metodo);
            }

            // Filtro por status
            if ($request->filled('status')) {
                $query->whereJsonContains('detalhes->status', $request->status);
            }

            // Busca por texto
            if ($request->filled('busca')) {
                $busca = $request->busca;
                $query->where(function($q) use ($busca) {
                    $q->where('mensagem', 'like', "%{$busca}%")
                      ->orWhere('rota', 'like', "%{$busca}%")
                      ->orWhereHas('usuario', function($q) use ($busca) {
                          $q->where('name', 'like', "%{$busca}%")
                            ->orWhere('email', 'like', "%{$busca}%");
                      });
                });
            }

            // Ordenação
            $orderBy = $request->order_by ?? 'created_at';
            $order = $request->order ?? 'desc';

            // Tratamento especial para ordenação por usuário
            if ($orderBy === 'usuario') {
                $query->join('users', 'logs.usuario_id', '=', 'users.id')
                      ->select('logs.*')
                      ->orderBy('users.name', $order);
            } else {
                $query->orderBy($orderBy, $order);
            }

            // Paginação
            $perPage = $request->per_page ?? 15;
            $logs = $query->paginate($perPage);

            return response()->json([
                'data' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total()
                ]
            ]);

        } catch (\Exception $e) {
            $this->logService->erro('Erro ao buscar logs', [
                'erro' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Erro ao buscar logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipo' => 'required|in:triagem,encaminhamento,erro,sistema',
                'mensagem' => 'required|string',
                'detalhes' => 'nullable|array',
                'nivel' => 'nullable|in:info,warning,error,critical'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $log = $this->logService->registrar(
                $request->tipo,
                $request->mensagem,
                $request->detalhes ?? [],
                $request->nivel ?? 'info'
            );

            return response()->json([
                'message' => 'Log registrado com sucesso',
                'log' => $log
            ], 201);

        } catch (\Exception $e) {
            $this->logService->erro('Erro ao registrar log', [
                'erro' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Erro ao registrar log',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 