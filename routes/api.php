<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\LoginController;
// SEMPRE RODAR O COMANDO:
//php artisan cache:clear
//php artisan route:cache
Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

Route::post('/login', [LoginController::class, 'login']);
