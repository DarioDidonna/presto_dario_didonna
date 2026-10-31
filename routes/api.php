<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Middleware\VerifyAgentToken;
use Illuminate\Support\Facades\Route;

// API interne per l'agente AI (ai-agent/): sola lettura, protette da token, con limite di richieste
Route::prefix('agent')
    ->middleware([VerifyAgentToken::class, 'throttle:agent-api'])
    ->name('api.agent.')
    ->group(function () {
        Route::get('categories', [AgentController::class, 'categories'])->name('categories');
        Route::get('articles', [AgentController::class, 'search'])->name('articles.search');
        Route::get('articles/{article}', [AgentController::class, 'show'])->name('articles.show');
    });
