<?php

use App\Http\Controllers\CitaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('citas.index');
});

Route::get('/citas', [CitaController::class, 'index'])->name('citas.index');
Route::get('/citas/crear', [CitaController::class, 'create'])->name('citas.create');
Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
Route::patch('/citas/{cita}/estado', [CitaController::class, 'updateStatus'])->name('citas.estado');
