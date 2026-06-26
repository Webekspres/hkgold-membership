<?php

use App\Http\Controllers\R2SignedUrlController;
use Illuminate\Support\Facades\Route;

Route::redirect('/login', '/app/login');

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->post('/internal/r2-signed-url', R2SignedUrlController::class);
