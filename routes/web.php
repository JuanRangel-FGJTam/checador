<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AdminController,
    CatalogController,
    EmployeeController,
    EmployeeScheduleController,
    HollidaysController,
    InactiveController,
    IncidentController,
    JustificationController,
    NewEmployeeController,
    ProfileController,
    UserController,
    ReportController,
    StaffController
};

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
        Route::post('{employee_number}/incidents/store', [IncidentController::class, 'makeIncidentsOfEmployee'])->name('incidents.store');

        Route::prefix('{employee_number}/justifications')->name('justifications.')->group(function(){
            Route::get('', [JustificationController::class, 'showJustificationOfEmployee'])->name('index');
            Route::get('justify-day', [JustificationController::class, 'showJustifyDay'])->name('justify-day');
            Route::post('', [JustificationController::class, 'storeJustification'])->name('store');
        });

        Route::get('{employee_number}/raw-events', [EmployeeController::class, 'eventsJson'])->name('raw-events');

        Route::prefix('{employee_number}/schedule')->name('schedule.')->group(function(){
            Route::get('', [EmployeeScheduleController::class, 'edit'])->name('edit');
            Route::patch('', [EmployeeScheduleController::class, 'update'])->name('update');
        });

        Route::get('{employee_number}/kardex', [EmployeeController::class, 'kardexEmployee'])->name('kardex');

    });

    Route::prefix('incidents')->name("incidents.")->group(function(){
        Route::get('/employee/{employee_number}', [IncidentController::class, 'getIncidentsByEmployee'])->name('employee.index');
        Route::get('/employee/{employee_number}/raw-incidents', [IncidentController::class, 'employeeIncidentsJson'])->name('employee.raw');
        Route::patch('{incident_id}/state', [IncidentController::class, 'updateIncidentState'])->name('state.update');
    });

    Route::prefix("justifications")->name('justifications.')->group(function(){
        Route::get('{justification_id}/file', [JustificationController::class, 'getJustificationFile'])->name('file');
        Route::get('{justification_id}/edit', [JustificationController::class, 'editJustify'])->name('edit');
        Route::post('{justification_id}/update', [JustificationController::class, 'updateJustify'])->name('update');
    });

    Route::prefix("reports")->name('reports.')->group(function(){
        Route::get('', [ReportController::class, 'index'])->name('index');
        Route::get('daily', [ReportController::class, 'createDailyReport'])->name('daily.create');
        Route::get('daily/{report_name}/download', [ReportController::class, 'downloadDailyReporte'])->name('daily.download');

        Route::get('monthly', [ReportController::class, 'createMonthlyReport'])->name('monthly.create');
        Route::get('monthly/{report_name}/download', [ReportController::class, 'downloadMonthlyReporte'])->name('monthly.download');
    });

    Route::prefix("new-employees")->name('newEmployees.')->group(function(){
        Route::get('', [NewEmployeeController::class, 'index'])->name('index');
        Route::get('{employee_number}/edit', [NewEmployeeController::class, 'edit'])->name('edit');
        Route::patch('{employee_number}', [NewEmployeeController::class, 'update'])->name('update');
    });


    Route::prefix('staff')->name('staff.')->group(function(){
        Route::get('', [StaffController::class, 'index'])->name('index');
        Route::get('{employee_number}', [StaffController::class, 'show'])->name('show');
        Route::get('{employee_number}/kardex', [EmployeeController::class, 'kardexEmployee'])->name('kardex');
    });

    Route::prefix('incidents')->name('incidents.')->group(function(){
        Route::get('', [IncidentController::class, 'index'])->name('index');
        Route::get('report', [IncidentController::class, 'makeReport'])->name('report.make');
    });

    Route::prefix('hollidays')->name('hollidays.')->group(function(){
        Route::get('create', [HollidaysController::class, 'create'])->name('create');
        Route::post('', [HollidaysController::class, 'store'])->name('store');
        Route::get('create/done', [HollidaysController::class, 'createDone'])->name('create.done');
    });

    Route::prefix('inactive')->name('inactive.')->group(function(){
        Route::get('', [InactiveController::class, 'index'])->name('index');
    });

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
