<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\{
    AdminController,
    ProfileController
};

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->name('admin.')->group(function(){
        Route::get('', [ AdminController::class, "index"])->name('index');
    });

});

Route::get('rh', function(){
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('rh');

Route::get('reports', function(){
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('reports');

Route::get('new-employees', function(){
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('newEmployees');

Route::get('show', function(){
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('show');

Route::get('incidents', function(){
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('incidents');

Route::get('incidents', function(){
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('incidents');

Route::get('inactive', function(){
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('inactive');


Route::get('/hollidays', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('hollidays');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
