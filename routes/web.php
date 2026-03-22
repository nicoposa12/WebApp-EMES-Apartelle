<?php

use App\Http\Controllers\Api\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoices/{id}/download', [InvoiceController::class, 'download'])->name('invoice.download');
Route::get('/invoices/{id}/view', [InvoiceController::class, 'view'])->name('invoice.view');

// SPA Catch-all and Named Routes
Route::get('/reset-password/{token}', function () {
    return view('welcome');
})->name('password.reset');

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
