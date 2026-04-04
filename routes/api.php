<?php

<<<<<<< HEAD
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerfilAcessoController;
=======
>>>>>>> a850cd7c88af1deddd85d5e4c3e34cc6a5112ba3
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\VendasController;
use Illuminate\Support\Facades\Route;

<<<<<<< HEAD
Route::post('/login', [AuthController::class, 'login']);
Route::post('/usuarios', [UsuariosController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::get('/usuarios', [UsuariosController::class, 'index']);
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
    Route::delete('/vendas/{venda}', [VendasController::class, 'destroy']);

    Route::get('/perfil-acesso', [PerfilAcessoController::class, 'index']);
    Route::post('/perfil-acesso', [PerfilAcessoController::class, 'store']);
    Route::get('/perfil-acesso/{perfilAcesso}', [PerfilAcessoController::class, 'show']);
    Route::put('/perfil-acesso/{perfilAcesso}', [PerfilAcessoController::class, 'update']);
    Route::delete('/perfil-acesso/{perfilAcesso}', [PerfilAcessoController::class, 'destroy']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
   

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
});
=======
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
>>>>>>> a850cd7c88af1deddd85d5e4c3e34cc6a5112ba3
