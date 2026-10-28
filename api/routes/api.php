<?php

use App\Http\Controllers\Api\V1\AreaController;
use App\Http\Controllers\Api\V1\AsistenciaController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EmpleadoController;
use App\Http\Controllers\Api\V1\ReporteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function (): void {
    // Máximo 5 intentos de login por minuto
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('yo', [AuthController::class, 'yo'])->name('yo');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('areas', [AreaController::class, 'index'])->name('areas.index');

        // Cualquier usuario autenticado consulta empleados; solo admin crea, edita o elimina
        Route::apiResource('empleados', EmpleadoController::class)->only(['index', 'show']);
        Route::apiResource('empleados', EmpleadoController::class)
            ->only(['store', 'update', 'destroy'])
            ->middleware('rol:admin');

        Route::get('asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');
        Route::post('asistencias/entrada', [AsistenciaController::class, 'registrarEntrada'])->name('asistencias.entrada');
        Route::patch('asistencias/{asistencia}/salida', [AsistenciaController::class, 'registrarSalida'])->name('asistencias.salida');

        Route::get('reportes/tardanzas', [ReporteController::class, 'tardanzas'])->name('reportes.tardanzas');
        Route::get('reportes/resumen-del-dia', [ReporteController::class, 'resumenDelDia'])->name('reportes.resumen');
    });
});
