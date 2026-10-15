<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProfileController;
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

// EDIT / DELETE (solo l'autore: ArticlePolicy)
Route::get('/article/{article}/edit', [ArticleController::class, 'edit'])->middleware(['auth', 'can:update,article'])->name('article.edit');
Route::delete('/article/{article}', [ArticleController::class, 'destroy'])->middleware(['auth', 'can:delete,article'])->name('article.destroy');

// CATEGORY
Route::get('/category/{category}', [ArticleController::class, 'byCategory'])->name('byCategory');

// REVISOR
Route::get('/revisor/index', [RevisorController::class, 'index'])->middleware('isRevisor')->name('revisor.index');

// ACCEPT
Route::patch('/accept/{article}', [RevisorController::class, 'accept'])->middleware('isRevisor')->name('accept');

// REJECT
Route::patch('/reject/{article}', [RevisorController::class, 'reject'])->middleware('isRevisor')->name('reject');

// UNDO (ultima decisione del revisore)
Route::patch('/revisor/undo', [RevisorController::class, 'undo'])->middleware('isRevisor')->name('revisor.undo');

// BECOME REVISOR
Route::post('/revisor/request', [RevisorController::class, 'becomeRevisor'])->middleware(['auth', 'throttle:3,60'])->name('become.revisor');

// MAKE REVISOR
Route::get('/make/revisor/{user}', [RevisorController::class, 'makeRevisor'])->middleware('signed')->name('make.revisor');

// SEARCH
Route::get('/search/article', [PublicController::class, 'searchArticles'])->name('article.search');

// LANGUAGE
Route::post('/lingua/{lang}', [PublicController::class, 'setLanguage'])->whereIn('lang', config('app.available_locales'))->name('setLocale');

// PROFILO
Route::get('/profilo', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profilo/avatar', [ProfileController::class, 'updateAvatar'])->middleware('throttle:10,1')->name('profile.avatar.update');
Route::delete('/profilo/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
