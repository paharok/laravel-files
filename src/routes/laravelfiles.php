<?php

use Illuminate\Support\Facades\Route;

Route::middleware(array_filter([
    'web',
    'plf.nocache',
    config('laravelfiles.auth_middleware') ? 'auth' : null,
]))->group(function () {
    Route::get('/laravel-files', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'index'])->name('laravel-files.index');
    Route::post('/laravel-files/new-folder', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'newFolder'])->name('laravel-files.newFolder');
    Route::post('/laravel-files/new-file', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'newFile'])->name('laravel-files.newFile');
    Route::post('/laravel-files/remove-file', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'removeFile'])->name('laravel-files.removeFile');
    Route::post('/laravel-files/remove-dir', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'removeDir'])->name('laravel-files.removeDir');
    Route::post('/laravel-files/search', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'search'])->name('laravel-files.search');
    Route::post('/laravel-files/rename', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'rename'])->name('laravel-files.rename');

    Route::post('/laravel-files/group-remove', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'groupRemove'])->name('laravel-files.groupRemove');
    Route::post('/laravel-files/group-copy', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'groupCopy'])->name('laravel-files.groupCopy');
    Route::post('/laravel-files/group-move', [\Paharok\Laravelfiles\Http\Controllers\LaravelFilesController::class,'groupMove'])->name('laravel-files.groupMove');


});
