<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'homepage'])->name('homepage');

// CREATE
Route::get('/create/article', [ArticleController::class, 'create'])->name('create.article');

// INDEX
Route::get('/article/index', [ArticleController::class, 'index'])->name('article.index');

// SHOW
Route::get('/show/article/{article}', [ArticleController::class, 'show'])->name('article.show');

// CATEGORY
Route::get('/category/{category}', [ArticleController::class, 'byCategory'])->name('byCategory');
