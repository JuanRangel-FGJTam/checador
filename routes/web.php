<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\{
    AdminController,
    CatalogController,
    EmployeeController,
    EmployeeScheduleController,
    JustificationController,
    ProfileController,
    UserController
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

        Route::prefix('users')->name('users.')->group(function(){
            Route::get('', [UserController::class, 'index'])->name('index');
            Route::post('', [UserController::class, 'store'])->name('store');
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::get('edit/{userid}', [UserController::class, 'edit'])->name('edit');
            Route::patch('{userid}', [UserController::class, 'update'])->name('update');
            Route::patch('{userid}/password', [UserController::class, 'updatePassword'])->name('update.password');
        });

        Route::prefix('catalogs')->name('catalogs.')->group(function(){
            Route::prefix('general-directions')->name("general-directions.")->group(function(){
                Route::get('', [CatalogController::class, 'generalDirectionsIndex'])->name('index');
                Route::get('/new', [CatalogController::class, 'generalDirectionsCreate'])->name('create');
                Route::post('', [CatalogController::class, 'generalDirectionsStore'])->name('store');
                Route::get('{catalogId}', [CatalogController::class, 'generalDirectionsEdit'])->name('edit');
                Route::patch('{catalogId}', [CatalogController::class, 'generalDirectionsUpdate'])->name('update');
            });

            Route::prefix('directions')->name("directions.")->group(function(){
                Route::get('', [CatalogController::class, 'directionsIndex'])->name('index');
                Route::get('/new', [CatalogController::class, 'directionsCreate'])->name('create');
                Route::post('', [CatalogController::class, 'directionsStore'])->name('store');
                Route::get('{directionId}', [CatalogController::class, 'directionsEdit'])->name('edit');
                Route::patch('{directionId}', [CatalogController::class, 'directionsUpdate'])->name('update');
            });

            Route::prefix('sub-directions')->name("sub-directions.")->group(function(){
                Route::get('', [CatalogController::class, 'subDirectionsIndex'])->name('index');
                Route::get('/new', [CatalogController::class, 'subDirectionCreate'])->name('create');
                Route::post('', [CatalogController::class, 'subDirectionStore'])->name('store');
                Route::get('{directionId}', [CatalogController::class, 'subDirectionEdit'])->name('edit');
                Route::patch('{directionId}', [CatalogController::class, 'subDirectionUpdate'])->name('update');
            });

            Route::prefix('departments')->name("departments.")->group(function(){
                Route::get('', [CatalogController::class, 'departmentsIndex'])->name('index');
                Route::get('/new', [CatalogController::class, 'departmentCreate'])->name('create');
                Route::post('', [CatalogController::class, 'departmentStore'])->name('store');
                Route::get('{directionId}', [CatalogController::class, 'departmentEdit'])->name('edit');
                Route::patch('{directionId}', [CatalogController::class, 'departmentUpdate'])->name('update');
            });

        });

    });

    Route::prefix('employees')->name('employees.')->group(function(){
        Route::get('', [EmployeeController::class, 'index'])->name('index');
        Route::get('{employee_number}', [EmployeeController::class, 'show'])->name('show');
        Route::get('{employee_number}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::patch('{employee_number}', [EmployeeController::class, 'update'])->name('update');

        Route::get('{employee_number}/incidents/create', [EmployeeController::class, 'incidentCreate'])->name('incidents.create');

        Route::prefix('{employee_number}/justifications')->name('justifications.')->group(function(){
            Route::get('', [JustificationController::class, 'showJustificationOfEmployee'])->name('index');
            Route::get('justify-day', [JustificationController::class, 'showJustifyDay'])->name('justify-day');
        });

        Route::get('{employee_number}/raw-events', [EmployeeController::class, 'eventsJson'])->name('raw-events');

        Route::prefix('{employee_number}/schedule')->name('schedule.')->group(function(){
            Route::get('', [EmployeeScheduleController::class, 'edit'])->name('edit');
            Route::patch('', [EmployeeScheduleController::class, 'update'])->name('update');
        });
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
