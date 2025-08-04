<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ExcelCompareController;

Route::get('/', [ExcelCompareController::class, 'index']);
Route::post('/compare', [ExcelCompareController::class, 'compare'])->name('compare');
Route::get('/download-page', [ExcelCompareController::class, 'downloadPage'])->name('download.page');
Route::get('/download', [ExcelCompareController::class, 'download'])->name('download');


