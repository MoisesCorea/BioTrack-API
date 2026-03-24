<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\ShiftsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\JustificationController;






Route::post('/login', [AuthController::class, 'login']);

//Registro de asistencia
Route::post('/users/{id}/attendance', [AttendanceController::class, 'attachAttendance']);

//Auth
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


//Reporte de asistencia
Route::get('/reports/user', [AttendanceController::class, 'generateReportUser'])->middleware('can:view_reports');
Route::get('/reports/users', [AttendanceController::class, 'generateReportUsers'])->middleware('can:view_reports');
Route::get('/attendances', [AttendanceController::class, 'getDailyAttendace'])->middleware('can:view_reports');

//CRUD admins
Route::get('admins/{id}', [AdminController::class, 'show'])->middleware('auth:sanctum');
Route::prefix('admins')->middleware('can:manage_admins')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::post('/', [AdminController::class, 'store']);
    Route::patch('/{id}', [AdminController::class, 'update']);
    Route::delete('/{id}', [AdminController::class, 'destroy']);
});

//CRUD roles
Route::get('roles/', [RolesController::class, 'index'])->middleware('can:view_roles'); 
Route::get('roles/{id}', [RolesController::class, 'show'])->middleware('can:view_roles'); 

Route::prefix('roles')->middleware('can:manage_roles')->group(function () {
    Route::post('/', [RolesController::class, 'store']); 
    Route::patch('/{id}', [RolesController::class, 'update']); 
    Route::delete('/{id}', [RolesController::class, 'destroy']); 
});

//CRUD departmentos
Route::get('departments', [DepartmentsController::class, 'index'])->middleware('can:view_departments'); 
Route::get('departments/{id}', [DepartmentsController::class, 'show'])->middleware('can:view_departments');

Route::prefix('departments')->middleware('can:manage_departments')->group(function () {
    Route::post('/', [DepartmentsController::class, 'store']); 
    Route::patch('/{id}', [DepartmentsController::class, 'update']); 
    Route::delete('/{id}', [DepartmentsController::class, 'destroy']); 
});

//CRUD horarios
Route::prefix('shifts')->group(function () {
    Route::get('/', [ShiftsController::class, 'index'])->middleware('can:view_shifts'); 
    Route::post('/', [ShiftsController::class, 'store'])->middleware('can:manage_shifts'); 
    Route::get('/{id}', [ShiftsController::class, 'show'])->middleware('can:view_shifts'); 
    Route::patch('/{id}', [ShiftsController::class, 'update'])->middleware('can:manage_shifts'); 
    Route::delete('/{id}', [ShiftsController::class, 'destroy'])->middleware('can:manage_shifts'); 
});

//CRUD eventos
Route::get('events', [EventsController::class, 'index'])->middleware('can:view_events'); 

Route::prefix('events')->group(function () {
    Route::get('/{id}', [EventsController::class, 'show'])->middleware('can:view_events'); 
    Route::middleware('can:manage_events')->group(function() {
        Route::post('/', [EventsController::class, 'store']); 
        Route::patch('/{id}', [EventsController::class, 'update']); 
        Route::patch('/{id}/status', [EventsController::class, 'updateStatus']); 
        Route::patch('/{id}/daily-attendance', [EventsController::class, 'updateDailyAttendance']);
        Route::delete('/{id}', [EventsController::class, 'destroy']);
    });
});

//CRUD users
Route::get('users', [UsersController::class, 'index'])->middleware('can:view_users'); 
Route::get('users/{id}', [UsersController::class, 'show'])->middleware('can:view_users'); 

Route::prefix('users')->middleware('can:manage_users')->group(function () {
    Route::post('/', [UsersController::class, 'store']); 
    Route::patch('/{id}', [UsersController::class, 'update']); 
    Route::delete('/{id}', [UsersController::class, 'destroy']); 
});

//Justificaciones
Route::prefix('justifications')->group(function() {
    Route::get('/', [JustificationController::class, 'index'])->middleware('can:view_justifications');
    Route::post('/', [JustificationController::class, 'store'])->middleware('can:view_justifications');
    Route::get('/{id}', [JustificationController::class, 'show'])->middleware('can:view_justifications');
    
    Route::middleware('can:manage_justifications')->group(function() {
        Route::patch('/{id}/status', [JustificationController::class, 'updateStatus']);
        Route::delete('/{id}', [JustificationController::class, 'destroy']);
    });
});







