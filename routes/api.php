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
use App\Http\Controllers\PermissionsController;

// Rutas Públicas
Route::post('/login', [AuthController::class, 'login']);

// Registro de asistencia (QR Scanner) - Se mantiene pública si el scanner no usa login
// Registro de asistencia (QR Scanner) - Se mantiene pública si el scanner no usa login
Route::post('/users/{user}/attendance', [AttendanceController::class, 'attachAttendance']);

// Rutas Protegidas por Autenticación
Route::middleware('auth:sanctum')->group(function () {
    
    // Perfil y Logout
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/logout', [AuthController::class, 'logout']);

    // Reportes de asistencia
    Route::middleware('can:view_reports')->group(function () {
        Route::get('/reports/user', [AttendanceController::class, 'generateReportUser']);
        Route::get('/reports/users', [AttendanceController::class, 'generateReportUsers']);
        Route::get('/attendances', [AttendanceController::class, 'getDailyAttendace']);
    });

    // CRUD admins
    Route::get('admins/{admin}', [AdminController::class, 'show']);
    Route::prefix('admins')->middleware('can:manage_admins')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/', [AdminController::class, 'store']);
        Route::patch('/{admin}', [AdminController::class, 'update']);
        Route::delete('/{admin}', [AdminController::class, 'destroy']);
    });

    // CRUD roles
    Route::get('roles/', [RolesController::class, 'index'])->middleware('can:view_roles'); 
    Route::get('roles/{role}', [RolesController::class, 'show'])->middleware('can:view_roles'); 
    Route::prefix('roles')->middleware('can:manage_roles')->group(function () {
        Route::post('/', [RolesController::class, 'store']); 
        Route::patch('/{role}', [RolesController::class, 'update']); 
        Route::delete('/{role}', [RolesController::class, 'destroy']); 
    });

    // Permisos
    Route::get('permissions', [PermissionsController::class, 'index'])->middleware('can:manage_roles');

    // CRUD departmentos
    Route::get('departments', [DepartmentsController::class, 'index'])->middleware('can:view_departments'); 
    Route::get('departments/{department}', [DepartmentsController::class, 'show'])->middleware('can:view_departments');
    Route::prefix('departments')->middleware('can:manage_departments')->group(function () {
        Route::post('/', [DepartmentsController::class, 'store']); 
        Route::patch('/{department}', [DepartmentsController::class, 'update']); 
        Route::delete('/{department}', [DepartmentsController::class, 'destroy']); 
    });

    // CRUD horarios
    Route::prefix('shifts')->group(function () {
        Route::get('/', [ShiftsController::class, 'index'])->middleware('can:view_shifts'); 
        Route::post('/', [ShiftsController::class, 'store'])->middleware('can:manage_shifts'); 
        Route::get('/{shift}', [ShiftsController::class, 'show'])->middleware('can:view_shifts'); 
        Route::patch('/{shift}', [ShiftsController::class, 'update'])->middleware('can:manage_shifts'); 
        Route::delete('/{shift}', [ShiftsController::class, 'destroy'])->middleware('can:manage_shifts'); 
    });

    // CRUD eventos
    Route::get('events', [EventsController::class, 'index'])->middleware('can:view_events'); 
    Route::prefix('events')->group(function () {
        Route::get('/{event}', [EventsController::class, 'show'])->middleware('can:view_events'); 
        Route::middleware('can:manage_events')->group(function() {
            Route::post('/', [EventsController::class, 'store']); 
            Route::patch('/{event}', [EventsController::class, 'update']); 
            Route::patch('/{event}/status', [EventsController::class, 'updateStatus']); 
            Route::patch('/{event}/daily-attendance', [EventsController::class, 'updateDailyAttendance']);
            Route::delete('/{event}', [EventsController::class, 'destroy']);
        });
    });

    // CRUD users
    Route::get('users', [UsersController::class, 'index'])->middleware('can:view_users'); 
    Route::get('users/{user}', [UsersController::class, 'show'])->middleware('can:view_users'); 
    Route::prefix('users')->middleware('can:manage_users')->group(function () {
        Route::post('/', [UsersController::class, 'store']); 
        Route::patch('/{user}', [UsersController::class, 'update']); 
        Route::delete('/{user}', [UsersController::class, 'destroy']); 
    });

    // Justificaciones
    Route::prefix('justifications')->group(function() {
        Route::get('/', [JustificationController::class, 'index'])->middleware('can:view_justifications');
        Route::post('/', [JustificationController::class, 'store'])->middleware('can:view_justifications');
        Route::get('/{justification}', [JustificationController::class, 'show'])->middleware('can:view_justifications');
        Route::middleware('can:manage_justifications')->group(function() {
            Route::patch('/{justification}/status', [JustificationController::class, 'updateStatus']);
            Route::delete('/{justification}', [JustificationController::class, 'destroy']);
        });
    });
});




