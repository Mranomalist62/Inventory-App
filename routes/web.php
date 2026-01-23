<?php

use App\Http\Controllers\BarcodeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/receipt/{sale}', [ReceiptController::class, 'print'])->name('receipt.print');
Route::get('/barcode/preview', [BarcodeController::class, 'preview'])->name('barcode.preview');
Route::get('/barcode/print', [BarcodeController::class, 'print'])->name('barcode.print');