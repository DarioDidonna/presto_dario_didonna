<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RevisorController;
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


// REVISOR
Route::get('/revisor/index', [RevisorController::class, 'index'])->middleware('isRevisor')->name('revisor.index');

// ACCEPT
Route::patch('/accept/{article}', [RevisorController::class, 'accept'])->name('accept');

// REJECT
Route::patch('/reject/{article}', [RevisorController::class, 'reject'])->name('reject');


// BECOME REVISOR
Route::get('/revisor/request', [RevisorController::class, 'becomeRevisor'])->middleware('auth')->name('become.revisor');


// MAKE REVISOR
Route::get('/make/revisor/{user}', [RevisorController::class, 'makeRevisor'])->name('make.revisor');

// SEARCH
Route::get('/search/article', [PublicController::class, 'searchArticles'])->name('article.search');