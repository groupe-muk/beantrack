<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChartTestController; // IMPORTANT: Add this use statement

Route::get('/sample', [ChartTestController::class, 'showColumnChart'])->name('column.chart');

Route::get('/', function () {
    return view('auth.welcome');
})->name("web");

Route::get('/create',[AuthController::class, 'showcreate'])->name('show.create');
Route::get('/login',[AuthController::class, 'showlogin'])->name('show.login');
Route::post('/create',[AuthController::class, 'create'])->name('create');
Route::post('/login',[AuthController::class, 'login'])->name('login');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');
Route::get('/app', [AuthController::class, 'showApp'])->name('show.app'); //Add middleware to this path



Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Route::middleware(['auth'])->group(function () {
//     Route::redirect('settings', 'settings/profile');




require __DIR__.'/auth.php';
