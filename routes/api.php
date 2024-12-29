<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

//route untuk index
Route::get('/transactions/{nim}', [TransactionController::class, 'indexByNim']);
//Route untuk Post
Route::post('/transactions', [TransactionController::class, 'store']);
//Route untuk Update
Route::put('/transactions/{id}', [TransactionController::class, 'update']);
//Route untuk Get Saldo
Route::get('/transactions/saldo/{nim}', [TransactionController::class, 'getSaldoByNim']);
//Route untuk Update Saldo
Route::put('/transactions/saldo/{nim}', [TransactionController::class, 'updateSaldoByNim']);
//Route untuk Delete
Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);