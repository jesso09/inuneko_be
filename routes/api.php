<?php

use App\Http\Controllers\AktivitasPenitipanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseCallController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PenitipanController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\VetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::group(['prefix' => 'auth', 'middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'getUser']);
    Route::get('user/{id}', [AuthController::class, 'getUserById']);
    Route::get('pp', [AuthController::class, 'getPP']);
});

Route::group(['prefix' => 'pet', 'middleware' => ['auth:sanctum']], function () {
    Route::get('index', [PetController::class, 'index']);
    Route::post('add', [PetController::class, 'store']);
    Route::post('edit/{id}', [PetController::class, 'update']);
    Route::get('show/{id}', [PetController::class, 'getPet']);
    Route::delete('delete/{id}', [PetController::class, 'destroy']);
    Route::get('petPict/{fileName}', [PetController::class, 'getPetImage']);
});

Route::group(['prefix' => 'produk', 'middleware' => ['auth:sanctum']], function () {
    Route::get('index', [ProdukController::class, 'index']);
    Route::get('show/{id}', [ProdukController::class, 'show']);
    Route::get('toko/{id}', [ProdukController::class, 'showToko']);
    Route::post('add', [ProdukController::class, 'store']);
    Route::post('edit/{id}', [ProdukController::class, 'update']);
    Route::get('produkPict/{fileName}', [ProdukController::class, 'getProductImage']);
    Route::delete('delete/{id}', [ProdukController::class, 'destroy']);
});

Route::group(['prefix' => 'vet', 'middleware' => ['auth:sanctum']], function () {
    Route::get('index', [VetController::class, 'index']);
});

Route::group(['prefix' => 'service', 'middleware' => ['auth:sanctum']], function () {
    Route::get('index', [LayananController::class, 'index']);
    Route::get('indexById/{id}', [LayananController::class, 'indexById']);
    Route::get('show/{id}', [LayananController::class, 'show']);
    Route::post('add', [LayananController::class, 'store']);
    Route::post('edit/{id}', [LayananController::class, 'update']);
    Route::delete('delete/{id}', [LayananController::class, 'destroy']);
});

Route::group(['prefix' => 'housecall', 'middleware' => ['auth:sanctum']], function () {
    Route::get('index', [HouseCallController::class, 'index']);
    Route::get('show/{id}', [HouseCallController::class, 'show']);
    Route::post('add', [HouseCallController::class, 'store']);
    Route::post('edit/{id}', [HouseCallController::class, 'update']);
    Route::delete('delete/{id}', [HouseCallController::class, 'destroy']);
});

Route::group(['prefix' => 'order', 'middleware' => ['auth:sanctum']], function () {
    Route::get('index', [PesananController::class, 'index']);
    Route::get('show/{id}', [PesananController::class, 'show']);
    Route::post('add', [PesananController::class, 'store']);
    Route::post('edit/{id}', [PesananController::class, 'update']);
    Route::post('status/{id}', [PesananController::class, 'changeStatus']);
});

Route::group(['prefix' => 'penitipan', 'middleware' => ['auth:sanctum']], function () {
    Route::get('index', [PenitipanController::class, 'index']);
    Route::get('show/{id}', [PenitipanController::class, 'show']);
    Route::post('add', [PenitipanController::class, 'store']);
    Route::post('edit/{id}', [PenitipanController::class, 'update']);
    Route::post('status/{id}', [PenitipanController::class, 'changeStatus']);
});

Route::group(['prefix' => 'activity', 'middleware' => ['auth:sanctum']], function () {
    Route::get('index/{id}', [AktivitasPenitipanController::class, 'index']);
    Route::get('show/{id}', [AktivitasPenitipanController::class, 'show']);
    Route::get('vid/{videoFileName}', [AktivitasPenitipanController::class, 'getVideoActivity']);
    Route::get('pict/{fotoFileName}', [AktivitasPenitipanController::class, 'getFotoActivity']);
    Route::post('add', [AktivitasPenitipanController::class, 'store']);
    Route::post('edit/{id}', [AktivitasPenitipanController::class, 'update']);
    Route::delete('delete/{id}', [AktivitasPenitipanController::class, 'destroy']);
});
