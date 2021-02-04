<?php

use Ghost\DcatConfig\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('config', Controllers\DcatConfigController::class.'@index');
Route::get('config/add', Controllers\DcatConfigController::class.'@add');
Route::post('config/addo', Controllers\DcatConfigController::class.'@addo');
Route::post('config.do', Controllers\DcatConfigController::class.'@update');
Route::any('files', Controllers\FileController::class.'@handle');
Route::delete('config/{id}', Controllers\DcatConfigController::class.'@destroy');
Route::get('config/{id}/edit', Controllers\DcatConfigController::class.'@edit');

Route::put('config/{id}', Controllers\DcatConfigController::class.'@putEdit');
