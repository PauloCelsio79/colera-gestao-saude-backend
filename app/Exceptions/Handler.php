<?php

namespace App\Exceptions;

use App\Services\LogService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e)) {
                $mensagem = match(true) {
                    $e instanceof ValidationException => 'Erro de validação de dados',
                    $e instanceof AuthenticationException => 'Erro de autenticação',
                    $e instanceof HttpException => 'Erro na requisição HTTP',
                    default => 'Erro interno do sistema'
                };

                app(LogService::class)->erro($mensagem, [
                    'tipo' => get_class($e),
                    'mensagem' => $e->getMessage(),
                    'arquivo' => $e->getFile(),
                    'linha' => $e->getLine()
                ]);
            }
        });

        $this->renderable(function (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Erro ao acessar o banco de dados',
                'status' => 500
            ], 500);
        });

        $this->renderable(function (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json([
                'message' => 'Recurso não encontrado',
                'status' => 404
            ], 404);
        });

        $this->renderable(function (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Erro na requisição',
                'status' => $e->getStatusCode()
            ], $e->getStatusCode());
        });
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'message' => 'Não autenticado. Por favor, faça login para acessar este recurso.',
            'status' => 401
        ], 401);
    }
}
