<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CnfCostCenterController;
use App\Http\Controllers\CompaniaController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\CostCodeController;
use App\Http\Controllers\encrypt;
use App\Http\Controllers\EnviarCorreo;
use App\Http\Controllers\EquipementsController;
use App\Http\Controllers\generatePDF_C;
use App\Http\Controllers\HtrUsuariosController;
use App\Http\Controllers\NotificacionSMS;
use App\Http\Controllers\preoperacional_actividad_C;
use App\Http\Controllers\preoperacional_C;
use App\Http\Controllers\ProjectCompanyController;
use App\Http\Controllers\SolicitudConcretoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Wb_Liberaciones_Reponsable;
use App\Http\Controllers\Wb_solicitud_liberaciones_act_controller;
use App\Http\Controllers\WbAsfaltAsignController;
use App\Http\Controllers\WbConfiguracionController;
use App\Http\Controllers\WbControlVersionesController;
use App\Http\Controllers\WbEquipoControlles;
use App\Http\Controllers\WbFormulasController;
use App\Http\Controllers\WbHitosController;
use App\Http\Controllers\WbHorometrosUbicacionesControlles;
use App\Http\Controllers\WbLiberacionesFormatosActController;
use App\Http\Controllers\WbMaterialListaController;
use App\Http\Controllers\WbSolicitudLiberacionesFirmasController;
use App\Http\Controllers\WbSolicitudesController;
use App\Http\Controllers\WbTipoFormatoController;
use App\Http\Controllers\WbTramosController;
use App\Http\Controllers\WbTramosHitosAsignController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

Session::start();

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

Route::get('/img-bandera/{nombreArchivo}', function ($nombreArchivo) {
    $fileContent = Storage::disk('imagenes')->get('paises/'.$nombreArchivo);

    return response($fileContent, 200)->header('Content-Type', 'image/svg+xml');
});
Route::get('/img-company/{nombreArchivo}', function ($nombreArchivo) {
    $fileContent = Storage::disk('imagenes')->get('company/'.$nombreArchivo);

    return response($fileContent, 200)->header('Content-Type', 'image/png');
});
Route::get('/img-company2/{nombreArchivo}', function ($nombreArchivo) {
    $patch = storage_path('app/imagenes/company/'.$nombreArchivo);
    $imagedata = file_get_contents($patch);
    $base64 = base64_encode($imagedata);

    return response($base64, 200)->header('Content-Type', 'application/json');
});
Route::prefix('tipo-formato')->group(function () {
    Route::get('/', [WbTipoFormatoController::class, 'get']);
});
/*
 * Start endpoint para soportar la version anterior
 * Eliminar en la proxima actualizacion
 */
Route::prefix('app')->middleware('isAndroid')->group(function () {

});
/*
 * End endpoint para soportar la version anterior
 */
/*
 * Start endpoint encriptados
 */
