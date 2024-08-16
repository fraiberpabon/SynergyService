<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AsfaltoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CnfCostCenterController;
use App\Http\Controllers\CompaniaController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\CostCodeController;
use App\Http\Controllers\encrypt;
use App\Http\Controllers\EnviarCorreo;
use App\Http\Controllers\EquipementsController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\EstrucFormulaController;
use App\Http\Controllers\EstrucTiposController;
use App\Http\Controllers\EstructurasController;
use App\Http\Controllers\EstructuraTipoElementoController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\generatePDF_C;
use App\Http\Controllers\HtrSolicitudController;
use App\Http\Controllers\HtrUsuariosController;
use App\Http\Controllers\Laboratorio\WbEnsayosController;
use App\Http\Controllers\Laboratorio\WbLaboratoriosController;
use App\Http\Controllers\Laboratorio\WbMuestraRegistroController;
use App\Http\Controllers\Laboratorio\WbMuestraRegistroEnsayoController;
use App\Http\Controllers\Laboratorio\WbSolicitudMuestraController;
use App\Http\Controllers\Laboratorio\WbTipoControlController;
use App\Http\Controllers\Laboratorio\WbTipoMuestreoController;
use App\Http\Controllers\LiberacionesActividadesController;
use App\Http\Controllers\LiberacionesController;
use App\Http\Controllers\LogAllTableController;
use App\Http\Controllers\Motivo_rechazo\WbMotivoRechazoAsignacionController;
use App\Http\Controllers\Motivo_rechazo\WbMotivoRechazoController;
use App\Http\Controllers\NotificacionSMS;
use App\Http\Controllers\PlanillaControlAsfaltoController;
use App\Http\Controllers\PlanillaControlConcretoController;
use App\Http\Controllers\PlantaController;
use App\Http\Controllers\preoperacional_actividad_C;
use App\Http\Controllers\preoperacional_C;
use App\Http\Controllers\ProjectCompanyController;
use App\Http\Controllers\SolicitudAsfaltoController;
use App\Http\Controllers\SolicitudConcretoController;
use App\Http\Controllers\SyncAccionesController;
use App\Http\Controllers\SyncBasculasController;
use App\Http\Controllers\SyncConfigController;
use App\Http\Controllers\SyncCostDescController;
use App\Http\Controllers\SyncEmpleadoController;
use App\Http\Controllers\SyncIndicadorController;
use App\Http\Controllers\SyncItemsTransportPainelController;
use App\Http\Controllers\SyncJobsController;
use App\Http\Controllers\SyncLocationController;
use App\Http\Controllers\SyncMsoController;
use App\Http\Controllers\SyncRegistroController;
use App\Http\Controllers\TipoMezclaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuPlantaController;
use App\Http\Controllers\Wb_Liberaciones_Reponsable;
use App\Http\Controllers\Wb_PermisosController;
use App\Http\Controllers\Wb_solicitud_liberaciones_act_controller;
use App\Http\Controllers\WbAbscisasController;
use App\Http\Controllers\WbAccionEstructuraController;
use App\Http\Controllers\WbAsfaltAsignController;
use App\Http\Controllers\WbAsfaltFormulaController;
use App\Http\Controllers\WbCentroProduccionHitosController;
use App\Http\Controllers\WbConfiguracionController;
use App\Http\Controllers\WbConfiguracionesController;
use App\Http\Controllers\WbControlVersionesController;
use App\Http\Controllers\WbEquipoControlles;
use App\Http\Controllers\WbEstrucConfigAsignController;
use App\Http\Controllers\WbEstrucConfigController;
use App\Http\Controllers\WbEstrucCriteriosController;
use App\Http\Controllers\WbEstructFirmasController;
use App\Http\Controllers\WbEstructPerfilFirmaController;
use App\Http\Controllers\WbFormulaCapaController;
use App\Http\Controllers\WbFormulaCentroProduccionController;
use App\Http\Controllers\WbFormulaListaController;
use App\Http\Controllers\WbHallazgoController;
use App\Http\Controllers\WbHitosAbcisasController;
use App\Http\Controllers\WbHitosController;
use App\Http\Controllers\WbInformeCampoController;
use App\Http\Controllers\WbInformeCampoHasHallazgoController;
use App\Http\Controllers\WbLiberacionesFormatosActController;
use App\Http\Controllers\WbLicenciaAmbientalController;
use App\Http\Controllers\WbMaterialCapaController;
use App\Http\Controllers\WbMaterialCentroProduccionController;
use App\Http\Controllers\WbMaterialFormulaController;
use App\Http\Controllers\WbMaterialListaController;
use App\Http\Controllers\WbMaterialPresupuestadoController;
use App\Http\Controllers\WbMaterialTiposController;
use App\Http\Controllers\WbPaisController;
use App\Http\Controllers\WbPlantillaReporteController;
use App\Http\Controllers\WbProgramadorTareasController;
use App\Http\Controllers\WbReporteInspeccionCalidadCalifiController;
use App\Http\Controllers\WbReporteInspeccionCalidadController;
use App\Http\Controllers\WbRutaNacionalController;
use App\Http\Controllers\WbSeguridadSitioController;
use App\Http\Controllers\WbSeguridadSitioEvidenciaController;
use App\Http\Controllers\WbSeguridadSitioHistorialController;
use App\Http\Controllers\WbSeguridadSitioTurnoController;
use App\Http\Controllers\WbSeguriRolesController;
use App\Http\Controllers\WbSeguriRolesPermisoController;
use App\Http\Controllers\WbSolicitudLiberacionesController;
use App\Http\Controllers\WbSolicitudLiberacionesFirmasController;
use App\Http\Controllers\WbSolicitudMaterialesController;
use App\Http\Controllers\WbTareasProgramadasController;
use App\Http\Controllers\WbTareasProgramadasViewsController;
use App\Http\Controllers\WbTemaInterfazController;
use App\Http\Controllers\WbTipoCalzadaController;
use App\Http\Controllers\WbTipoCapaController;
use App\Http\Controllers\WbTipoCarrilController;
use App\Http\Controllers\WbTipoDeAdaptacionController;
use App\Http\Controllers\WbTipoDeObraController;
use App\Http\Controllers\WbTipoDePasoDeFaunaController;
use App\Http\Controllers\WbTipoEquipoController;
use App\Http\Controllers\WbTipoFormatoController;
use App\Http\Controllers\WbTipoViaController;
use App\Http\Controllers\WbTramosController;
use App\Http\Controllers\WbTramosHitosAsignController;
use App\Http\Controllers\WbUsuarioProyectoController;
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

/*
 * Se puso aqui sin middleaware por que daba error con retrofit y la cabecera para reconocer el tipo de dispositivo cliente
 */
Route::prefix('app/estructura')->group(function () {
    Route::put('/finalizar/{id}', [EstructurasController::class, 'finalizarEstructuraDeprecated']);
});

