<?php

use Illuminate\Support\Facades\Route;

Route::get('/laravel-files', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'index']);
