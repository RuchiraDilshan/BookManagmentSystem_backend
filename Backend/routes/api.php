<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BorrowController;


// Category Routes
Route::get('/categories', [CategoryController::class, 'index']);

// Book Routes
Route::apiResource('books', BookController::class);

// User Routes
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);


// Borrow Routes

Route::get('/borrow-records', [BorrowController::class, 'index']);
Route::get('/borrow-records/current', [BorrowController::class, 'currentBorrows']);
Route::get('/borrow-records/history', [BorrowController::class, 'borrowHistory']);
Route::get('/borrow-records/user/{userId}/current', [BorrowController::class, 'userCurrentBorrows']);
Route::post('/borrow-records/borrow', [BorrowController::class, 'borrow']);
Route::post('/borrow-records/return', [BorrowController::class, 'returnBook']);
Route::post('/borrow-records/{id}/return', [BorrowController::class, 'returnBook']);
