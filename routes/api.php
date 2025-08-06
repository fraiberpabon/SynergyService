<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompaniaController;
use App\Http\Controllers\CostCodeController;
use App\Http\Controllers\encrypt;
use App\Http\Controllers\EquiposLiquidacion\WbEquiposLiquidacionController;
use App\Http\Controllers\MotivosInterrupcion\MotivosInterrupcionController;
use App\Http\Controllers\ProjectCompanyController;
use App\Http\Controllers\Transporte\WbConductoresController;
use App\Http\Controllers\Transporte\WbTransporteRegistroController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\WbConfiguracionesController;
use App\Http\Controllers\WbControlVersionesController;
use App\Http\Controllers\WbEquipoControlles;
use App\Http\Controllers\WbFormulasController;
use App\Http\Controllers\WbHorometrosUbicacionesController;
use App\Http\Controllers\WbLiberacionesFormatoController;
use App\Http\Controllers\WbMaterialListaController;
use App\Http\Controllers\WbSolicitudesController;
use App\Http\Controllers\WbTipoFormatoController;
use App\Http\Controllers\WbEquipoEstadoController;
use App\Http\Controllers\ParteDiario\InterrupcionesController;
use App\Http\Controllers\BasculaMovil\Transporte\WbBasculaMovilTransporteController;
use App\Http\Controllers\Turnos\SyTurnosController;
use App\Http\Controllers\CostCenter\CostCenterController;
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
    $fileContent = Storage::disk('imagenes')->get('paises/' . $nombreArchivo);

    return response($fileContent, 200)->header('Content-Type', 'image/svg+xml');
});
Route::get('/img-company/{nombreArchivo}', function ($nombreArchivo) {
    $fileContent = Storage::disk('imagenes')->get('company/' . $nombreArchivo);

    return response($fileContent, 200)->header('Content-Type', 'image/png');
});
Route::get('/img-company2/{nombreArchivo}', function ($nombreArchivo) {
    $patch = storage_path('app/imagenes/company/' . $nombreArchivo);
    $imagedata = file_get_contents($patch);
    $base64 = base64_encode($imagedata);

    return response($base64, 200)->header('Content-Type', 'application/json');
});
Route::prefix('tipo-formato')->group(function () {
    Route::get('/', [WbTipoFormatoController::class, 'get']);
});
/*
 * Start endpoint encriptados
 */
