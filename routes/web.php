<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [LinkController::class, 'index'])->name('dashboard');
    Route::post('/links', [LinkController::class, 'store'])->name('links.store');
    Route::get('/links/{link}', [LinkController::class, 'show'])->name('links.show');
    Route::put('/links/{link}', [LinkController::class, 'update'])->name('links.update');
    Route::patch('/links/{link}/toggle', [LinkController::class, 'toggle'])->name('links.toggle');
    Route::delete('/links/{link}', [LinkController::class, 'destroy'])->name('links.destroy');
    Route::get('/links/{link}/qr.svg', QrController::class)->name('links.qr');
});

// Short links. Registered last so application routes always win; reserved words can never be codes.
Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9_-]{3,32}')
    ->middleware('throttle:240,1')
    ->name('redirect');
