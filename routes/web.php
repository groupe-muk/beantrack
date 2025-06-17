<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name("web");

Route::post('/logout',[AuthController::class, 'logout'])->name('logout');
Route::middleware(['guest'])->controller(AuthController::class)->group(function () {
        
Route::get('/create', 'showcreate')->name('show.create');
Route::get('/login', 'showlogin')->name('show.login');
Route::post('/create', 'create')->name('create');
Route::post('/login', 'login')->name('login');
});




route::middleware(['auth'])->group(function () {
    Route::get('/accordion', function () {
    return view('accordion');
})->name('accordion');

Route::get('/carousel', function () {
    return view('carousel');
})->name('carousel');

Route::get('/modal', function () {
    return view('modal');
})->name('modal');

Route::get('/collapse', function () {
    return view('collapse');
})->name('collapse');

Route::get('/dial', function () {
    return view('dial');
})->name('dial');

Route::get('/dismiss', function () {
    return view('dismiss');
})->name('dismiss');

Route::get('/drawer', function () {
    return view('drawer');
})->name('drawer');

Route::get('/dropdown', function () {
    return view('dropdown');
})->name('dropdown');

Route::get('/popover', function () {
    return view('popover');
})->name('popover');

Route::get('/tooltip', function () {
    return view('tooltip');
})->name('tooltip');

Route::get('/input-counter', function () {
    return view('input-counter');
})->name('input-counter');

Route::get('/tabs', function () {
    return view('tabs');
})->name('tabs');

Route::get('/datepicker', function () {
    return view('datepicker');
})->name('datepicker');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

   

// Route::middleware(['auth'])->group(function () {
//     Route::redirect('settings', 'settings/profile');

//     Route::get('settings/profile', Profile::class)->name('settings.profile');
//     Route::get('settings/password', Password::class)->name('settings.password');
//     Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
// });

require __DIR__.'/auth.php';
 });