Route::middleware('desencript')->group(function () {
    Route::prefix('app/v1/')->middleware('isAndroid')->group(function () {
        Route::prefix('users')->group(function () {
            Route::patch('/bloquear-usuario/{imei}', [UsuarioController::class, 'bloquearPorImei']);
            Route::post('actualizar_contrasena_y_imei', [UsuarioController::class, 'actualizarContrasenaYImei']);
            Route::post('cambiar_contrasena', [UsuarioController::class, 'cambiarContrasena']);
        });
        Route::prefix('control-version')->group(function () {
            Route::post('/', [WbControlVersionesController::class, 'getByVersion']);
        });

        Route::prefix('horometros-ubicaciones')->controller(WbHorometrosUbicacionesController::class)->group(function () {
            Route::post('/insertar', 'post');
            Route::post('/insertar-paquete-background', 'postArray');
        });

        Route::prefix('transportes')->group(function () {
            Route::post('/insertar', [WbTransporteRegistroController::class, 'post']);
            Route::post('/insertar-v2', [WbTransporteRegistroController::class, 'postV2']);
            Route::post('/insertar-v3', [WbTransporteRegistroController::class, 'postV3']);
            Route::post('/insertar-paquete-background', [WbTransporteRegistroController::class, 'postArray']);
        });

        Route::prefix('bascula-movil')->controller(WbBasculaMovilTransporteController::class)->group(function () {
            Route::post('/insertar', 'post');
            Route::post('/insertar-paquete-background', 'postArray');
        });

        Route::prefix('solicitudes')->group(function () {
            //Route::post('/array-find', [WbSolicitudesController::class, 'getListForIds']);
            Route::post('/array-find', [WbSolicitudesController::class, 'getListForIdsV1']); //deprecated
            Route::post('/array-find-v2', [WbSolicitudesController::class, 'getListForIdsV2']);
            Route::post('/array-find-v3', [WbSolicitudesController::class, 'getListForIdsV3']);
            Route::post('/array-find-v4', [WbSolicitudesController::class, 'getListForIdsV4']);
            Route::get('/backgound/v4', [WbSolicitudesController::class, 'getAppV4']);
            Route::get('/backgound/v5', [WbSolicitudesController::class, 'getAppV5']);
        });

        Route::prefix('equipos')->group(function () {
            Route::post('/array-find', [WbEquipoControlles::class, 'getListForIds']);
            Route::get('/background', [WbEquipoControlles::class, 'equiposActivos']);
        });

        // conductores
        Route::prefix('conductores')->controller(WbConductoresController::class)->group(function () {
            Route::get('/backgound', 'get');
        });

        Route::prefix('parte-diario')->controller(InterrupcionesController::class)->group(function () {
            Route::post('/insertar', 'post');
            Route::post('/insertarD', 'postInterrupciones');
            Route::post('/insertar-paquete', 'postArray');
            Route::post('/insertar-paquete-distribuciones', 'postArrayDistribuciones');
            Route::post('/AnularParteDiarioMobile', 'AnularParteDiarioMobile');
        });

        Route::prefix('turnos')->controller(SyTurnosController::class)->group(function () {
            Route::get('/getTurnos', 'getTurnos');
        });
    });



    Route::middleware(['token', 'habilitado', 'proyecto'])->group(function () {
        Route::prefix('app/v1/')->group(function () {

            Route::prefix('interrupciones')->group(function () {
                Route::get('/obtenerInterrupciones', [InterrupcionesController::class, 'get']);
            });
            Route::prefix('cost-code')->group(function () {
                Route::get('/activos', [CostCodeController::class, 'getActivos']);
            });
            Route::prefix('equipos')->group(function () {
                Route::get('/', [WbEquipoControlles::class, 'equiposActivos']);
                Route::get('/estado', [WbEquipoEstadoController::class, 'getActivosForProject']);
                Route::post('/insertar-peso-paquete', [WbEquipoControlles::class, 'postPesoArray']);
            });
            Route::prefix('equipos-liquidacion')->controller(WbEquiposLiquidacionController::class)->group(function () {
                Route::get('/last-date-liquidation', 'getFechaUltimoCierre');
            });

            Route::prefix('formulas')->group(function () {
                Route::get('/', [WbFormulasController::class, 'get']);
                Route::get('/v2', [WbFormulasController::class, 'getV2']);
                Route::get('/v3', [WbFormulasController::class, 'getV3']);
                Route::get('/composicion', [WbFormulasController::class, 'getComposicion']);
            });

            Route::prefix('solicitudes')->group(function () {
                Route::get('/', [WbSolicitudesController::class, 'getApp']); // deprecated
                Route::get('/v2', [WbSolicitudesController::class, 'getAppV2']); //deprecated
                Route::get('/v3', [WbSolicitudesController::class, 'getAppV3']);
                Route::get('/v4', [WbSolicitudesController::class, 'getAppV4']);
                Route::get('/v5', [WbSolicitudesController::class, 'getAppV5']);
            });

            Route::prefix('material-lista')->controller(WbMaterialListaController::class)->group(function () {
                Route::get('/', 'get');
            });

            Route::prefix('horometros-ubicaciones')->controller(WbHorometrosUbicacionesController::class)->group(function () {
                Route::post('/insertar-paquete', 'postArray');
            });

            Route::prefix('transportes')->controller(WbTransporteRegistroController::class)->group(function () {
                Route::post('/insertar-paquete', 'postArray');
            });

            Route::prefix('bascula-movil')->controller(WbBasculaMovilTransporteController::class)->group(function () {
                Route::post('/transporte-insertar-paquete', 'postArray');
            });

            // configuraciones
            Route::prefix('configuraciones')->group(function () {
                Route::get('/app', [WbConfiguracionesController::class, 'get']);
            });

            // conductores
            Route::prefix('conductores')->controller(WbConductoresController::class)->group(function () {
                Route::get('/app', 'get');
                Route::post('/insertar-paquete', 'postArray');
            });

            // formatos
            Route::prefix('formatos')->controller(WbLiberacionesFormatoController::class)->group(function () {
                Route::get('/app', 'get');
            });

            Route::prefix('motivo_interrupcion')->controller(MotivosInterrupcionController::class)->group(function () {
                Route::get('/getMobile', 'getMobile');
            });
        });

        Route::prefix('parte_diario')->controller(InterrupcionesController::class)->group(function () {
            Route::get('/getParteDiarioWeb', 'GetParteDiarioWeb');
            Route::put('/anularParteDiario/{id_parte_diario}', 'AnularParteDiario');
            Route::post('/AnularParteDiarioMobile', 'AnularParteDiarioMobile');
        });



        /*
         * End endpoint para WebuApp
         */


        /*
         * Start endpoint para la pagina web
         */
        Route::prefix('cost-center')->controller(CostCenterController::class)->group(function () {
            Route::get('/getCentroCostoMobile', 'getCostCenterMobile');
            Route::put('/anularCentroCosto', 'AnularCentroCosto');
            Route::post('/crear', 'postCentroCosto');
            Route::put('/actualizar', 'updateCentroCosto');
        });


        Route::prefix('parte_diario')->controller(InterrupcionesController::class)->group(function () {
            Route::get('/getParteDiarioWeb', 'GetParteDiarioWeb');
            Route::put('/anularParteDiario/{id_parte_diario}', 'AnularParteDiario');
            Route::post('/AnularParteDiarioMobile', 'AnularParteDiarioMobile');
            Route::put('/editar', 'editarParteDiario');
        });
        /*
         * End endpoint para la pagina web
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

    // Route::prefix('MotivoInterrupcion')->group(function () {
    //     Route::get('/getMobile', [MotivosInterrupcionController::class, 'getMobile']);
    // });


    // Route::prefix('parte_diario')->controller(InterrupcionesController::class)->group(function () {
    //     Route::get('/getParteDiarioWeb', 'GetParteDiarioWeb');
    //     Route::put('/anularParteDiario/{id_parte_diario}', 'AnularParteDiario');
    //     Route::put('/AnularParteDiarioMobile', 'AnularParteDiarioMobile');
    //     Route::put('/editar', 'editarParteDiario');
    // });


    Route::prefix('equipos')->group(function () {
        Route::get('/', [WbEquipoControlles::class, 'equiposActivos']);
        Route::get('/estado', [WbEquipoEstadoController::class, 'getActivosForProject']);
    });



    Route::get('tables', function () { });
    // Route::prefix('parte-diario')->controller(InterrupcionesController::class)->group(function () {
    //     Route::post('/insertar', 'post');
    //     Route::post('/insertarD', 'postInterrupciones');
    // });


    Route::get('encrypt/{tipoPassword}', [encrypt::class, 'index']);
    Route::get('encrypt', [encrypt::class, 'index']);
    // Route::update('/equipos/actualizar', [WbEquipoControlles::class, 'update']);

    // ACTIVIDADES POR DEPARTAMENTO
    // --------------------------------------------------------------------------------------------------------------------------
    // pendiente eliminar estos servicios - OBSOLETOS

    // hasta aqui
    // ---------------------------------------------------------------------------------------------------------------------------

    // rutas para pruebas en desarrollo
    /* Route::prefix('csv-dev')->group(function () {
        Route::post('/', [WbExcelController::class, 'post']);
    }); */
    // ----------------------------------------------------------------------------------------------------------------------------

    /* Route::prefix('test')->group(function () {
        Route::get('/', [WbSolicitudesController::class, 'getApp']);
        Route::get('/v2', [WbSolicitudesController::class, 'getAppV2']);
        Route::get('/v3', [WbSolicitudesController::class, 'getAppV3']);
        Route::get('/v4', [WbFormulasController::class, 'getV2']);
        Route::get('/v5', [WbSolicitudesController::class, 'getListForIdsV2']);
        Route::get('/v6', [WbSolicitudesController::class, 'getListForIdsV1']);
        Route::get('/v7', [WbSolicitudesController::class, 'getAppV5']);
        Route::get('/v8', [WbFormulasController::class, 'getV2']);
    }); */
});

/*
 * End rutas huerfanas
 */
