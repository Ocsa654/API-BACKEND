<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UsuarioController;
use App\Http\Controllers\API\MasterTokenController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\SongController; 

// Ruta para generar el token maestro
Route::post('/generate-master-token', [MasterTokenController::class, 'generateMasterToken']);

// Ruta para el login
Route::post('/login', [LoginController::class, 'login']);  // Ruta para login
Route::middleware('auth:sanctum')->get('/user', [LoginController::class, 'user']);  // Ruta para obtener el usuario autenticado

// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('musica', SongController::class);
});