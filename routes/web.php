<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/readme', function () {
    return response()->file(base_path('README.md'));
});
