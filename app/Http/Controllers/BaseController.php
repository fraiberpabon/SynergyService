<?php

namespace App\Http\Controllers;

use App\Http\trait\AdministradorTrait;
use App\Http\trait\CompaniaInProyectoTrait;
use App\Http\trait\CompaniaTrait;
use App\Http\trait\DateHelpersTrait;
use App\Http\trait\EstadoModelTrait;
use App\Http\trait\FiltroTrait;
use App\Http\trait\IpTrait;
use App\Http\trait\MisProyectosTrait;
use App\Http\trait\PasswordTrait;
use App\Http\trait\PermisoModelTrait;
use App\Http\trait\ProyectoTrait;
use App\Http\trait\Resource;
use App\Http\trait\RolTrait;
use App\Http\trait\SetProyectoYCompania;
use App\Http\trait\TipoClavePasswordTrait;
use App\Http\trait\TokenHelpersTrait;
use App\Http\trait\UsuarioTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Psy\Util\Json;

class BaseController extends Controller
{
    use
        Resource,
        EstadoModelTrait,
        PermisoModelTrait,
        DateHelpersTrait,
        TokenHelpersTrait,
        RolTrait,
        AdministradorTrait,
        TipoClavePasswordTrait,
        MisProyectosTrait,
        CompaniaInProyectoTrait,
        PasswordTrait,
        ProyectoTrait,
        CompaniaTrait,
        FiltroTrait,
        SetProyectoYCompania,
        IpTrait,
        UsuarioTrait;

    /**
     * Errores al autenticar
     */
    var $usuarioSinImeiError = 'AUTCH01';
    var $usuarioImeiIncorrectoError = 'AUTCH02';
    var $usuarioContrasenaNoValidaError = 'AUTCH03';
    var $usuarioNoExisteError = 'AUTCH04';
    var $usuarioBloqueadoError = 'AUTCH05';
    var $usuarioDeshabilitadoError = 'AUTCH06';
    var $usuarioSinProyectoError = 'AUTCH07';
    var $proyectoNoEncontradoError = 'AUTCH08';
    var $proyectoloqueadoError = 'AUTCH10';
    /**
     * Errores de formula asfalto
     */
    var $formulaAsfaltoNoEncontradaError = 'FORMAS01';
    /**
     * Errores de solicitud asfalto
     */
    var $cantidadSolicitudAsfaltoSolicitadaMenorOIgualA0Error = 'SOLASF01';
    /**
     * Errores de hitos
     */
    var $hitoNoEncontradoError = 'HI01';
    /**
     * Errores de tramo
     */
    var $tramoNoEncontradoError = 'HI01';
    /**
     * Errores de htrusuario
     */
    var $accionNovalidaInicioSessionError = 'ACCIONH01';
    var $accionNoRegistradaInicioSessionError = 'ACCIONH02';
    /**
     * Errores de usuario
     */

    var $userContrasenaAnteriorYNuevaIgualesError = 'USER01';
    var $usuarioPorImeilNoEncontradoError = 'USER10';
    /**
     * Error cuando el endpoint necesita un campo de la solicitud y no la encuentra
     */
    var $faltanParametrosCabeceraError = 'HEADER07';
    /**
     * Error cuando el endpoint necesita un campo de la solicitud y no la encuentra
     */
    var $turnoPreoperacionalNoValidoError = 'PREO01';

    //encripta la data y envia el estado de que esta consulta ha sido encriptado
    public function handleResponse(Request $req, $result, $msg, $paginas = 1): JsonResponse
    {
        $res = [
            'success' => true,
            'data'    => $result,
            'mensaje' => $msg,
            'encript' => false,
            'paginas' => floor($paginas),
            'cod' => 0
        ];
        return response()->json($res);
    }

    public function handleAlert($msg,$success = false): JsonResponse
    {
        //$this->eliminarPasswordTrait($this->getPasswordActualTrait(Request::capture()));
        $res = [
            'success' => $success,
            'mensaje' => $msg,
            'encript' => false,
            'cod' => 0
        ];
        return response()->json($res);
    }

    public function handleSolicitudNoPuedeSerProcesada(): JsonResponse
    {
        //$this->eliminarPasswordTrait($this->getPasswordActualTrait(Request::capture()));
        $res = [
            'success' => false,
            'mensaje' => __('messages.no_se_puede_procesar_la_solicitud'),
            'encript' => false,
            'cod' => 0
        ];
        return response()->json($res);
    }

    public function handleCod($msg,$cod = 0): JsonResponse
    {
        //$this->eliminarPasswordTrait($this->getPasswordActualTrait(Request::capture()));
        $res = [
            'success' => false,
            'mensaje' => $msg,
            'encript' => false,
            'cod' => $cod
        ];
        return response()->json($res);
    }

    public function handleError($error, $errorMsg = [], $code = 404): JsonResponse
    {
        //$this->eliminarPasswordTrait($this->getPasswordActualTrait(Request::capture()));
        $res = [
            'success' => false,
            'mensaje' => $error,
            'encript' => false,
            'cod' => 0
        ];
        if(!empty($errorMsg)){
            $res['data'] = $errorMsg;
        }
        return response()->json($res, $code);
    }
}
