<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BulkDataController;
use App\Http\Controllers\MyPokemonController;
use App\Http\Controllers\CsvReplaceController;

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


// ダッシュボード（認証が必要）
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// ホーム（認証が必要）
Route::get('/home', function () {
    $pokemonCount = \App\Models\MyPokemon::where('user_id', auth()->id())->count();
    $partyCount = \App\Models\MyParty::where('user_id', auth()->id())->count();
    
    return view('home', compact('pokemonCount', 'partyCount'));
})->middleware('auth')->name('home');

// データ一括保存（認証が必要）
Route::get('/bulk-data', [BulkDataController::class, 'index'])->middleware('auth')->name('bulk-data');
Route::post('/bulk-data', [BulkDataController::class, 'store'])->middleware('auth')->name('bulk-data.store');

// ポケモン管理（認証が必要）
Route::get('/pokemon', [MyPokemonController::class, 'index'])->middleware('auth')->name('pokemon.index');
Route::get('/pokemon/create', [MyPokemonController::class, 'create'])->middleware('auth')->name('pokemon.create');
Route::post('/pokemon', [MyPokemonController::class, 'store'])->middleware('auth')->name('pokemon.store');
Route::get('/pokemon/forms', [MyPokemonController::class, 'getPokemonForms'])->middleware('auth')->name('pokemon.forms');
Route::get('/pokemon/search', [MyPokemonController::class, 'searchPokemons'])->middleware('auth')->name('pokemon.search');

// CSV置換ツール（認証が必要）
Route::get('/csv-replace', [CsvReplaceController::class, 'index'])->middleware('auth')->name('csv-replace.index');
Route::post('/csv-replace/process', [CsvReplaceController::class, 'process'])->middleware('auth')->name('csv-replace.process');
Route::post('/csv-replace/preview', [CsvReplaceController::class, 'preview'])->middleware('auth')->name('csv-replace.preview');
Route::get('/csv-replace/download/{filename}', [CsvReplaceController::class, 'download'])->middleware('auth')->name('csv-replace.download');

// CSV一括置換ツール（認証が必要）
Route::get('/csv-replace/batch', [CsvReplaceController::class, 'batch'])->middleware('auth')->name('csv-replace.batch');
Route::post('/csv-replace/batch-process', [CsvReplaceController::class, 'batchProcess'])->middleware('auth')->name('csv-replace.batch-process');

// トップページ（未認証時はログイン画面へリダイレクト）
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});
