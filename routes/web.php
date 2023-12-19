<?php

use App\Http\Controllers\SoalController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

// Route::redirect('/', '/dashboard-general-dashboard');

// Dashboard
Route::get('/', function () {
    return view('pages.auth.login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return view('pages.dashboard');
    })->name('dashboard');
    Route::resource('user', UserController::class);
    Route::resource('soal', SoalController::class);
    // Route::post('/import', SoalController::class, 'import')->name('import');
});

Route::middleware(['auth'])->post('/import', [SoalController::class, 'import'])->name('import');

// Route::post('import', 'SoalController@import')->middleware('auth');


// Route::get('/login', function () {
//     return view('pages.auth.login');
// })->name('login');

// Route::get('/register', function () {
//     return view('pages.auth.register');
// })->name('register');

// Route::get('/users', function () {
//     return view('pages.users.index');
// });
