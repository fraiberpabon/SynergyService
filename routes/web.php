<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\WbInformeCampoController;



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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/generate-qrcode/{ids}', [QrCodeController::class, 'index']);
Route::get('/generate-qrcode/{ids}/{proy}', [QrCodeController::class, 'index']);
Route::get('/generate-qrcode-lab/{ids}', [QrCodeController::class, 'laboratorio_solicitud_muestras']);

Route::get('/QR/{alt}/{anc}', function ($alt, $anc) {
    return view('qrcode_ejemplo', ['alto' => $alt, 'ancho' => $anc]);
});


//Route::get('/', [ExportController::class, 'index']);
Route::get('/informe_hallazgo_excel', [WbInformeCampoController::class, 'get'])->name('export');
