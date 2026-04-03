<?php

use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\VendasController;
use Illuminate\Support\Facades\Route;

Route::get('/usuarios', [UsuariosController::class, 'index']);
Route::post('/usuarios', [UsuariosController::class, 'store']);
Route::get('/usuarios/{usuario}', [UsuariosController::class, 'show']);
Route::put('/usuarios/{usuario}', [UsuariosController::class, 'update']);
Route::delete('/usuarios/{usuario}', [UsuariosController::class, 'destroy']);

Route::get('/produtos', [ProdutosController::class, 'index']);
Route::post('/produtos', [ProdutosController::class, 'store']);
Route::get('/produtos/{produto}', [ProdutosController::class, 'show']);
Route::put('/produtos/{produto}', [ProdutosController::class, 'update']);
Route::delete('/produtos/{produto}', [ProdutosController::class, 'destroy']);

Route::get('/vendas', [VendasController::class, 'index']);
Route::post('/vendas', [VendasController::class, 'store']);
Route::get('/vendas/{venda}', [VendasController::class, 'show']);
// Route::put('/vendas/{venda}', [VendasController::class, 'update']);
Route::delete('/vendas/{venda}', [VendasController::class, 'destroy']);