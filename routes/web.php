<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\columnChartController;
use App\Http\Controllers\tableCardController;

Route::get('/sample', [columnChartController::class, 'showColumnChart'])->name('column.chart');


Route::get('/', function () {
    return view('auth.welcome');
})->name("web");

Route::post('/logout',[AuthController::class, 'logout'])->name('logout');
Route::get('/app', [AuthController::class, 'showApp'])->name('show.app'); 

Route::middleware(['guest'])->controller(AuthController::class)->group(function () {
        
Route::get('/create', 'showcreate')->name('show.create');
Route::get('/login', 'showlogin')->name('show.login');
Route::post('/create', 'create')->name('create');
Route::post('/login', 'login')->name('login');
});



Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Route::middleware(['auth'])->group(function () {
//     Route::redirect('settings', 'settings/profile');




require __DIR__.'/auth.php';
 


