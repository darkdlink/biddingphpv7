<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BiddingController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\DocumentController;

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Rota de usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rotas de Licitações
    Route::get('/biddings', [BiddingController::class, 'index']);
    Route::post('/biddings', [BiddingController::class, 'store']);
    Route::get('/biddings/{id}', [BiddingController::class, 'show']);
    Route::put('/biddings/{id}', [BiddingController::class, 'update']);
    Route::delete('/biddings/{id}', [BiddingController::class, 'destroy']);
    Route::post('/biddings/fetch-api', [BiddingController::class, 'fetchFromApi']);

    // Rotas de Entidades
    Route::get('/entities', [EntityController::class, 'index']);
    Route::post('/entities', [EntityController::class, 'store']);
    Route::get('/entities/{id}', [EntityController::class, 'show']);
    Route::put('/entities/{id}', [EntityController::class, 'update']);
    Route::delete('/entities/{id}', [EntityController::class, 'destroy']);

    // Rotas de Propostas
    Route::get('/proposals', [ProposalController::class, 'index']);
    Route::post('/proposals', [ProposalController::class, 'store']);
    Route::get('/proposals/{id}', [ProposalController::class, 'show']);
    Route::put('/proposals/{id}', [ProposalController::class, 'update']);
    Route::delete('/proposals/{id}', [ProposalController::class, 'destroy']);
    Route::post('/proposals/calculate-profit', [ProposalController::class, 'calculateProfit']);

    // Rotas de Documentos
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::put('/documents/{id}', [DocumentController::class, 'update']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
    Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
});
