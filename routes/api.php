<?php

use App\Http\Controllers\Api\v1\BookController;
use App\Http\Controllers\Api\v1\BorrowController;
use App\Http\Controllers\Api\v1\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/customers', CustomerController::class);

Route::apiResource('/borrows', BorrowController::class)->except(['store', 'update']);
Route::post('/borrows', [BorrowController::class, 'borrow']);
Route::post('/borrows/{borrow}', [BorrowController::class, 'unborrow']);

Route::apiResource('/books', BookController::class);
Route::post('/books/{book}/update-stock', [BookController::class, 'updateStock']);
