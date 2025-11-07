<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);              // GET /api/users
    Route::post('/', [UserController::class, 'store']);             // POST /api/users
    Route::put('/{user}', [UserController::class, 'update']);       // PUT /api/users/{user}
    Route::delete('/{user}', [UserController::class, 'destroy']);   // DELETE /api/users/{user}
    Route::delete('/{user}/addresses/{address}', [UserController::class, 'detachAddress']); // DELETE /api/users/{user}/addresses/{address}
    
});
