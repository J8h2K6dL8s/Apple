<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthentificationController;


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

Route::get('/confirmation', [AuthentificationController::class, 'showConfirmation'])->name('confirmation');

Route::get('/contact', [AuthentificationController::class, 'contact'])->name('contact');
