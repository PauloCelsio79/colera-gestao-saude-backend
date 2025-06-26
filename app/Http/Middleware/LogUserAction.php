<?php

namespace App\Http\Middleware;

use App\Services\LogService;
use Closure;
use Illuminate\Http\Request;

class LogUserAction
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('delete')) {
            $rota = $request->route()->getName();
            $metodo = $request->method();
            $acao = $this->determinarAcao($metodo);
            $recurso = $this->determinarRecurso($rota);

            $detalhes = [
                'recurso' => $recurso,
                'id' => $request->route('id') ?? $request->route('paciente') ?? $request->route('triagem') ?? $request->route('encaminhamento'),
                'dados' => $request->except(['password', 'password_confirmation']),
                'status' => $response->status()
            ];

            $this->logService->sistema($acao, $detalhes);
        }

        return $response;
    }

    /**
     * Determina a ação baseada no método HTTP
     */
    private function determinarAcao(string $metodo): string
    {
        return match($metodo) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'outro'
        };
    }

    /**
     * Determina o recurso baseado na rota
     */
    private function determinarRecurso(?string $rota): string
    {
        if (!$rota) return 'desconhecido';

        $partes = explode('.', $rota);
        return $partes[0] ?? 'desconhecido';
    }
}
