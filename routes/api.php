<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\PacienteController;
use App\Http\Controllers\Api\TriagemController;
use App\Http\Controllers\Api\EncaminhamentoController;
use App\Http\Controllers\Api\RelatorioController;
use App\Http\Controllers\Api\DirecaoMunicipalController;
use App\Http\Controllers\Api\GabineteProvincialController;
use App\Http\Controllers\Api\AmbulanciaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RemocaoPacienteController;
use App\Http\Controllers\RemocaoAmbulanciaController;


// Rotas públicas de autenticação
Route::post('/login', [AuthController::class, 'login']);


// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Autenticação
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Pacientes
    Route::apiResource('pacientes', PacienteController::class);
    Route::post('/pacientes/{id}/restore', [PacienteController::class, 'restore']);
    Route::get('/pacientes/{paciente}/triagens', [PacienteController::class, 'triagens']);

    // Triagens
    Route::apiResource('triagens', TriagemController::class);
    Route::post('/triagens/{id}/restore', [TriagemController::class, 'restore']);
    Route::get('/triagens/estatisticas/por-risco', [TriagemController::class, 'porRisco']);
    Route::get('/triagens/{triagem}/qr', [\App\Http\Controllers\Api\TriagemController::class, 'qr']);

    // Hospitais
    Route::apiResource('hospitais', HospitalController::class);
    Route::post('/hospitais/{id}/restore', [HospitalController::class, 'restore']);
    Route::get('/hospitais/nearby', [HospitalController::class, 'nearby']);
    Route::get('/hospitais/estatisticas', [HospitalController::class, 'estatisticas']);

    // Encaminhamentos
    Route::apiResource('encaminhamentos', EncaminhamentoController::class);
    Route::post('/encaminhamentos/{id}/restore', [EncaminhamentoController::class, 'restore']);
    Route::get('/encaminhamentos/estatisticas', [EncaminhamentoController::class, 'estatisticas']);

    // Relatórios
    Route::post('/relatorios/gerar', [RelatorioController::class, 'gerar']);

    // Gabinetes Provinciais
    Route::apiResource('gabinetes-provinciais', GabineteProvincialController::class);
    Route::get('gabinetes-provinciais/{gabineteProvincial}/estatisticas', [GabineteProvincialController::class, 'estatisticas']);

    // Direções Municipais
    Route::apiResource('direcoes-municipais', DirecaoMunicipalController::class);
    Route::get('direcoes-municipais/{direcaoMunicipal}/estatisticas', [DirecaoMunicipalController::class, 'estatisticas']);

    // Rotas de Ambulâncias
    Route::get('/ambulancias', [AmbulanciaController::class, 'index']);
    Route::post('/ambulancias', [AmbulanciaController::class, 'store']);
    Route::get('/ambulancias/{ambulancia}', [AmbulanciaController::class, 'show']);
    Route::put('/ambulancias/{ambulancia}', [AmbulanciaController::class, 'update']);
    Route::delete('/ambulancias/{ambulancia}', [AmbulanciaController::class, 'destroy']);
    Route::get('/ambulancias/disponiveis', [AmbulanciaController::class, 'buscarDisponiveis']);

    // Usuários
    Route::get('/usuarios', [\App\Http\Controllers\Api\UserController::class, 'index']);
    Route::delete('/usuarios/{id}', [\App\Http\Controllers\Api\UserController::class, 'destroy']);
    Route::put('/usuarios/{id}', [\App\Http\Controllers\Api\UserController::class, 'update']);
    Route::patch('/usuarios/{id}', [\App\Http\Controllers\Api\UserController::class, 'update']);

    // Admin
    Route::post('/admin/permitir-acesso', [\App\Http\Controllers\Api\AdminController::class, 'permitirAcesso']);

    // Logs
    Route::get('/logs', [\App\Http\Controllers\Api\LogController::class, 'index']);
    Route::post('/logs', [\App\Http\Controllers\Api\LogController::class, 'store']);

    // Remoção de Pacientes
    Route::post('/remocao-pacientes', [RemocaoPacienteController::class, 'store']);
    Route::get('/remocao-pacientes/pendentes', [RemocaoPacienteController::class, 'pendentes']);
    Route::post('/remocao-pacientes/{id}/aprovar', [RemocaoPacienteController::class, 'aprovar']);
    Route::post('/remocao-pacientes/{id}/recusar', [RemocaoPacienteController::class, 'recusar']);
    Route::get('/remocao-pacientes/minhas', [RemocaoPacienteController::class, 'minhas']);

    // Remoção de Ambulâncias
    Route::post('/remocao-ambulancias', [\App\Http\Controllers\RemocaoAmbulanciaController::class, 'store']);
    Route::get('/remocao-ambulancias/pendentes', [\App\Http\Controllers\RemocaoAmbulanciaController::class, 'pendentes']);
    Route::post('/remocao-ambulancias/{id}/aprovar', [\App\Http\Controllers\RemocaoAmbulanciaController::class, 'aprovar']);
    Route::post('/remocao-ambulancias/{id}/recusar', [\App\Http\Controllers\RemocaoAmbulanciaController::class, 'recusar']);
    Route::get('/remocao-ambulancias/minhas', [RemocaoAmbulanciaController::class, 'minhas']);
});

// Fallback
Route::fallback(function () {
    return response()->json([
        'message' => 'Rota não encontrada. Verifique a URL e o método HTTP.'
    ], 404);
});
