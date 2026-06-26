<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'homepage'])->name('homepage');

// CREATE
Route::get('/create/article', [ArticleController::class, 'create'])->name('create.article');
