<?php

use App\Http\Controllers\CitaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/citas', [CitaController::class, 'index'])->name('api.citas.index');
    Route::post('/citas', [CitaController::class, 'store'])->name('api.citas.store');
    Route::patch('/citas/{cita}/estado', [CitaController::class, 'updateStatus'])->name('api.citas.estado');
});
