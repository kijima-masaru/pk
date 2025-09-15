<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 認証ルート
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// パスワードリセット（後で実装）
Route::get('/password/reset', function () {
    return view('auth.forgot-password');
})->name('password.request');

// ダッシュボード（認証が必要）
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// ホーム（認証が必要）
Route::get('/home', function () {
    return view('home');
})->middleware('auth')->name('home');

// トップページ（未認証時はログイン画面へリダイレクト）
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});
