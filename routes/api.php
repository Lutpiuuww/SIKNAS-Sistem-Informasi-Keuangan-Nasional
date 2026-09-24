<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'storeExternal'])->middleware('auth:sanctum');


