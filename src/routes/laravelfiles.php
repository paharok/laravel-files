<?php

use Illuminate\Support\Facades\Route;

Route::get('/laravel-files', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'index'])->name('laravel-files.index');
Route::post('/laravel-files/new-folder', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'newFolder'])->name('laravel-files.newFolder');
Route::post('/laravel-files/new-file', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'newFile'])->name('laravel-files.newFile');
Route::post('/laravel-files/remove-file', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'removeFile'])->name('laravel-files.removeFile');
Route::post('/laravel-files/remove-dir', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'removeDir'])->name('laravel-files.removeDir');
Route::post('/laravel-files/search', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'search'])->name('laravel-files.search');