Route::post('/reportecalidad/items', [WbReporteInspeccionCalidadCalifiController::class, 'post']);
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
    Route::get('/actividades/{id}', [WbLiberacionesFormatosActController::class, 'getActividadV2Deprecated']);
    Route::prefix('estructura')->group(function () {
        Route::get('/nomenclatura/{tramo}/{hito}/{tipoEstructura}', [EstructurasController::class, 'getNomenclaturasFinalizarDeprecated']);
        Route::get('/nomenclaturaPorLicencia/{tramo}/{hito}/{licencia}', [EstructurasController::class, 'getNomenclaturasFinalizarPorLicenciaDeprecated']);
        Route::get('/getTipoEstructura/{tramo}/{hito}', [EstructurasController::class, 'getTipoEstructuraDeprecated']);
        Route::get('/getLicencia/{tramo}/{hito}', [EstructurasController::class, 'getLicenciaDeprecated']);
        Route::get('/sincronizarApp/{tramo}', [EstructurasController::class, 'getParaSincronizarAppDeprecated']);
    });
    Route::prefix('actividades')->controller(EstructurasController::class)->group(function () {
        Route::get('/responsable/{id}', [Wb_Liberaciones_Reponsable::class, 'getResponsable']);
        Route::get('/completas', [ActividadController::class, 'actividadesCompletasDeprecated']);
        Route::get('/formato/{id}', [WbLiberacionesFormatosActController::class, 'getActividadV2Deprecated']);
    });
    Route::prefix('hito')->controller(WbHitosController::class)->group(function () {
        Route::get('/tramo/{id}', [WbHitosController::class, 'getActivosByTramosDeprecated']);
    });
    Route::prefix('hitos')->group(function () {
        Route::get('/activos/{proyect}', [WbTramosHitosAsignController::class, 'getActivosDeprecated']);
    });
    Route::prefix('tramo')->group(function () {
        Route::get('/', [WbTramosController::class, 'getTramosActivosDeprecated']);
    });
    Route::get('/actividades/{id}', [WbLiberacionesFormatosActController::class, 'getActividadV2Deprecated']);

    // ruta de carga de reportes de inspeccion
    Route::post('/reportecalidad', [WbReporteInspeccionCalidadController::class, 'postDeprecated']);
    Route::post('/reportecalidad/items', [WbReporteInspeccionCalidadCalifiController::class, 'postDeprecated']);
    // ruta de carga de calificacion de reportes

    // ruta de carga de calificaciones de preoperacionales
    Route::post('/preoperacional/actividades', [preoperacional_actividad_C::class, 'postDeprecated']);
    // ruta de sincronizacion del preoperacional
    Route::post('/preoperacional', [preoperacional_C::class, 'postDeprecated']);
    Route::prefix('solicitud-concreto')->group(function () {
        Route::post('/solicitar', [SolicitudConcretoController::class, 'postDeprecated']);
    });
    Route::post('/solicitud/material/frente', [WbSolicitudMaterialesController::class, 'postforFrentesDeprecated']);
    // ruta para la insersion de una solicitud de material para centros de produccion
    Route::post('/solicitud/material/centro', [WbSolicitudMaterialesController::class, 'postforCentrosDeprecated']);
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
            Route::prefix('asignacion')->group(function () {
                Route::get('/byArea/{id}', [AsignacionController::class, 'getByArea']);
            });
            Route::prefix('asfalt-asign')->group(function () {
                Route::get('/activos', [WbAsfaltAsignController::class, 'getActivos']);
            });
            Route::prefix('actividades')->group(function () {
                Route::get('/', [ActividadController::class, 'get']);
                Route::get('/responsable/{id}', [Wb_Liberaciones_Reponsable::class, 'getResponsable']);
                Route::get('/completas', [ActividadController::class, 'actividadesCompletas']);
                Route::get('/formato/{id}', [WbLiberacionesFormatosActController::class, 'getActividadV2']);
                Route::get('/by-solicitud/{id}', [ActividadController::class, 'getActividadBySolicitud']);
                // CONSULTAR ACTIVIDADES
                Route::get('/calificar/{id}', [Wb_solicitud_liberaciones_act_controller::class, 'getActividad']);
                Route::post('/calificar', [Wb_solicitud_liberaciones_act_controller::class, 'CambiarEstado']);
                // Route::get('/actividades/calificar/{id}', [Wb_solicitud_liberaciones_act_controller::class, 'getActividad']);

                // FIRMAR ACTIVIDAD
                Route::post('/firmar', [WbSolicitudLiberacionesFirmasController::class, 'Firmar']);
                Route::post('/calificar-firmar', [WbSolicitudLiberacionesFirmasController::class, 'firmarV2']);
            });
            Route::prefix('cost-center')->group(function () {
                Route::get('/', [CnfCostCenterController::class, 'getEnabledAndDistributable']);
            });
            Route::prefix('cost-code')->group(function () {
                Route::get('/activos', [CostCodeController::class, 'getActivos']);
            });
            Route::prefix('equipos')->group(function () {
                Route::get('/', [WbEquipoControlles::class, 'equiposActivos']);
            });
            Route::prefix('estruc-formula')->controller(EstrucFormulaController::class)->group(function () {
                Route::get('/', [EstrucFormulaController::class, 'get']);
            });
            Route::prefix('estructura')->group(function () {
                Route::put('/finalizar/{id}', [EstructurasController::class, 'finalizarEstructura']);
                Route::get('/nomenclatura/{tramo}/{hito}/{tipoEstructura}', [EstructurasController::class, 'getNomenclaturasFinalizar']);
                Route::get('/nomenclaturaPorLicencia/{tramo}/{hito}/{licencia}', [EstructurasController::class, 'getNomenclaturasFinalizarPorLicencia']);
                Route::get('/getTipoEstructura/{tramo}/{hito}', [EstructurasController::class, 'getTipoEstructura']);
                Route::get('/getLicencia/{tramo}/{hito}', [EstructurasController::class, 'getLicencia']);
                Route::get('/sincronizarApp/{tramo}', [EstructurasController::class, 'getParaSincronizarApp']);
            });
            Route::prefix('formula-centro-produccion')->group(function () {
                Route::get('/por-formula-y-hito/{formula}/{hito}', [WbFormulaCentroProduccionController::class, 'getPorFormulaListaYhito']);
                Route::get('/centros/{material}/{hito}', [WbFormulaCentroProduccionController::class, 'getCentrobyFormulaHito']);
                Route::get('/formulas/', [WbFormulaCentroProduccionController::class, 'getFormulawithCenter']);
            });
            Route::prefix('hito')->group(function () {
                Route::get('/tramo/{id}', [WbHitosController::class, 'getActivosByTramos']);
            });
            Route::prefix('hitos')->group(function () {
                Route::get('/activos/', [WbTramosHitosAsignController::class, 'getActivos']);
            });
            Route::prefix('hito-abcisa')->group(function () {
                Route::get('/', [WbHitosAbcisasController::class, 'getParaSync']);
            });
            Route::prefix('tipo-via')->controller(WbTipoViaController::class)->group(function () {
                Route::get('/activos', 'getActivos');
            });
            Route::prefix('formula-capa')->group(function () {
                Route::get('/por-tipo-capa/{tipoCapa}', [WbFormulaCapaController::class, 'getPorTipoCapa']);
                Route::get('/activos-por-tipo-capa/{tipoCapa}', [WbFormulaCapaController::class, 'getActivosPorTipoCapa']);
                Route::get('/{id}', [WbFormulaCapaController::class, 'getFormulaCapa']);
            });
            Route::prefix('hallazgo')->group(function () {
                Route::get('/', [WbHallazgoController::class, 'get']);
            });
            Route::prefix('htr-solicitud')->group(function () {
                Route::post('/', [HtrSolicitudController::class, 'post']);
            });
            Route::prefix('htr-usuarios')->group(function () {
                Route::get('/', [HtrUsuariosController::class, 'get']);
            });
            Route::prefix('informe-campo')->group(function () {
                Route::get('/', [WbInformeCampoController::class, 'get']);
                Route::post('/', [WbInformeCampoController::class, 'post'])->middleware('permiso:CREAR_INFORME_DE_CAMPO');
                Route::post('/masivo', [WbInformeCampoController::class, 'postMasivo'])->middleware('permiso:CREAR_INFORME_DE_CAMPO');
                Route::patch('/estado/{id}/{estado}', [WbInformeCampoController::class, 'cambiarEstado'])->middleware('permiso:CAMBIAR_ESTADO_INFORME_DE_CAMPO');
            });
            Route::prefix('liberacion-actividad')->group(function () {
                Route::put('/modificar-por-activiad-y-solicitud', [LiberacionesActividadesController::class, 'modificarPorActividadYSolicitud']);
            });
            Route::prefix('material-capa')->group(function () {
                Route::get('/', [WbMaterialCapaController::class, 'get']);
                Route::get('/{id}', [WbMaterialCapaController::class, 'getMaterialCapa']);
                Route::get('/por-capa-disponible/{tipoDeCapa}', [WbMaterialCapaController::class, 'materialesPorCapaDisponible']);
            });
            Route::prefix('material-centro-produccion')->group(function () {
                Route::get('/por-material-y-hito/{material}', [WbMaterialCentroProduccionController::class, 'getPorMaterialYHito']);
                Route::get('/por-material-y-hito/{material}/{hito}', [WbMaterialCentroProduccionController::class, 'getPorMaterialYHito']);
                Route::get('/por-material-y-hito-no-disponible/{material}/{hito}', [WbMaterialCentroProduccionController::class, 'getPorMaterialYHitoNoDIsponibleParaMateriales']);
                Route::get('/por-formula-y-hito-para-formula/{formula}/{hito}', [WbMaterialCentroProduccionController::class, 'getPorFormulaYHitoNoDIsponibleParaFormulas']);
                Route::get('/centros/{material}/{hito}', [WbMaterialCentroProduccionController::class, 'getCentrobyMaterialHito']);
                Route::get('/materiales', [WbMaterialCentroProduccionController::class, 'getMaterialwithCenter']);
            });
            Route::prefix('plantilla-reporte')->group(function () {
                Route::get('/tipo-formato/{id}', [WbPlantillaReporteController::class, 'getByTipoFormato']);
            });
            Route::prefix('preoperacional')->group(function () {
                // ruta de carga de calificaciones de preoperacionales
                Route::post('/actividades', [preoperacional_actividad_C::class, 'post']);
                // ruta de sincronizacion del preoperacional
                Route::post('', [preoperacional_C::class, 'post']);
            });
            Route::prefix('reportecalidad')->group(function () {
                // ruta de carga de reportes de inspeccion
                Route::post('', [WbReporteInspeccionCalidadController::class, 'post']);
                Route::post('/items', [WbReporteInspeccionCalidadCalifiController::class, 'post']);
            });
            Route::prefix('ruta-nacional')->group(function () {
                Route::get('', [WbRutaNacionalController::class, 'get']);
                Route::post('', [WbRutaNacionalController::class, 'post'])->middleware('permiso:CREAR_RUTA_NACIONAL');
                Route::put('/{id}', [WbRutaNacionalController::class, 'update'])->middleware('permiso:MODIFICAR_RUTA_NACIONAL');
            });
            Route::prefix('solicitud')->group(function () {
                // ruta para la insersion de una solicitud de material para frentes de obra
                Route::post('/material/frente', [WbSolicitudMaterialesController::class, 'postforFrentes']);
                // ruta para la insersion de una solicitud de material para centros de produccion
                Route::post('/material/centro', [WbSolicitudMaterialesController::class, 'postforCentros']);
            });
            Route::prefix('solicitud-asfalto')->group(function () {
                Route::get('', [AsfaltoController::class, 'get']);
                Route::post('', [SolicitudAsfaltoController::class, 'post']);
                Route::put('/cerrar/{id}', [SolicitudAsfaltoController::class, 'cerrarSolicitud']);
            });
            Route::prefix('solicitud-concreto')->group(function () {
                Route::get('/', [SolicitudConcretoController::class, 'get']); // envia estado que se quiere
                Route::get('/borrador', [SolicitudConcretoController::class, 'getBorradores']);
                Route::get('/estado/pendiente', [SolicitudConcretoController::class, 'getPendientes']);
                Route::post('/solicitar', [SolicitudConcretoController::class, 'post']);
                Route::put('/borrador/{id}', [SolicitudConcretoController::class, 'borador']);
                Route::put('/cerrar/{id}', [SolicitudConcretoController::class, 'cerrarLiberaccion']);
                Route::put('/{id}', [SolicitudConcretoController::class, 'update']);
            });

            Route::prefix('solicitud-liberacion')->group(function () {
                Route::post('/', [WbSolicitudLiberacionesController::class, 'post1']); //deprecated 23-07-2024 - disponible hastal a version 2.0.3.0
                Route::post('/registrar', [WbSolicitudLiberacionesController::class, 'post2']);
                Route::get('/', [WbSolicitudLiberacionesController::class, 'get']);
                Route::get('/getByFecha', [WbSolicitudLiberacionesController::class, 'getByFecha']);
                Route::get('/formula', [WbSolicitudLiberacionesController::class, 'getComposicionPorSolicitudLiberacion']);
                Route::get('/solicitud', [WbSolicitudLiberacionesController::class, 'getV2']);
                Route::get('/solicitud/{fecha}', [WbSolicitudLiberacionesController::class, 'getV2']);
                Route::get('/solicitudes', [WbSolicitudLiberacionesController::class, 'getV3']);
                Route::get('/solicitudes/{fecha}', [WbSolicitudLiberacionesController::class, 'getV3']);
            });
            Route::prefix('solicitud-liberacion-firma')->group(function () {
                Route::get('/', [WbSolicitudLiberacionesFirmasController::class, 'get']);
            });
            Route::prefix('solicitud-materiales')->group(function () {
                Route::get('/', [WbSolicitudMaterialesController::class, 'getApp']);
                Route::get('/byFecha', [WbSolicitudMaterialesController::class, 'getAppByFecha']);
                Route::patch('/cerrar/{id}', [WbSolicitudMaterialesController::class, 'cerrarSolicitudMaterial']);
            });
            Route::prefix('tipo-calzada')->group(function () {
                Route::get('/', [WbTipoCalzadaController::class, 'get']);
                Route::get('/activos', [WbTipoCalzadaController::class, 'getActivos']);
            });
            Route::prefix('tipo-capa')->group(function () {
                Route::get('/', [WbTipoCapaController::class, 'get']);
                Route::get('/activos', [WbTipoCapaController::class, 'getActivos']);
                Route::get('/activos-con-actividad', [WbTipoCapaController::class, 'getActivosConActividad']);
                Route::get('/activos-general', [WbTipoCapaController::class, 'getActivosGeneral']);
            });
            Route::prefix('tipo-mezcla')->group(function () {
                Route::get('/activos', [TipoMezclaController::class, 'getActivos']);
            });
            Route::prefix('tramo')->group(function () {
                Route::get('/', [WbTramosController::class, 'getTramosActivos']);
            });
            Route::prefix('tipo-carril')->group(function () {
                Route::get('/', [WbTipoCarrilController::class, 'get']);
            });
            Route::prefix('tipo-formato')->group(function () {
                Route::get('/', [WbTipoFormatoController::class, 'get']);
            });
            Route::prefix('plantilla-reporte')->group(function () {
                Route::get('/tipo-formato/{id}', [WbPlantillaReporteController::class, 'getByTipoFormato']);
            });
            Route::prefix('users')->group(function () {
                Route::patch('/firma', [UsuarioController::class, 'actualizarFirma']);
            });
            Route::prefix('usu-planta')->group(function () {
                Route::get('/activos', [UsuPlantaController::class, 'getActivos']);
            });
            
            Route::prefix('material-lista')->controller(WbMaterialListaController::class)->group(function () {
                Route::get('/', 'get');
            });

            // seguridad en sitio
            Route::prefix('seguridad-sitio')->group(function () {
                Route::post('/', [WbSeguridadSitioController::class, 'post']);
                Route::get('/', [WbSeguridadSitioController::class, 'get']);
                Route::get('/turnos', [WbSeguridadSitioTurnoController::class, 'getMovil']);
                Route::post('/detalle', [WbSeguridadSitioController::class, 'getDetalle']);
                Route::post('/anular', [WbSeguridadSitioController::class, 'anularSolicitud']);
                Route::post('/finalizar', [WbSeguridadSitioController::class, 'finalizarSolicitud']);
                Route::post('/trasladar', [WbSeguridadSitioController::class, 'trasladoSolicitud']);
                Route::post('/filtro', [WbSeguridadSitioController::class, 'getFilters']);
                Route::post('/evidencias', [WbSeguridadSitioController::class, 'subirEvidencias']);
                Route::post('/evidencias/resume', [WbSeguridadSitioEvidenciaController::class, 'getEvidenciaPorSolicitud']);
                Route::post('/elementos', [WbSeguridadSitioController::class, 'actualizarElementos']);
                Route::post('/historial', [WbSeguridadSitioHistorialController::class, 'get']);
                Route::post('/historial/evidencias', [WbSeguridadSitioEvidenciaController::class, 'getEvidencias']);
                Route::post('/historial/elementos', [WbSeguridadSitioHistorialController::class, 'getElementos']);
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
        Route::middleware('isWeb')->group(function () {
            Route::prefix('informe-campo-has-hallazgo')->group(function () {
                Route::get('/informe/{id}', [WbInformeCampoHasHallazgoController::class, 'getByInformeHallazgo']);
            });
            Route::prefix('informe-campo')->group(function () {
                Route::get('/', [WbInformeCampoController::class, 'get']);
                Route::get('/getUser', [WbInformeCampoController::class, 'getUser']);
                Route::get('/{id}', [WbInformeCampoController::class, 'getById']);
                Route::put('/cerrar-hallazgos', [WbInformeCampoController::class, 'CerrarHallazgos']);
            });
            Route::prefix('ruta-nacional')->group(function () {
                Route::get('', [WbRutaNacionalController::class, 'get']);
            });
            Route::prefix('hallazgo')->group(function () {
                Route::get('/', [WbHallazgoController::class, 'get']);
            });
            Route::prefix('tipo-equipo')->group(function () {
                Route::get('/', [WbTipoEquipoController::class, 'get']);
            });
            Route::prefix('tipo-formato')->group(function () {
                Route::get('/', [WbTipoFormatoController::class, 'get']);
            });
            Route::prefix('plantilla-reporte')->group(function () {
                Route::post('/', [WbPlantillaReporteController::class, 'post'])->middleware('permiso:CREAR_PLANTILLA_REPORTE');
                Route::get('/tipo-formato/{id}', [WbPlantillaReporteController::class, 'getByTipoFormato']);
                Route::put('/{id}', [WbPlantillaReporteController::class, 'cambiarUrlYNombre'])->middleware('permiso:MODIFICAR_PLANTILLA_REPORTE');
                Route::patch('/bloquear/{id}', [WbPlantillaReporteController::class, 'bloquearPlantilla'])->middleware('permiso:BLOQUEAR_PLANTILLA_REPORTE');
                Route::patch('/desbloquear/{id}', [WbPlantillaReporteController::class, 'desBloquearPlantilla'])->middleware('permiso:DESBLOQUEAR_PLANTILLA_REPORTE');
            });
            Route::prefix('mso')->controller(SyncMsoController::class)->group(function () {
                Route::get('/viaje-bascula', 'msoParaViajeBascula');
            });
            Route::prefix('location')->controller(SyncLocationController::class)->group(function () {
                Route::get('/by-estado/{estado}', 'byEstado');
                Route::get('/by-estado/{estado}/{frente}', 'byEstadoJob');
            });
            Route::prefix('jobs')->controller(SyncJobsController::class)->group(function () {
                Route::get('/viaje-bascula/{estado}', 'jobsParaViajeBascula');
                Route::get('/viaje-bascula/{estado}/{frente}', 'byEstadoJob');
            });
            Route::prefix('solicitud-asfalto')->controller(SolicitudAsfaltoController::class)->group(function () {
                Route::put('/{id}', 'update');
                Route::get('/{id}', 'getById');
            });
            Route::prefix('item_tramsporte_panel')->controller(SyncItemsTransportPainelController::class)->group(function () {
                Route::get('/', 'get');
                Route::get('/movil', 'basculaMovil');
            });
            Route::prefix('configuraciones')->controller(WbConfiguracionesController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('empleado')->controller(SyncEmpleadoController::class)->group(function () {
                Route::get('/', 'get');
                Route::get('/viajeBascula/{cedula}', 'empleadosParaViajeBascula');
            });
            Route::prefix('htr-solicitud')->controller(HtrSolicitudController::class)->group(function () {
                Route::get('/', [HtrSolicitudController::class, 'get']);
            });
            Route::prefix('estructura')->controller(EstructurasController::class)->group(function () {
                Route::middleware('permiso:CREAR_UBICACIONES')->group(function () {
                    Route::post('/', [EstructurasController::class, 'post']);
                    Route::put('/{id}', [EstructurasController::class, 'update']);
                    Route::delete('/{id}', [EstructurasController::class, 'delete']);
                });
                Route::get('/sincronizarApp/{tramo}', [EstructurasController::class, 'getParaSincronizarApp']);
                Route::get('/tipo-estructura/{tipoEstructura}/{hito}', [EstructurasController::class, 'getByTipoEstructuraYAbcisasCercanas']);
                Route::get('/sala-tecnica/{proyecto}', [EstructurasController::class, 'getParaSalaTecnica']);
                Route::put('/finalizar/{id}', [EstructurasController::class, 'finalizarEstructura']);
                Route::get('/nomenclatura/{area}/{tramo}/{hito}/{tipoEstructura}', [EstructurasController::class, 'getNomenclaturasFinalizar']);
                Route::get('/', 'get');
            });
            Route::prefix('users')->controller(UsuarioController::class)->group(function () {
                Route::post('/', [UsuarioController::class, 'insert'])->middleware('permiso:CREAR_USUARIO');
                Route::put('/{id}', [UsuarioController::class, 'update'])->middleware('permiso:MODIFICAR_USUARIO');
                Route::delete('/{id}', [UsuarioController::class, 'delete'])->middleware('permiso:INHABILITAR_USUARIO');
                Route::put('/habilitar/{id}', [UsuarioController::class, 'habilitar'])->middleware('permiso:INHABILITAR_USUARIO');
                Route::get('/', [UsuarioController::class, 'get'])->middleware('permiso:VER_USUARIOS');
                Route::get('/mi-usuario', [UsuarioController::class, 'miUsuario']);
                Route::patch('/cambiar-estado/{user}', [UsuarioController::class, 'changueStatus'])->middleware('permiso:BLOQ_USUARIOS');
                Route::get('/Basculas', [UsuarioController::class, 'getUserBascula']);
            });
            Route::prefix('logs-table')->controller(LogAllTableController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('rol')->controller(WbSeguriRolesController::class)->group(function () {
                Route::get('/isAdmin', [WbSeguriRolesController::class, 'isAdmin']);
                Route::get('/', [WbSeguriRolesController::class, 'get']);
                Route::delete('/{rol}', [WbSeguriRolesController::class, 'delete']);
                Route::patch('/habilitar/{rol}', [WbSeguriRolesController::class, 'habilitar']);
                Route::post('/', [WbSeguriRolesController::class, 'post'])->middleware('permiso:CREAR_ROL', 'desencript');
            });
            Route::prefix('liberacion-actividades')->controller(LiberacionesActividadesController::class)->group(function () {
                Route::post('/', 'post');
            });
            Route::prefix('estructura-tipo-elemento')->controller(EstructuraTipoElementoController::class)->group(function () {
                Route::post('/', 'post');
                Route::get('/', 'get');
            });
            Route::prefix('solicitud-concreto')->controller(SolicitudConcretoController::class)->group(function () {
                Route::put('/{id}', [SolicitudConcretoController::class, 'update']);
                Route::put('/borrador/{id}', [SolicitudConcretoController::class, 'borrador']);
                Route::get('/solicitudes_realizadas', [SolicitudConcretoController::class, 'numeroSolicitudesConcretoRealizados']);
                Route::get('/', 'get');
                Route::get('/pendientes-y-enviadas', [SolicitudConcretoController::class, 'getPendientesOEnviados']);
                Route::put('/cerrar/{id}', [SolicitudConcretoController::class, 'cerrarSolicitud'])->middleware('permiso:VER_SOLI_CONCRETO', 'desencript');
                Route::put('/anularViaje/{id}', [SolicitudConcretoController::class, 'anularViajes']);
            });
            Route::prefix('htr-usuarios')->controller(HtrUsuariosController::class)->group(function () {
                Route::get('/', [HtrUsuariosController::class, 'get']);
            });
            Route::prefix('sync-bascula')->controller(SyncBasculasController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('sync-registro')->controller(SyncRegistroController::class)->group(function () {
                Route::get('/agrupados', 'getAgrupados');
                Route::get('/mirarBascula', 'mirarBascula');
                Route::get('/filtro', 'registroSegunFiltro');
                Route::delete('/{id}', 'delete');
            });
            Route::prefix('estruct-firma')->controller(WbEstructFirmasController::class)->group(function () {
                Route::post('/', 'post');
                Route::get('/', 'get');
            });
            Route::prefix('tipo-mezcla')->controller(TipoMezclaController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('material-tipo')->controller(WbMaterialTiposController::class)->group(function () {
                Route::get('/', 'get');
                Route::post('/', 'post');
                Route::put('/{id}', 'update');
            });
            Route::prefix('tipo-capa')->controller(WbTipoCapaController::class)->group(function () {
                Route::get('/', 'get');
                Route::post('/', 'post');
                Route::put('/{id}', 'update');
            });

            Route::prefix('material-capa')->controller(WbMaterialCapaController::class)->group(function () {
                Route::get('/material-lista/{id}', [WbMaterialCapaController::class, 'getByMaterialLista']);
                Route::post('/', [WbMaterialCapaController::class, 'post']);
                Route::post('/masivo', [WbMaterialCapaController::class, 'postMasivo']);
                Route::put('/{id}', [WbMaterialCapaController::class, 'update']);
                Route::get('/', [WbMaterialCapaController::class, 'get']);
                Route::get('/por-capa-disponible/{tipoDeCapa}', [WbMaterialCapaController::class, 'materialesPorCapaDisponible']);
            });
            Route::prefix('material-centro-produccion')->controller(WbMaterialCentroProduccionController::class)->group(function () {
                Route::get('/material-lista/{id}', 'getByMaterialLista');
                Route::get('/', 'get');
                Route::get('/formula-lista/{id}', 'getConMaterialListaYUsuPlanta');
                Route::put('/{id}', 'update');
                Route::post('/', 'post');
                Route::post('/masivo', 'postMasiva');
            });
            Route::prefix('centro-produccion-hitos')->controller(WbCentroProduccionHitosController::class)->group(function () {
                Route::get('/planta/{id}', 'getByPlanta');
                Route::put('/{id}', 'update');
                Route::post('/', 'post');
            });
            Route::prefix('area')->controller(AreaController::class)->group(function () {
                Route::post('/', [AreaController::class, 'post']);
                Route::delete('/{id}', [AreaController::class, 'delete']);
                Route::get('/', [AreaController::class, 'get']);
                Route::patch('/bloquear/{area}', [AreaController::class, 'bloquear']);
                Route::patch('/desbloquear/{area}', [AreaController::class, 'desbloquear']);
            });
            Route::prefix('asignacion')->group(function () {
                Route::get('/area/{id}', [AsignacionController::class, 'getByArea']);
                Route::post('/', [AsignacionController::class, 'post']);
                Route::get('/', [AsignacionController::class, 'get']);
            });
            Route::prefix('sync-acciones')->controller(SyncAccionesController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('sync-config')->controller(SyncConfigController::class)->group(function () {
                Route::get('/', 'get');
                Route::get('/{user}', 'getByUser');
            });
            Route::prefix('actividad')->controller(ActividadController::class)->group(function () {
                Route::get('/', [ActividadController::class, 'get']);
                Route::post('/', [ActividadController::class, 'post']);
            });
            Route::prefix('compania')->controller(CompaniaController::class)->group(function () {
                Route::get('/', [CompaniaController::class, 'get']);
                /* Route::get('/{proyecto}', 'getPorProyecto'); */
                Route::post('/', [CompaniaController::class, 'post']);
                Route::put('/{id}', [CompaniaController::class, 'update']);
                Route::get('/all', [CompaniaController::class, 'getAll'])->middleware('permiso:VER_PROYECTOS');
            });
            Route::prefix('usu-planta')->controller(UsuPlantaController::class)->group(function () {
                Route::post('/', [UsuPlantaController::class, 'post']);
                Route::get('', [UsuPlantaController::class, 'get']);
                Route::get('/activos/{compania}', [UsuPlantaController::class, 'getByCompania']);
            });
            Route::prefix('asfalt-formula-asign')->controller(WbAsfaltAsignController::class)->group(function () {
                Route::post('/', 'post');
                Route::get('/', 'get');
                Route::patch('/activar/{id}', 'activar');
                Route::patch('/desactivar/{id}', 'desActivar');
            });
            Route::prefix('estruc-perfil-firmas')->controller(WbEstructPerfilFirmaController::class)->group(function () {
                Route::get('/activos', 'getActivos');
                Route::post('/', 'post');
            });
            Route::prefix('estruc-criterio')->controller(WbEstrucCriteriosController::class)->group(function () {
                Route::post('/', 'post');
                Route::get('/', 'get');
                Route::put('/{id}', 'update');
            });
            Route::prefix('estruc-config-asign')->controller(WbEstrucConfigAsignController::class)->group(function () {
                Route::get('/', 'get');
                Route::post('/', 'post');
                Route::get('/{id}', 'getById');
                Route::delete('/{id}', 'delete');
            });
            Route::prefix('estruc-config')->controller(WbEstrucConfigController::class)->group(function () {
                Route::get('/activos', 'getActivos');
                Route::post('/', 'post');
            });
            Route::prefix('formula-lista')->controller(WbFormulaListaController::class)->group(function () {
                Route::post('/', 'post');
                Route::put('/{id}', 'update');
                Route::get('/', 'get');
                Route::patch('/', 'get');
                Route::patch('/inhabilitar/{id}', 'inHabilitar');
                Route::patch('/habilitar/{id}', 'habilitar');
            });
            Route::prefix('material-formula')->controller(WbMaterialFormulaController::class)->group(function () {
                Route::post('/', 'post');
                Route::put('/{id}', 'update');
                Route::PATCH('/estado/{id}', 'cambiarEstado');
                Route::get('/codigo-formula-cdp/{id}', 'getPorFormulaCdp');
            });
            Route::prefix('formula-capa')->controller(WbFormulaCapaController::class)->group(function () {
                Route::get('/', [WbFormulaCapaController::class, 'get']);
                Route::patch('/habilitar/{id}', [WbFormulaCapaController::class, 'habilitar']);
                Route::patch('/inhabilitar/{id}', [WbFormulaCapaController::class, 'inhabilitar']);
                Route::post('/', [WbFormulaCapaController::class, 'post']);
                Route::put('/{id}', [WbFormulaCapaController::class, 'update']);
                Route::get('/formula-lista/{id}', [WbFormulaCapaController::class, 'getConTipoCapaPorFormulaLista']);
                Route::get('/por-tipo-capa/{tipoCapa}', [WbFormulaCapaController::class, 'getPorTipoCapa']);
            });
            Route::prefix('formula-centro-produccion')->controller(WbFormulaCentroProduccionController::class)->group(function () {
                Route::get('/', 'get');
                Route::patch('/habilitar/{id}', 'habilitar');
                Route::patch('/inhabilitar/{id}', 'inhabilitar');
                Route::post('/', 'post');
                Route::get('/formula-lista/{id}', 'getPorFormulaLista');
            });
            Route::prefix('hito')->controller(WbHitosController::class)->group(function () {
                Route::post('/', 'post');
                Route::put('/{id}', 'update');
                Route::get('/', 'get');
                Route::get('/sin-asignacion', 'getSinAsignacionAnterior');
                Route::get('/tramo/{id}', 'getByTramosEncrypt'); // consultar activos
                Route::get('/', 'get'); // consultar activos
            });
            Route::prefix('hito-abcisa')->controller(WbHitosAbcisasController::class)->group(function () {
                Route::post('/', [WbHitosAbcisasController::class, 'post']);
                Route::put('/{id}', [WbHitosAbcisasController::class, 'update']);
                Route::get('/hito/{id}', [WbHitosAbcisasController::class, 'getByHito']);
                Route::get('/items', [WbHitosAbcisasController::class, 'getParaSync']);
            });
            Route::prefix('enviar-correo')->controller(EnviarCorreo::class)->group(function () {
                Route::post('/material', 'material');
            });
            Route::prefix('tramo-hito-asign')->controller(WbTramosHitosAsignController::class)->group(function () {
                Route::get('/', 'get');
                Route::get('/tramo/{id}', 'getByTramo');
                Route::put('/{id}', 'update');
                Route::post('/asignacionMasiva', 'asignacionMasiva');
                Route::post('/', 'post');
                Route::put('/por-tramo-y-hito/{tramo}/{hito}', 'updatePorTramoYHito');
            });
            Route::prefix('tramo')->controller(WbTramosController::class)->group(function () {
                Route::get('/', [WbTramosController::class, 'get']);
                Route::get('/para_sync', [WbTramosController::class, 'getParaSync']);
                Route::put('/{id}', [WbTramosController::class, 'update']);
                Route::post('/', [WbTramosController::class, 'post']);
                Route::get('/{id}', [WbTramosController::class, 'getById']);
            });
            Route::prefix('cnf-cost-control')->controller(CnfCostCenterController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('formula')->controller(FormulaController::class)->group(function () {
                Route::post('/', 'post');
                Route::get('/', 'get');
                Route::patch('/habilitar/{id}', 'habilitar');
                Route::patch('/inHabilitar/{id}', 'inhabilitar');
                Route::delete('/{id}', 'delete');
            });
            Route::prefix('estruc-formula')->controller(EstrucFormulaController::class)->group(function () {
                Route::post('/', [EstrucFormulaController::class, 'post']);
                Route::get('/', [EstrucFormulaController::class, 'get']);
                Route::delete('/{id}', [EstrucFormulaController::class, 'deletePr']);
            });
            Route::prefix('asfalt-formula')->controller(WbAsfaltFormulaController::class)->group(function () {
                Route::get('/', 'get');
                Route::post('/', 'post');
            });
            Route::prefix('solicitud-material')->controller(WbSolicitudMaterialesController::class)->group(function () {
                Route::put('/{id}', [WbSolicitudMaterialesController::class, 'update']);
                Route::get('/suliberador', [WbSolicitudMaterialesController::class, 'get']);
                Route::put('/re-asignar/{id}', [WbSolicitudMaterialesController::class, 'reAsignar']);
                Route::put('/aprovar/{id}', [WbSolicitudMaterialesController::class, 'aprovar']);
                Route::put('/rechazar/{id}', [WbSolicitudMaterialesController::class, 'rechazar']);
                Route::get('/', [WbSolicitudMaterialesController::class, 'getSolicitudMateriales']);
            });
            Route::prefix('pemiso-de-rol')->controller(WbSeguriRolesPermisoController::class)->group(function () {
                Route::delete('/{id}', 'delete');
            });
            Route::prefix('permiso')->controller(Wb_PermisosController::class)->group(function () {
                Route::delete('/{id}', [Wb_PermisosController::class, 'destroy']);
                Route::get('/rol/{id}', [Wb_PermisosController::class, 'show']);
                Route::post('/asignar', [Wb_PermisosController::class, 'assign']);
                Route::post('/', [Wb_PermisosController::class, 'store']);
                Route::get('/', [Wb_PermisosController::class, 'get']);
                Route::get('/byRol/{permiso}', [Wb_PermisosController::class, 'getByRol']);
                Route::get('/permisoPorRol/{rol}', [Wb_PermisosController::class, 'getPermisosPorRol']);
                Route::get('/permisoMenuWeb/', [Wb_PermisosController::class, 'permisosMenuWeb']);
                Route::get('/permisosSolicitudAsfalto/', [Wb_PermisosController::class, 'permisosSolicitudAsfalto']);
                Route::get('/permisosSolicitudConcreto/', [Wb_PermisosController::class, 'permisosSolicitudConcreto']);
                Route::get('/permisosCrubReporte/', [Wb_PermisosController::class, 'permisosCrubReporte']);
                Route::get('/permisoCubEmpleado/', [Wb_PermisosController::class, 'permisosSyncEmpleado']);
                Route::get('/estoyAutorizado/{permiso}', [Wb_PermisosController::class, 'estoyAutorizado']);
                Route::get('/permisosGestionProyecto', [Wb_PermisosController::class, 'permisosGestionProyecto']);
                Route::get('/permisosGestionProyecto', [Wb_PermisosController::class, 'permisosGestionProyecto']);
                Route::get('/permisosGestionCompania', [Wb_PermisosController::class, 'permisosGestionCompania']);
                Route::get('/paraUsuario/', [Wb_PermisosController::class, 'permisosParaUsuario']);
                Route::get('/paraEquipo/', [Wb_PermisosController::class, 'permisosEquipos']);
                Route::get('/permisosHallazgo/', [Wb_PermisosController::class, 'permisosHallazgo']);
                Route::get('/permisosRutaNacional/', [Wb_PermisosController::class, 'permisosRutaNacional']);
                Route::get('/permisosInformeHallazgo/', [Wb_PermisosController::class, 'permisosInformeHallazgo']);
                Route::delete('/retirar/{permiso}', [Wb_PermisosController::class, 'unassign']);
            });
            Route::prefix('planilla-control-asfalto')->controller(PlanillaControlAsfaltoController::class)->group(function () {
                Route::post('', 'post');
                Route::get('/byUsuario', 'getByUsuario');
                Route::get('/bySolicitud/{solicitud}', 'getBySolcitud');
                Route::get('/byPlanta', 'getByPlanta');
                Route::put('/anularViaje/{id}', [PlanillaControlAsfaltoController::class, 'anularViajes']);
            });
            Route::prefix('planta')->controller(PlantaController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('planilla-control-concreto')->controller(PlanillaControlConcretoController::class)->group(function () {
                Route::post('/', [PlanillaControlConcretoController::class, 'post']);
                Route::get('/', [PlanillaControlConcretoController::class, 'get']);
                Route::get('/cdc', [PlanillaControlConcretoController::class, 'listarCdc']);
                Route::get('/bySolicitud/{solicitud}', [PlanillaControlConcretoController::class, 'getBySolicitud']);
                Route::get('/{proyecto}', [PlanillaControlConcretoController::class, 'getPorProyecto']);
                Route::put('/anularViaje/{id}', [PlanillaControlConcretoController::class, 'anularViajes']);
            });
            Route::prefix('liberacion')->controller(LiberacionesController::class)->group(function () {
                Route::put('/{id}', 'firmarProduccion');
                Route::get('/', 'get');
                Route::get('/numero_solicitudes_liberadas', 'numeroSolicitudesLiberadas');
            });
            Route::prefix('tipo-estructura')->controller(EstrucTiposController::class)->group(function () {
                Route::get('/', 'get');
                Route::get('/hito/{hito}', 'getByHito');
            });
            Route::prefix('material-presupuestado')->controller(WbMaterialPresupuestadoController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('tipo-mezcla')->controller(TipoMezclaController::class)->group(function () {
                Route::post('/', [TipoMezclaController::class, 'post']);
                Route::get('/para_sync', [TipoMezclaController::class, 'getParaSync']);
            });
            Route::prefix('estados')->controller(EstadoController::class)->group(function () {
                Route::get('/sin-estructura', 'get');
                Route::get('/estructura', 'getParaEstructura');
            });
            Route::prefix('accion-estructura')->controller(WbAccionEstructuraController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('tipo-de-adaptacion')->controller(WbTipoDeAdaptacionController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('tipo-de-obra')->controller(WbTipoDeObraController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('proyecto')->controller(ProjectCompanyController::class)->group(function () {
                Route::get('/byUsuario/{id}', [ProjectCompanyController::class, 'getById']);
                Route::get('/miUsuario', [ProjectCompanyController::class, 'getByUsuario']);
                Route::get('/proyecto-para-asignar/compania', [ProjectCompanyController::class, 'getProyectoPorusuarioYpermisoAsignarCompania'])->middleware('permiso:ASIGNAR_USUARIO_PROYECTO');
                Route::get('/proyecto-para-asignar/usuario', [ProjectCompanyController::class, 'getProyectoPorusuarioYpermisoAsignarUsuario'])->middleware('permiso:ASIGNAR_USUARIO_PROYECTO');
                Route::post('/', [ProjectCompanyController::class, 'post'])->middleware('permiso:CREAR_PROYECTO');
                Route::put('/{id}', [ProjectCompanyController::class, 'update'])->middleware('permiso:MODIFICAR_PROYECTO');
                Route::patch('/habilitar/{id}', [ProjectCompanyController::class, 'habilitar'])->middleware('permiso:DESBLOQUEAR_PROYECTO');
                Route::patch('/desHabilitar/{id}', [ProjectCompanyController::class, 'desHabilitar'])->middleware('permiso:BLOQUEAR_PROYECTO');
            });
            Route::prefix('pais')->controller(WbPaisController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('tema-interfaz')->controller(WbTemaInterfazController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('licencia-ambiental')->controller(WbLicenciaAmbientalController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('tipo-calzada')->group(function () {
                Route::get('/', [WbTipoCalzadaController::class, 'get']);
                Route::get('/activos', [WbTipoCalzadaController::class, 'getActivos']);
                Route::post('/', [WbTipoCalzadaController::class, 'post']);
                Route::post('/editar', [WbTipoCalzadaController::class, 'editar']);
                Route::post('/cambio-estado', [WbTipoCalzadaController::class, 'cambiarEstado']);
            });
            Route::prefix('tipo-via')->controller(WbTipoViaController::class)->group(function () {
                Route::get('/activos', 'getActivos');
            });
            Route::prefix('equipos')->group(function () {
                Route::get('/listar', [WbEquipoControlles::class, 'equiposActivos']);
                Route::get('/get', [WbEquipoControlles::class, 'equiposParaViajeBascula']);
                Route::get('/get/{buscar}', [WbEquipoControlles::class, 'equiposParaViajeBascula']);
                Route::get('/validar/{id}', [WbEquipoControlles::class, 'validarEquimentId']);
                Route::get('/viaje-bascula/{id}', [WbEquipoControlles::class, 'equiposParaViajeBascula']);
                Route::get('/paraViajeBascula/{id}', [WbEquipoControlles::class, 'equiposParaViajeBascula']);
                Route::put('/updateEquipo', [WbEquipoControlles::class, 'updateEquipo']);
                // Route::get('/', [WbEquipoControlles::class, 'get']);
            });
            Route::prefix('sync-cost-code')->controller(SyncCostDescController::class)->group(function () {
                Route::get('/frente/{frente}', 'getByActivoFrente');
            });
            // pasar a sync cost code
            Route::prefix('cost-code')->controller(CostCodeController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::get('refresh', [AuthController::class, 'refresh']);
            Route::get('/usu/{id}', [UsuarioController::class, 'Restablecer']);

            Route::prefix('basculas')->group(function () {
                Route::prefix('permisos')->controller(Wb_PermisosController::class)->group(function () {
                    Route::get('/usuario/{user}', 'Basculasbyuser');
                    Route::get('/usuarios', 'Basculas');
                });
            });
            Route::prefix('usuario-proyecto')->controller(WbUsuarioProyectoController::class)->group(function () {
                Route::get('/usuario/{usuario}', 'getByUsuario');
                Route::post('/', 'post')->middleware('permiso:ASIGNAR_USUARIO_PROYECTO');
                Route::delete('/{usuario}/{proyecto}', 'desAsignar')->middleware('permiso:DESASIGNAR_USUARIO_PROYECTO');
            });
            // listar solicitudes
            Route::get('/Asfalto/Listar', [AsfaltoController::class, 'index']);
            // crear nuevo equipo
            Route::post('/equipos/crear', [WbEquipoControlles::class, 'post']);
            Route::get('/equipos/verEquipos', [WbEquipoControlles::class, 'verEquipos']);
            Route::prefix('compania')->controller(CompaniaController::class)->group(function () {
                Route::get('/', 'get');
                Route::post('/', 'post');
            });
            Route::prefix('tipo-paso-de-fauna')->controller(WbTipoDePasoDeFaunaController::class)->group(function () {
                Route::get('/', 'get');
            });
            Route::prefix('solicitud-asfalto')->controller(SolicitudAsfaltoController::class)->group(function () {
                Route::put('/{id}', 'update');
            });
            Route::prefix('tipo-de-obra')->controller(WbTipoDeObraController::class)->group(function () {
                Route::get('/', 'get');
                Route::get('/{proyecto}', 'getPorProyecto');
            });
            Route::prefix('seguridad-sitio')->controller(WbSeguridadSitioController::class)->group(function () {
                Route::get('/list', 'getWeb');
                Route::post('/gestion/aprobar', 'calificarAprobado');
                Route::post('/gestion/rechazar', 'calificarRechazado');
                Route::post('/gestion/anular', 'gestionAnularSolicitud');
                Route::get('/gestion/report', 'report');
                Route::post('/gestion/comentar', 'comentario');
            });
            Route::prefix('seguridad-sitio')->controller(WbSeguridadSitioEvidenciaController::class)->group(function () {
                Route::post('/list/evidencia', 'getUltimaEvidenciaActivaPorSolicitud');
                Route::post('/gestion/historial/evidencia', 'getEvidencias');
            });
            Route::prefix('seguridad-sitio')->controller(WbSeguridadSitioHistorialController::class)->group(function () {
                Route::post('/gestion/historial', 'getHistorialWeb');
            });
            Route::prefix('seguridad-sitio/turnos')->controller(WbSeguridadSitioTurnoController::class)->group(function () {
                Route::get('', 'get');
                Route::post('', 'post');
                Route::post('/actualizar', 'actualizar');
                Route::post('/eliminar', 'eliminar');
            });
           
        });
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
    Route::prefix('area-white')->controller(AreaController::class)->group(function () {
        Route::get('/', 'get');
        Route::get('/{proyecto}', 'getPorProyectoParaRegistro');
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
    // Route::prefix('solicitud-concreto')->controller(SolicitudConcretoController::class)->group(function () {
    //     Route::put('/{id}', [SolicitudConcretoController::class, 'update']);
    //     Route::put('/borrador/{id}', [SolicitudConcretoController::class, 'borrador']);
    //     Route::get('/solicitudes_realizadas', [SolicitudConcretoController::class, 'numeroSolicitudesConcretoRealizados']);
    //     Route::get('/', 'get');
    //     Route::get('/pendientes-y-enviadas', [SolicitudConcretoController::class, 'getPendientesOEnviados']);
    //     Route::put('/cerrar/{id}', [SolicitudConcretoController::class, 'cerrarSolicitud'])->middleware('permiso:VER_SOLI_CONCRETO', 'desencript');
    //     Route::put('/anularViaje/{id}', [SolicitudConcretoController::class, 'anularViajes']);
    // });

    // Route::prefix('planilla-control-concreto')->controller(PlanillaControlConcretoController::class)->group(function () {
    //     Route::post('/', [PlanillaControlConcretoController::class, 'post']);
    //     Route::get('/', [PlanillaControlConcretoController::class, 'get']);
    //     Route::get('/cdc', [PlanillaControlConcretoController::class, 'listarCdc']);
    //     Route::get('/bySolicitud/{solicitud}', [PlanillaControlConcretoController::class, 'getBySolicitud']);
    //     Route::get('/{proyecto}', [PlanillaControlConcretoController::class, 'getPorProyecto']);
    //     Route::put('/anularViaje/{id}', [PlanillaControlConcretoController::class, 'anularViajes']);
    // });
    /*Route::prefix('informe-campo2')->group(function () {
            Route::get('/', [WbInformeCampoController::class, 'get']);
            Route::post('/', [WbInformeCampoController::class, 'post'])->middleware('permiso:CREAR_INFORME_DE_CAMPO');
            Route::post('/masivo', [WbInformeCampoController::class, 'postMasivo'])->middleware('permiso:CREAR_INFORME_DE_CAMPO');
            Route::patch('/estado/{id}/{estado}', [WbInformeCampoController::class, 'cambiarEstado']);
            Route::put('/cerrar-hallazgos', [WbInformeCampoController::class, 'CerrarHallazgos']);
            Route::get('/getUser', [WbInformeCampoController::class, 'getUser']);
        });



        Route::prefix('tipo-equipo2')->group(function () {
            Route::get('/', [WbTipoEquipoController::class, 'get']);
        });*/

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
    // PREOPERACIONALES
    Route::post('/preoperacional', [preoperacional_C::class, 'guardarDeprecated']);

    // DESCARGA DE REPORTES
    // EXCEL
    Route::get('/excel2/{report}', [generatePDF_C::class, 'InformeExcel2']);
    // PDF
    Route::get('/PDF/{report}', [generatePDF_C::class, 'InformePdf2']);
    // agrergar apermisos necesario

    // ENVIO DE SMS
    Route::get('/notificacion/sms', [NotificacionSMS::class, 'SMS']);
    Route::middleware(['throttle:api'])->group(function () {
        Route::post('/preoperacional/actividades', [preoperacional_actividad_C::class, 'guardarDeprecated']);
    });
    // EQUIPOS
    // consultar equipos
    Route::get('/equipos/{n}/equipo/{id}', [EquipementsController::class, 'ListarEquipos']);
    Route::get('/equipos/{n}/equipo', [EquipementsController::class, 'ListarEquipos']);

    // cambiar estado de equipo
    Route::get('/equipos/{id}/estado', [WbEquipoControlles::class, 'CambiarEstado']);

    // CONTRATISTAS
    // listar contratistas activos
    Route::get('/contratistas/activos', [ContratistaController::class, 'ContratistasActivosAPI']);

    // ASFALTO

    // CONSULTAR ACTIVIDADES
    Route::get('/actividades/calificar/{id}', [Wb_solicitud_liberaciones_act_controller::class, 'getActividadDeprecated']);
    Route::post('/actividades/calificar', [Wb_solicitud_liberaciones_act_controller::class, 'CambiarEstadoDeprecated']);
    // FIRMAR ACTIVIDAD
    Route::post('/actividades/firmar', [WbSolicitudLiberacionesFirmasController::class, 'FirmarDeprecated']);
    Route::get('/generate/pdf/{sol}', [generatePDF_C::class, 'informe']);
    Route::get('/generate/pdf2/{sol}', [generatePDF_C::class, 'informe2']);
    // CONSULTAR RESPONSABLE
    Route::get('/actividades/responsable/{id}', [Wb_Liberaciones_Reponsable::class, 'getResponsableDeprecated']);

    // permisos
    // consultar todos los permisos, probar
    /* Route::get('/permisos', [Wb_PermisosController::class,'index']); */
    // elimina un permiso, probar
    /* Route::delete('/permisos/{id}', [Wb_PermisosController::class,'destroy']); */
    // consulta los permisos asignados a un rol
    Route::get('/permisos/{rol}', [Wb_PermisosController::class, 'show']);
    // asigna un permiso a un rol

    // api para descargar la configuracion de los indicadores
    Route::get('/indicadores', [SyncIndicadorController::class, 'get']);
    Route::get('/equipos', [WbEquipoControlles::class, 'equiposActivosDeprecated']);
    Route::get('/para_sync', [EstrucFormulaController::class, 'getParaSync']);

    Route::prefix('equipos-dev')->group(function () {
        Route::get('/', [WbEquipoControlles::class, 'equiposActivos']);
    });
});
/*
 * End rutas huerfanas
 */
