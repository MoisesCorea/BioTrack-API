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
Route::get('/reports/user', [AttendanceController::class, 'generateReportUser'])->middleware('verify.rol:Admin,Admin-1,Admin-2');
Route::get('/reports/users', [AttendanceController::class, 'generateReportUsers'])->middleware('verify.rol:Admin,Admin-1,Admin-2');
Route::get('/attendances', [AttendanceController::class, 'getDailyAttendace'])->middleware('verify.rol:Admin,Admin-1,Admin-2');



//CRUD admins
Route::get('admins/{id}', [AdminController::class, 'show'])->middleware('auth:sanctum');
Route::prefix('admins')->middleware('verify.rol:Admin')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::post('/', [AdminController::class, 'store']);
    Route::patch('/{id}', [AdminController::class, 'update']);
    Route::delete('/{id}', [AdminController::class, 'destroy']);
});

//CRUD roles
Route::get('roles/', [RolesController::class, 'index'])->middleware('verify.rol:Admin'); 
Route::get('roles/{id}', [RolesController::class, 'show'])->middleware('verify.rol:Admin'); 

Route::prefix('roles')->middleware('verify.rol:Admin')->group(function () {
    Route::post('/', [RolesController::class, 'store']); 
    Route::patch('/{id}', [RolesController::class, 'update']); 
    Route::delete('/{id}', [RolesController::class, 'destroy']); 
});

//CRUD departmentos

Route::get('departments', [DepartmentsController::class, 'index'])->middleware('verify.rol:Admin,Admin-1,Admin-2'); 
Route::get('departments/{id}', [DepartmentsController::class, 'show'])->middleware('verify.rol:Admin,Admin-1,Admin-2');

Route::prefix('departments')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::post('/', [DepartmentsController::class, 'store']); 
    Route::patch('/{id}', [DepartmentsController::class, 'update']); 
    Route::delete('/{id}', [DepartmentsController::class, 'destroy']); 
});

//CRUD horarios

Route::prefix('shifts')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::get('/', [ShiftsController::class, 'index']); 
    Route::post('/', [ShiftsController::class, 'store']); 
    Route::get('/{id}', [ShiftsController::class, 'show']); 
    Route::patch('/{id}', [ShiftsController::class, 'update']); 
    Route::delete('/{id}', [ShiftsController::class, 'destroy']); 
});

//CRUD eventos
Route::get('events', [EventsController::class, 'index'])->middleware('verify.rol:Admin,Admin-1,Admin-2'); 

Route::prefix('events')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::post('/', [EventsController::class, 'store']); 
    Route::get('/{id}', [EventsController::class, 'show']); 
    Route::patch('/{id}', [EventsController::class, 'update']); 
    Route::patch('/{id}/status', [EventsController::class, 'updateStatus']); 
    Route::patch('/{id}/daily-attendance', [EventsController::class, 'updateDailyAttendance']);
    Route::delete('/{id}', [EventsController::class, 'destroy']);
});

//CRUD users
Route::get('users', [UsersController::class, 'index'])->middleware('verify.rol:Admin,Admin-1,Admin-2'); 
Route::get('users/{id}', [UsersController::class, 'show'])->middleware('verify.rol:Admin,Admin-1,Admin-2'); 

Route::prefix('users')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::post('/', [UsersController::class, 'store']); 
    Route::patch('/{id}', [UsersController::class, 'update']); 
    Route::delete('/{id}', [UsersController::class, 'destroy']); 
});

//Justificaciones
Route::prefix('justifications')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::get('/', [JustificationController::class, 'index']);
    Route::post('/', [JustificationController::class, 'store']);
    Route::get('/{id}', [JustificationController::class, 'show']);
    Route::patch('/{id}/status', [JustificationController::class, 'updateStatus']);
    Route::delete('/{id}', [JustificationController::class, 'destroy']);
});







