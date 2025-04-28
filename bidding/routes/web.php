<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BiddingController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Biddings
Route::get('/biddings', [BiddingController::class, 'index'])->name('biddings.index');
Route::get('/biddings/create', [BiddingController::class, 'create'])->name('biddings.create');
Route::post('/biddings', [BiddingController::class, 'store'])->name('biddings.store');
Route::get('/biddings/{bidding}', [BiddingController::class, 'show'])->name('biddings.show');
Route::get('/biddings/{bidding}/edit', [BiddingController::class, 'edit'])->name('biddings.edit');
Route::put('/biddings/{bidding}', [BiddingController::class, 'update'])->name('biddings.update');
Route::delete('/biddings/{bidding}', [BiddingController::class, 'destroy'])->name('biddings.destroy');

// Proposals
Route::get('/biddings/{bidding}/proposals/create', [ProposalController::class, 'create'])->name('proposals.create');
Route::post('/biddings/{bidding}/proposals', [ProposalController::class, 'store'])->name('proposals.store');
Route::get('/proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
Route::get('/proposals/{proposal}/edit', [ProposalController::class, 'edit'])->name('proposals.edit');
Route::put('/proposals/{proposal}', [ProposalController::class, 'update'])->name('proposals.update');
Route::delete('/proposals/{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');

// Companies
Route::resource('companies', CompanyController::class);

// Documents
Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