Route::middleware('desencript')->group(function () {
    /*
     * Aqui deben caer las solicitudes de versiones de WebuApp nueva
     */
    Route::prefix('app/v1/')->middleware('isAndroid')->group(function () {
        Route::prefix('users')->group(function () {
            Route::patch('/bloquear-usuario/{imei}', [UsuarioController::class, 'bloquearPorImei']);
            Route::post('actualizar_contrasena_y_imei', [UsuarioController::class, 'actualizarContrasenaYImei']);
            Route::post('cambiar_contrasena', [UsuarioController::class, 'cambiarContrasena']);
        });
        Route::prefix('control-version')->group(function () {
            Route::post('/', [WbControlVersionesController::class, 'getByVersion']);
        });
        Route::prefix('htr-usuarios')->group(function () {
            Route::post('/', [HtrUsuariosController::class, 'post']);
        });
    });
    Route::middleware(['token', 'habilitado', 'proyecto'])->group(function () {
        /*
         * Aqui deben caer las solicitudes de versiones de WebuApp nueva
         */
        Route::prefix('app/v1/')->group(function () {
            Route::prefix('cost-code')->group(function () {
                Route::get('/activos', [CostCodeController::class, 'getActivos']);
            });
            Route::prefix('equipos')->group(function () {
                Route::get('/', [WbEquipoControlles::class, 'equiposActivos']);
            });
            Route::prefix('formulas')->group(function() {
                Route::get('/', [WbFormulasController::class, 'get']);
                Route::get('/composicion', [WbFormulasController::class, 'getComposicion']);
            });

            Route::prefix('solicitudes')->group(function () {
                Route::get('/', [WbSolicitudesController::class, 'getApp']);
            });

            Route::prefix('material-lista')->controller(WbMaterialListaController::class)->group(function () {
                Route::get('/', 'get');
            });

            Route::prefix('horometros-ubicaciones')->controller(WbHorometrosUbicacionesControlles::class)->group(function () {
                Route::post('/insertar-paquete', 'postArray');
            });

            // configuraciones
            Route::prefix('configuracion')->group(function () {
                Route::get('/liberacion_capa/fecha', [WbConfiguracionController::class, 'getfecha_liberacion_capa']);
                Route::get('/', [WbConfiguracionController::class, 'get']);
            });
        });
        /*
         * End endpoint para WebuApp
         */
        /*
         * Start endpoint para la pagina web
         */

    });

    /*
     * end middelware token, habilitado, proyecto
     */
    /*
     * Start rutas que no necesitan autenticacion
     */
    Route::prefix('proyecto')->controller(ProjectCompanyController::class)->group(function () {
        Route::get('/', [ProjectCompanyController::class, 'get']); // si el usuario no esta autenticado solo devolvera los nombres de los proyecto
    });
    Route::prefix('compania')->controller(CompaniaController::class)->group(function () {
        Route::get('/proyecto/{proyecto}', 'getByProyecto'); // si el usuario no esta autenticado solo devolvera los nombres de la compañia
    });
    Route::prefix('users')->group(function () {
        Route::post('/usuarioExterno', [UsuarioController::class, 'insert']);
        Route::middleware('isAndroid')->post('actualizar_contrasena_y_imei', [UsuarioController::class, 'actualizarContrasenaYImei']);
    });
    Route::post('login', [AuthController::class, 'login']);
    Route::post('enviar-numero-telefono', [AuthController::class, 'confirmarNumeroTelefono']);
    Route::post('enviar-token-id-y-telefono', [AuthController::class, 'verificarTelefono']);
    /*
     * End rutas que no necesitan autenticacion
     */
});
/*
 * End de middelware desencript
 */



/*
 * Start rutas huerfanas
 */
Route::prefix('')->group(function () {
    Route::get('validar', function () {
        $res = [
            'success' => true,
            'mensaje' => 'Conectado',
            'encript' => false,
        ];

        return response()->json($res);
    });

    Route::prefix('Login')->group(function () {
        Route::get('/confirmarNum', [UsuarioController::class, 'enviarCodigo']);
        Route::get('/confirmarCod', [UsuarioController::class, 'confirmarNumero']);
    });


    Route::get('tables', function () {
    });

    Route::get('encrypt/{tipoPassword}', [encrypt::class, 'index']);
    Route::get('encrypt', [encrypt::class, 'index']);
    // Route::update('/equipos/actualizar', [WbEquipoControlles::class, 'update']);

    // ACTIVIDADES POR DEPARTAMENTO
    // --------------------------------------------------------------------------------------------------------------------------
    // pendiente eliminar estos servicios - OBSOLETOS

    // hasta aqui
    // ---------------------------------------------------------------------------------------------------------------------------

    // rutas para pruebas en desarrollo
    Route::prefix('equipos-dev')->group(function () {
        Route::get('/', [WbEquipoControlles::class, 'equiposActivos']);
    });

    Route::prefix('material-lista-dev')->controller(WbMaterialListaController::class)->group(function () {
        Route::get('/', 'get');
    });

    Route::prefix('solicitud-materiales-dev')->group(function () {
        Route::get('/', [WbSolicitudesController::class, 'getApp']);
    });

    Route::prefix('formula-materiales-dev')->group(function() {
        Route::get('/', [WbFormulasController::class, 'get']);
    });
    // ----------------------------------------------------------------------------------------------------------------------------
});
/*
 * End rutas huerfanas
 */
