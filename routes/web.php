<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Inicio de sesión (RF-01)
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/inicio', [LoginController::class, 'inicio'])->name('inicio');

// Gestión de usuarios (RF-11), solo administrador
Route::resource('usuarios', UsuarioController::class)->except(['show']);

// Módulo del mesero: mesas (RF-02)
Route::get('/mesas', [MesaController::class, 'index'])->name('mesas.index');
Route::post('/mesas/{mesa}/reservar', [MesaController::class, 'reservar'])->name('mesas.reservar');
Route::post('/mesas/{mesa}/liberar', [MesaController::class, 'liberar'])->name('mesas.liberar');

// Módulo del mesero: comanda de una mesa (RF-03, RF-07)
Route::get('/mesas/{mesa}/comanda', [PedidoController::class, 'create'])->name('pedidos.create');
Route::post('/mesas/{mesa}/comanda', [PedidoController::class, 'store'])->name('pedidos.store');

// Pedidos de una mesa y cancelación (RF-12), mesero y administrador
Route::get('/mesas/{mesa}/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
Route::post('/pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');