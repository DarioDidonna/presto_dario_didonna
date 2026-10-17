<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\RevisorController;
use App\Http\Controllers\SalesController;
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

// CARRELLO (solo utenti registrati)
Route::middleware('auth')->prefix('carrello')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
    // Prima di {article}: "invia" non è l'id di un annuncio
    Route::post('/invia', [PurchaseRequestController::class, 'store'])->middleware('throttle:5,1')->name('checkout');
    Route::post('/{article}', [CartController::class, 'add'])->middleware('can:addToCart,article')->name('add');
    Route::delete('/{article}', [CartController::class, 'remove'])->name('remove');
});

// ORDINI (richieste d'acquisto inviate)
Route::get('/ordini', [PurchaseRequestController::class, 'index'])->middleware('auth')->name('orders.index');
Route::patch('/ordini/{purchaseRequest}/annulla', [PurchaseRequestController::class, 'cancel'])
    ->middleware(['auth', 'can:cancel,purchaseRequest'])->name('orders.cancel');

// MESSAGGI tra acquirente e venditore di una richiesta d'acquisto
Route::get('/richieste/{purchaseRequest}/messaggi', [ConversationController::class, 'show'])
    ->middleware(['auth', 'can:view,purchaseRequest'])->name('conversations.show');

// VENDITE (richieste d'acquisto ricevute)
Route::middleware('auth')->prefix('vendite')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('index');
    Route::patch('/{purchaseRequest}/accetta', [SalesController::class, 'accept'])->middleware('can:respond,purchaseRequest')->name('accept');
    Route::patch('/{purchaseRequest}/rifiuta', [SalesController::class, 'decline'])->middleware('can:respond,purchaseRequest')->name('decline');
});
