<?php

use App\Http\Controllers\LicitacaoController;
use App\Http\Controllers\PropostaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiTestController;

// Rota para a página inicial
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Rotas para licitações
Route::get('/licitacoes', [LicitacaoController::class, 'index'])->name('licitacoes.index');
Route::get('/licitacoes/sincronizar', [LicitacaoController::class, 'sincronizar'])->name('licitacoes.sincronizar');
Route::get('/licitacoes/{id}', [LicitacaoController::class, 'show'])->name('licitacoes.show');
Route::post('/licitacoes/{id}/interesse', [LicitacaoController::class, 'marcarInteresse'])->name('licitacoes.interesse');
Route::post('/licitacoes/{id}/analisada', [LicitacaoController::class, 'marcarAnalisada'])->name('licitacoes.analisada');

// Rotas para propostas
Route::resource('propostas', PropostaController::class);

// Rotas para clientes
Route::resource('clientes', ClienteController::class);

// Rotas de autenticação
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Aplicar middleware de autenticação para as rotas que requerem login
Route::middleware(['auth'])->group(function () {
    // Rota para a página inicial
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas para licitações
    Route::get('/licitacoes', [LicitacaoController::class, 'index'])->name('licitacoes.index');
    Route::get('/licitacoes/sincronizar', [LicitacaoController::class, 'sincronizar'])->name('licitacoes.sincronizar');
    Route::get('/licitacoes/{id}', [LicitacaoController::class, 'show'])->name('licitacoes.show');
    Route::post('/licitacoes/{id}/interesse', [LicitacaoController::class, 'marcarInteresse'])->name('licitacoes.interesse');
    Route::post('/licitacoes/{id}/analisada', [LicitacaoController::class, 'marcarAnalisada'])->name('licitacoes.analisada');

    // Rotas para propostas
    Route::resource('propostas', PropostaController::class);
    Route::post('/propostas/{id}/enviar', [PropostaController::class, 'enviar'])->name('propostas.enviar');
    Route::post('/propostas/{id}/status', [PropostaController::class, 'atualizarStatus'])->name('propostas.status');

    // Rotas para clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('/clientes/lista', [ClienteController::class, 'lista'])->name('clientes.lista');
});


// Rotas para relatórios
Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
Route::get('/relatorios/licitacoesPorPeriodo', [RelatorioController::class, 'licitacoesPorPeriodo']);
Route::get('/relatorios/propostasPorStatus', [RelatorioController::class, 'propostasPorStatus']);
Route::get('/relatorios/desempenhoPorCliente', [RelatorioController::class, 'desempenhoPorCliente']);
Route::get('/relatorios/licitacoesPorUF', [RelatorioController::class, 'licitacoesPorUF']);
Route::get('/relatorios/valorMedioPorModalidade', [RelatorioController::class, 'valorMedioPorModalidade']);
Route::get('/relatorios/{tipo}/excel', [RelatorioController::class, 'exportarExcel']);

// Rotas de teste da API
Route::get('/api-test', [ApiTestController::class, 'index'])->name('api.test');
Route::post('/api-test/connection', [ApiTestController::class, 'testApiConnection'])->name('api.test.connection');

Route::get('/api-test/multiple-endpoints', [ApiTestController::class, 'testMultipleEndpoints'])->name('api.test.multiple');

Route::get('/api-test/specific-url', [ApiTestController::class, 'testApiWithSpecificUrl'])->name('api.test.specific');
