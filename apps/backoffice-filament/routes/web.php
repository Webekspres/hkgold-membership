<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/login', '/app/login');

Route::get('/', function () {
    return view('welcome');
});
