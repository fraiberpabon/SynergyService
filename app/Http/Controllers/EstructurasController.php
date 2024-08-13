<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\estado;
use App\Models\estruc_tipos;
use App\Models\estructuras;
use App\Models\usuarios_M;
use App\Models\WbAccionEstructura;
use App\Models\WbHitos;
use App\Models\WbLicenciaAmbiental;
use App\Models\WbMaterialPresupuestado;
use App\Models\WbTipoCalzada;
use App\Models\WbTipoDeAdaptacion;
use App\Models\WbTipoDeObra;
use App\Models\WbTipoVia;
use App\Models\WbTramos;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class EstructurasController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('tramo')) {
            return $this->handleAlert('Falta el campo tramo');
        }
        if(!$req->json()->has('calzada')) {
            return $this->handleAlert('Falta el campo calzada');
        }
        if(!$req->json()->has('actividad')) {
            return $this->handleAlert('Falta el campo actividad');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta el campo estado');
        }
        if(!$req->json()->has('hito')) {
            return $this->handleAlert('Falta el campo hito');
        }
        $validator = Validator::make($req->all(),[
            'obra'=> 'string|nullable|max:50',
            'tipoCalzada'=> 'numeric',
            'nomenclatura'=> 'string|max:50',
            'abscisa'=> 'max:10',
            'diametro'=> 'numeric|max:50',
            'celdas'=> 'numeric',
            'localizacion'=> 'max:50',
            'baseM'=> 'numeric|max:999',
            'longitud'=> 'numeric|nullable|max:999',
            'longitudTotal'=> 'numeric|nullable|max:999',
            'observaciones'=> 'string|max:250|nullable',
            'descripcion'=> 'string|max:250|nullable',
            'documentoModificacion'=> 'string|nullable|max:60',
            'coordenadaEste'=> 'numeric|numeric',
            'coordenadaNorte'=> 'numeric|numeric',
            'accionAmbiental'=> 'nullable|string|max:100',
            'statusAccionAmbiental'=> 'string|nullable|max:100',
            'tipoDePasoDeFauna'=> 'numeric|max:10',
            'obraAdyacente'=> 'string|nullable|max:50',
            'licenciaAmbiental'=> 'numeric',
            'tipoAdaptacion'=> 'nullable|numeric',
            'accionEstructura'=> 'numeric',
            'materialPresupuestado'=> 'numeric',
            'estado'=> 'required|numeric',
            'fechaTerminar'=> 'required',
            'tipoEstructura'=> 'numeric',
            'tipoObra'=> 'string|nullable|max:2',
        ]);
        if($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }
        try{
            if(WbTramos::where('Id_Tramo', $req->tramo)->first() == null) {
                return $this->handleAlert('Tramo no encontrado.');
            }
            if(WbHitos::where('Id_Hitos', $req->hito)->first() == null) {
                return $this->handleAlert('Hito no encotrado.');
            }
            $estadoGet = estado::find($req->estado);
            if($estadoGet == null) {
                return $this->handleAlert('Estado no encontrada.');
            }
            $tipoEstructura = estruc_tipos::find($req->tipoEstructura);
            if($req->tipoEstructura != 0 && $tipoEstructura == null) {
                return $this->handleAlert('Tipo de estructura no encontrada.');
            }
            if($req->licenciaAmbiental != 0 && WbLicenciaAmbiental::find($req->licenciaAmbiental) == null) {
                return $this->handleAlert('Licencia ambiental no encotrada.');
            }
            if($req->tipoAdaptacion != 0 && WbTipoDeAdaptacion::find($req->tipoAdaptacion) == null) {
                return $this->handleAlert('Tipo de adaptacion no encotrada.');
            }
            if($req->accionEstructura != 0 && WbAccionEstructura::find($req->accionEstructura) == null) {
                return $this->handleAlert('Accion estructura no encontrada.');
            }
            if($req->materialPresupuestado != 0 && WbMaterialPresupuestado::find($req->materialPresupuestado) == null) {
                return $this->handleAlert('Material presupuestado no encontrada.');
            }
            if(WbTipoCalzada::find($req->tipoCalzada) == null) {
                return $this->handleAlert('Calzada no encontrada.');
            }
            if(WbTipoDeObra::find($req->tipoObra) == null) {
                return $this->handleAlert('Tipo de obra no encontrada.');
            }
            $estructura = new estructuras;
            $estructura->TRAMO = $req->tramo;
            $estructura->HITO_OTRO_SI_10 = $req->hito;
            $estructura->OBRA = $req->obra;
            $estructura->NOMENCLATURA = $req->nomenclatura;
            $estructura->ABSCISA = $req->abscisa;
            $estructura->DIAMETRO = $req->diametro;
            $estructura->CELDAS = $req->celdas;
            $estructura->WBS = $req->wbs;
            $estructura->actividad = $req->actividad;
            $estructura->localizacion = $req->localizacion;
            $estructura->base_m = $req->baseM;
            $estructura->longitud = $req->longitud;
            $estructura->longitud_total = $req->longitudTotal;
            $estructura->observaciones = $req->observaciones;
            $estructura->documento_modificacion = $req->documentoModificacion;
            $estructura->coordenada_este = $req->coordenadaEste;
            $estructura->coordenada_norte = $req->coordenadaNorte;
            $estructura->accion_ambiental = $req->accionAmbiental;
            $estructura->status_accion_ambiental = $req->statusAccionAmbiental;
            $estructura->tipo_de_paso_de_fauna = $req->tipoDePasoDeFauna;
            $estructura->obra_adyacente = $req->obraAdyacente;
            $estadoTerminado = $this->traitEstadoTerminado();
            if($estadoTerminado->id_estados == $req->estado) {
                $token = $req->headers->get('cod-autch', null);
                $tokenPersonal = PersonalAccessToken::findToken($token);
                $estructura->usuario_terminar = $tokenPersonal->tokenable_id;
                $estructura->fecha_terminar = $req->fechaTerminar;
            }
            $estructura->fk_estado = $req->estado;
            $estructura->ESTADO = $estadoGet->descripcion_estado;
            $estructura->fk_calzada = $req->tipoCalzada;
            $estructura->descripcion = $req->descripcion;
            if($req->tipoObra != 0) {
                $estructura->fk_tipo_obra = $req->tipoObra;
            }
            if($req->licenciaAmbiental != 0) {
                $estructura->fk_licencia_ambiental = $req->licenciaAmbiental;
            }
            if($req->tipoAdaptacion != 0) {
                $estructura->fk_tipo_adaptacion = $req->tipoAdaptacion;
            }
            if($req->accionEstructura != 0) {
                $estructura->fk_accion_estructura = $req->accionEstructura;
            }
            if($req->materialPresupuestado != 0) {
                $estructura->fk_material_presupuestado = $req->materialPresupuestado;
            }
            if($req->tipoEstructura != 0) {
                $estructura->fk_tipo_estructura = $req->tipoEstructura;
                $estructura->TIP_1 = $tipoEstructura->TIP_1;
                $estructura->TIPO_DE_ESTRUCTURA = $tipoEstructura->TIPO_DE_ESTRUCTURA;
            }
            $estructura = $this->traitSetProyectoYCompania($req, $estructura);
            if($estructura->save()) {
                $aux = new estructuras;
                $aux->N = $estructura->N;
                return $this->handleResponse($req, $this->estructuraToModel($aux), 'Estructura guardada en el sistema');
            }
        } catch(Exception $exc) {
            return $this->handleAlert('No se pudo guardar la estructura en el sistema.');
        }
    }

    public function update(Request $req, $id): JsonResponse {
        if(!is_numeric($id)) {
            return $this->handleAlert('Estructura no valida');
        }
        if(!$req->json()->has('tramo')) {
            return $this->handleAlert('Falta el campo tramo');
        }
        if(!$req->json()->has('actividad')) {
            return $this->handleAlert('Falta el campo actividad');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta el campo estado');
        }
        if(!$req->json()->has('hito')) {
            return $this->handleAlert('Falta el campo hito');
        }
        $validator = Validator::make($req->all(),[
            'obra'=> 'string|nullable|max:50',
            'nomenclatura'=> 'string|max:50',
            'abscisa'=> 'max:10',
            'diametro'=> 'numeric|nullable|max:50',
            'celdas'=> 'numeric|nullable',
            'localizacion'=> 'string|nullable|max:50',
            'tipoObra'=> 'string|nullable|max:2',
            'baseM'=> 'numeric|max:999',
            'longitud'=> 'numeric|nullable|max:999',
            'longitudTotal'=> 'numeric|nullable|max:999',
            'observaciones'=> 'string|nullable',
            'documentoModificacion'=> 'string|nullable|max:60',
            'coordenadaEste'=> 'numeric|nullable',
            'coordenadaNorte'=> 'numeric|nullable',
            'accionAmbiental'=> 'nullable|string|max:100',
            'statusAccionAmbiental'=> 'string|nullable|max:100',
            'tipoDePasoDeFauna'=> 'numeric|max:10',
            'obraAdyacente'=> 'string|nullable|max:50',
            'licenciaAmbiental'=> 'numeric',
            'tipoAdaptacion'=> 'nullable|numeric',
            'accionEstructura'=> 'numeric|nullable',
            'materialPresupuestado'=> 'numeric',
            'tipoEstructura'=> 'numeric|nullable',

        ]);
        if($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }
        try{
            if(WbTramos::where('Id_Tramo', $req->tramo)->first() == null) {
                return $this->handleAlert('Tramo no encontrado.');
            }
            if(WbHitos::where('Id_Hitos', $req->hito)->first() == null) {
                return $this->handleAlert('Hito no encotrado.');
            }
            $estadoGet = estado::find($req->estado);
            if($estadoGet == null) {
                return $this->handleAlert('Estado no encontrada.');
            }
            if(WbTipoCalzada::find($req->tipoCalzada) == null) {
                return $this->handleAlert('Calzada no encontrada.');
            }
            if(WbTipoDeObra::find($req->tipoObra) == null) {
                return $this->handleAlert('Tipo de obra no encontrada.');
            }
            if($req->tipoEstructura != 0 && estruc_tipos::find($req->tipoEstructura) == null) {
                return $this->handleAlert('Tipo de estructura no encontrada.');
            }
            if($req->licenciaAmbiental != 0 && WbLicenciaAmbiental::find($req->licenciaAmbiental) == null) {
                return $this->handleAlert('Licencia ambiental no encotrada.');
            }
            if($req->tipoAdaptacion != 0 && WbTipoDeAdaptacion::find($req->tipoAdaptacion) == null) {
                return $this->handleAlert('Tipo de adaptacion no encotrada.');
            }
            if($req->accionEstructura != 0 && WbAccionEstructura::find($req->accionEstructura) == null) {
                return $this->handleAlert('Accion estructura no encontrada.');
            }
            if($req->materialPresupuestado != 0 && WbMaterialPresupuestado::find($req->materialPresupuestado) == null) {
                return $this->handleAlert('Material presupuestado no encontrada.');
            }
            $estructura = estructuras::find($id);
            if($estructura == null) {
                return $this->handleAlert('Estructura no encontrada.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($estructura->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Estructura no valida.');
            }
            $estructura->TRAMO = $req->tramo;
            $estructura->HITO_OTRO_SI_10 = $req->hito;
            $estructura->OBRA = $req->obra;
            $estructura->NOMENCLATURA = $req->nomenclatura;
            $estructura->ABSCISA = $req->abscisa;
            $estructura->DIAMETRO = $req->diametro;
            $estructura->CELDAS = $req->celdas;
            $estructura->WBS = $req->wbs;
            $estructura->actividad = $req->actividad;
            $estructura->localizacion = $req->localizacion;
            $estructura->fk_calzada = $req->calzada;
            $estructura->base_m = $req->baseM;
            $estructura->longitud = $req->longitud;
            $estructura->longitud_total = $req->longitudTotal;
            $estructura->observaciones = $req->observaciones;
            $estructura->documento_modificacion = $req->documentoModificacion;
            $estructura->coordenada_este = $req->coordenadaEste;
            $estructura->coordenada_norte = $req->coordenadaNorte;
            $estructura->accion_ambiental = $req->accionAmbiental;
            $estructura->status_accion_ambiental = $req->statusAccionAmbiental;
            $estructura->tipo_de_paso_de_fauna = $req->tipoDePasoDeFauna;
            $estructura->obra_adyacente = $req->obraAdyacente;
            $estadoTerminado = $this->traitEstadoTerminado();
            if($estructura->fk_estado != $req->estado && $estadoTerminado->id_estados == $req->estado) {
                $permiso = $this->traitPermisoPorNombre('TERMINAR_ESTRUCTURA');
                if($permiso->count() > 0) {
                    $token = $req->headers->get('cod-autch', null);
                    $tokenPersonal = PersonalAccessToken::findToken($token);
                    $usuario = usuarios_M::find($tokenPersonal->tokenable_id);
                    $permisoRol = $this->traitPermisosPorIdYRol($usuario->fk_rol, $permiso[0]->id_permiso);
                    if($permisoRol->count() > 0) {
                        $estructura->fk_estado = $req->estado;
                        $estructura->fecha_terminar = $req->fechaTerminar;
                        $estructura->usuario_terminar = $tokenPersonal->tokenable_id;
                    }
                } else {
                    return $this->handleAlert('Su usuario no cuenta con privilegios para poner items de obra en estado terminado.');
                }
            } else {
                $estructura->fk_estado = $req->estado;
            }
            $estructura->ESTADO = $estadoGet->descripcion_estado;
            $estructura->fk_calzada = $req->tipoCalzada;
            $estructura->descripcion = $req->descripcion;
            $estructura->fk_tipo_obra = $req->tipoObra;
            if($req->licenciaAmbiental != 0) {
                $estructura->fk_licencia_ambiental = $req->licenciaAmbiental;
            } else {
                $estructura->fk_licencia_ambiental = null;
            }
            if($req->tipoAdaptacion != 0) {
                $estructura->fk_tipo_adaptacion = $req->tipoAdaptacion;
            } else {
                $estructura->fk_tipo_adaptacion = null;
            }
            if($req->accionEstructura != 0) {
                $estructura->fk_accion_estructura = $req->accionEstructura;
            } else {
                $estructura->fk_accion_estructura = null;
            }
            if($req->materialPresupuestado != 0) {
                $estructura->fk_material_presupuestado = $req->materialPresupuestado;
            } else {
                $estructura->fk_material_presupuestado = null;
            }
            if($req->tipoEstructura != 0) {
                $estructura->fk_tipo_estructura = $req->tipoEstructura;
            } else {
                $estructura->fk_tipo_estructura = null;
            }
            if($estructura->save()) {
                return $this->handleResponse($req, [], 'Estructura guardada en el sistema');
            }
        } catch(Exception $exc) {
            var_dump($exc);
        }
        return $this->handleAlert('No se pudo guardar la estructura en el sistema.');
    }

    function download() {
        return Excel::download(estructuras::all(), 'users.xlsx');
    }

    /**
     * @param Request $request
     * @param $tramo
     * @param $hito
     * @return JsonResponse
     * @deprecated usar getTipoEstructura
     */
    function getTipoEstructuraDeprecated(Request $request, $tramo, $hito) {
        $consulta = estruc_tipos::select('Estruc_tipos.id as identificador', 'Estruc_tipos.TIP_1 as tipo','Estruc_tipos.TIPO_DE_ESTRUCTURA as descripcion')
            ->where('Estructuras.TRAMO', $tramo)
            ->where('Estructuras.HITO_OTRO_SI_10', $hito)
            ->where('Estructuras.ESTADO', '!=', 'TERMINADO')
            ->where('Estruc_tipos.estado', '1')
            ->leftJoin('Estructuras', 'Estructuras.fk_tipo_estructura', '=', 'Estruc_tipos.id')
            ->groupBy('Estruc_tipos.TIPO_DE_ESTRUCTURA')
            ->groupBy('Estruc_tipos.TIP_1')
            ->groupBy('Estruc_tipos.id')
            ->orderBy('Estruc_tipos.TIP_1', 'asc')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    function getTipoEstructura(Request $request, $tramo, $hito) {
        $consulta = estruc_tipos::select('Estruc_tipos.id as identificador', 'Estruc_tipos.TIP_1 as tipo','Estruc_tipos.TIPO_DE_ESTRUCTURA as descripcion')
            ->where('Estructuras.TRAMO', $tramo)
            ->where('Estructuras.HITO_OTRO_SI_10', $hito)
            ->where('Estructuras.ESTADO', '!=', 'TERMINADO')
            ->where('Estruc_tipos.estado', '1')
            ->leftJoin('Estructuras', 'Estructuras.fk_tipo_estructura', '=', 'Estruc_tipos.id')
            ->groupBy('Estruc_tipos.TIPO_DE_ESTRUCTURA')
            ->groupBy('Estruc_tipos.TIP_1')
            ->groupBy('Estruc_tipos.id')
            ->orderBy('Estruc_tipos.TIP_1', 'asc');
        $consulta = $this->filtrar($request, $consulta, 'Estruc_tipos')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    function getLicenciaDeprecated(Request $request, $tramo, $hito) {
        $consulta = estructuras::select('Wb_licencia_ambiental.nomenclatura as nombre', 'Wb_licencia_ambiental.descripcion as descripcion')
            ->where('TRAMO', $tramo)
            ->where('HITO_OTRO_SI_10', $hito)
            ->where('ESTADO', 'not like', '%TERMINADO%')
            ->whereNotNull('Estructuras.fk_licencia_ambiental')
            ->leftjoin('Wb_licencia_ambiental', 'Wb_licencia_ambiental.id', '=', 'Estructuras.fk_licencia_ambiental')
            ->groupBy('Wb_licencia_ambiental.nomenclatura')
            ->groupBy('Wb_licencia_ambiental.descripcion')
            ->orderBy('Wb_licencia_ambiental.nomenclatura', 'desc')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    function getLicencia(Request $request, $tramo, $hito) {
        $consulta = estructuras::select('Wb_licencia_ambiental.nomenclatura as nombre', 'Wb_licencia_ambiental.descripcion as descripcion')
            ->where('TRAMO', $tramo)
            ->where('HITO_OTRO_SI_10', $hito)
            ->where('ESTADO', 'not like', '%TERMINADO%')
            ->whereNotNull('Estructuras.fk_licencia_ambiental')
            ->leftjoin('Wb_licencia_ambiental', 'Wb_licencia_ambiental.id', '=', 'Estructuras.fk_licencia_ambiental')
            ->groupBy('Wb_licencia_ambiental.nomenclatura')
            ->groupBy('Wb_licencia_ambiental.descripcion')
            ->orderBy('Wb_licencia_ambiental.nomenclatura', 'desc');
        $consulta = $this->filtrar($request, $consulta, 'Estructuras')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    /**
     * @param Request $request
     * @param $tramo
     * @param $hito
     * @param $tipoEstructura
     * @return JsonResponse
     * @deprecated usar getNomenclaturasFinalizar
     */
    function getNomenclaturasFinalizarDeprecated(Request $request, $tramo, $hito, $tipoEstructura) {
        if( $tramo != null and $hito != null && $tipoEstructura != null) {
            $consulta = estructuras::select('Estructuras.N as identificador','Estructuras.NOMENCLATURA as nomenclatura')
                ->where('TRAMO', $tramo)
                ->where('HITO_OTRO_SI_10', $hito)
                ->where('Estructuras.ESTADO', '!=', 'TERMINADO')
                ->leftjoin('Estruc_tipos', 'Estruc_tipos.id', 'Estructuras.fk_tipo_estructura')
                ->where('Estruc_tipos.id', $tipoEstructura)->get();
            return $this->handleResponse($request, $consulta, __("messages.consultado"));
        }
        return $this->handleResponse($request, [], 'no consultado');
    }

    function getNomenclaturasFinalizar(Request $request, $tramo, $hito, $tipoEstructura) {
        if( $tramo != null and $hito != null && $tipoEstructura != null) {
            $consulta = estructuras::select('Estructuras.N as identificador','Estructuras.NOMENCLATURA as nomenclatura')
                ->where('TRAMO', $tramo)
                ->where('HITO_OTRO_SI_10', $hito)
                ->where('Estructuras.ESTADO', '!=', 'TERMINADO')
                ->leftjoin('Estruc_tipos', 'Estruc_tipos.id', 'Estructuras.fk_tipo_estructura')
                ->where('Estruc_tipos.id', $tipoEstructura);
            $consulta = $this->filtrar($request, $consulta,'Estructuras')->get();
            return $this->handleResponse($request, $consulta, __("messages.consultado"));
        }
        return $this->handleResponse($request, [], 'no consultado');
    }

    function getNomenclaturasFinalizarPorLicenciaDeprecated(Request $request, $tramo, $hito, $licencia) {
        if( $tramo != null and $hito != null && $licencia != null) {

            $consulta = estructuras::select('N as identificador','Estructuras.NOMENCLATURA as nomenclatura')
                ->where('TRAMO', $tramo)
                ->where('HITO_OTRO_SI_10', $hito)
                ->leftjoin('Wb_licencia_ambiental', 'Wb_licencia_ambiental.id', '=', 'Estructuras.fk_licencia_ambiental')
                ->where('Wb_licencia_ambiental.nomenclatura', $licencia)
                ->where('ESTADO', '!=', 'TERMINADO')->get();
            return $this->handleResponse($request, $consulta, __("messages.consultado"));
        }
        return $this->handleResponse($request, [], 'no consultado');
    }

    function getNomenclaturasFinalizarPorLicencia(Request $request, $tramo, $hito, $licencia) {
        if( $tramo != null and $hito != null && $licencia != null) {

            $consulta = estructuras::select('N as identificador','Estructuras.NOMENCLATURA as nomenclatura')
                ->where('TRAMO', $tramo)
                ->where('HITO_OTRO_SI_10', $hito)
                ->leftjoin('Wb_licencia_ambiental', 'Wb_licencia_ambiental.id', '=', 'Estructuras.fk_licencia_ambiental')
                ->where('Wb_licencia_ambiental.nomenclatura', $licencia)
                ->where('ESTADO', '!=', 'TERMINADO');
            $consulta = $this->filtrar($request, $consulta, 'Estructuras')->get();
            return $this->handleResponse($request, $consulta, __("messages.consultado"));
        }
        return $this->handleResponse($request, [], 'no consultado');
    }

    /**
     * @param Request $req
     * @param $id
     * @return JsonResponse
     * @deprecated
     */
    function finalizarEstructuraDeprecated(Request $req, $id) {
        if($id <= 0) {
            return $this->handleAlert('Estructura no valida');
        }
        if(!$req->has("fechaFinalizacion")) {
            return $this->handleAlert("Falta fecha de finalizacion");
        }
        if(!$req->has("usuario")) {
            return $this->handleAlert("Falta usuario");
        }
        $estructuraTerminar = estructuras::find($id);
        if(usuarios_M::find($req->usuario) == null) {
            return $this->handleAlert('Usuario no encontrado.');
        }
        if($estructuraTerminar->ESTADO == "TERMINADO") {
            return $this->handleAlert('Esta estructura se encuentra termianda.');
        }
        //estado terminado
        $estructuraTerminar->ESTADO = "TERMINADO";
        $estructuraTerminar->fk_estado = 23;

        $estructuraTerminar->usuario_terminar = $req->usuario;
        $estructuraTerminar->fecha_terminar = $req->fechaFinalizacion;
        try {
            if($estructuraTerminar->save()) {
                return $this->handleResponse($req, $estructuraTerminar, 'Estructura terminada');
            }
        } catch (Exception $exc){}
        return $this->handleAlert('No se pudo cambiar el estado de la estructura.');
    }

    function finalizarEstructura(Request $req, $id) {
        if($id <= 0) {
            return $this->handleAlert('Estructura no valida');
        }
        if(!$req->has("fechaFinalizacion")) {
            return $this->handleAlert("Falta fecha de finalizacion");
        }
        if(!$req->has("usuario")) {
            return $this->handleAlert("Falta usuario");
        }
        $estructuraTerminar = estructuras::find($id);
        if(usuarios_M::find($req->usuario) == null) {
            return $this->handleAlert('Usuario no encontrado.');
        }
        if($estructuraTerminar->ESTADO == "TERMINADO") {
            return $this->handleAlert('Esta estructura se encuentra termianda.');
        }
        //estado terminado
        $estructuraTerminar->ESTADO = "TERMINADO";
        $estructuraTerminar->fk_estado = 23;

        $estructuraTerminar->usuario_terminar = $req->usuario;
        $estructuraTerminar->fecha_terminar = $req->fechaFinalizacion;
        try {
            if($estructuraTerminar->save()) {
                return $this->handleResponse($req, $estructuraTerminar, 'Estructura terminada');
            }
        } catch (Exception $exc){}
        return $this->handleAlert('No se pudo cambiar el estado de la estructura.');
    }

    /**
     * @param $tramo
     * @return JsonResponse
     * @deprecated
     */
    public function getParaSincronizarAppDeprecated($tramo) {

        $consulta = estructuras::select(
            'TRAMO',
            'HITO_OTRO_SI_10',
            'TIPO_DE_ESTRUCTURA',
            'NOMENCLATURA',
            'ABSCISA',
            'N'
        )->where('TRAMO', $tramo)
            ->where(function($query) {
                $estadoTerminar = $this->traitEstadoTerminado();
                $query->where('fk_estado', '!=', $estadoTerminar->id_estados)
                    ->orWhereNull('fk_estado');
            })
            ->where('estado_registro','=','1')
            ->get();
        return response()->json($consulta, 200);
    }

    public function getParaSincronizarApp(Request $request, $tramo) {

        $consulta = estructuras::select(
            'TRAMO',
            'HITO_OTRO_SI_10',
            'et.TIPO_DE_ESTRUCTURA',
            'NOMENCLATURA',
            'ABSCISA',
            'N'
        )
        ->leftjoin('Estruc_tipos as et','estructuras.fk_tipo_estructura','et.id')
        ->where('TRAMO', $tramo)
        ->where(function($query) {
                $estadoTerminar = $this->traitEstadoTerminado();
                $query->where('fk_estado', '!=', $estadoTerminar->id_estados)
                      ->orWhereNull('fk_estado');
            })
        ->where('estado_registro','=','1');
        $consulta = $this->filtrarPorProyecto($request, $consulta,'estructuras')->get();
        return $this->handleResponse($request, $consulta, __('messages.consultado'));
    }

    public function getParaSalaTecnica(Request $request,$proyecto) {
        if(!is_numeric($request->page) || !is_numeric($request->limit) && (strlen($request->idBuscar) > 0 && !is_numeric($request->idBuscar))) {
            return $this->handleAlert('Datos invalidos');
        }
        $tipoEstructuras = estruc_tipos::all();
        $materialesPresupuestado = WbMaterialPresupuestado::all();
        $tiposAdaptacion = WbTipoDeAdaptacion::all();
        $accionesEstructura = WbAccionEstructura::all();
        $estados = estado::all();
        $licenciaAmbiental = WbLicenciaAmbiental::all();
        $tiposObra = WbTipoDeObra::all();
        $tiposCalzada = WbTipoCalzada::all();
        $consulta = estructuras::orderBy('N', 'desc')->where('estado_registro','=','1');
        $contador = estructuras::select('N')->where('estado_registro','=','1')->get();
        if (is_numeric($request->idBuscar) && $request->idBuscar) {
            $consulta->where('N', $request->idBuscar);
            $contador->where('N', $request->idBuscar);
        } else if(strlen($request->idBuscar) > 0) {
            $consulta->where('N', 0);
        }
        $limitePaginas = ($contador->count()/$request->limit) + 1;
        $consulta = $this->filtrar($request, $consulta);
        $consulta = $consulta->forPage($request->page, $request->limit)->get();
        foreach ($consulta as $estructura) {
            $this->setTipoEstructuraById($estructura, $tipoEstructuras);
            $this->setMaterialPresupuestadoById($estructura, $materialesPresupuestado);
            $this->setTipoAdaptacionById($estructura, $tiposAdaptacion);
            $this->setAccionEstructuraById($estructura, $accionesEstructura);
            $this->setEstadoById($estructura, $estados);
            $this->setLicenciaAmbientalById($estructura, $licenciaAmbiental);
            $this->setTipoObraById($estructura, $tiposObra);
            $this->setTipoCalzadaById($estructura, $tiposCalzada);
        }
        return $this->handleResponse($request, $this->estructuraToArray($consulta), __("messages.consultado"), $limitePaginas);
    }

    public function getByTipoEstructuraYAbcisasCercanas(Request $request, $tipoEstructura, $hito) {
        if(!is_numeric($tipoEstructura)) {
            return $this->handleAlert('Tipo de estructura no valida.');
        }
        $consulta = estructuras::where('fk_tipo_estructura', $tipoEstructura)
            ->where('HITO_OTRO_SI_10', $hito)
            ->orderBy('N', 'desc')->get();
        /*for($i = 0; $i < $consulta->count(); $i ++) {
            $exp_regular = array();
            $exp_regular[0] = '/K/';
            $exp_regular[1] = '/+/';
            $cadena_nueva = array();
            $cadena_nueva[0] = '';
            $cadena_nueva[1] = '';
            $abcisaConsultada = preg_replace($exp_regular, $cadena_nueva, $consulta[$i]->ABSCISA);
            if($abcisaConsultada > $abcisa) {

            } else {

            }
        }*/
        return $this->handleResponse($request, $this->toArray($consulta), __("messages.consultado"));
    }

    private function setTipoCalzadaById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_calzada == $array[$i]->id_tipo_calzada) {
                $reescribir["identificador"] = $array[$i]->id_tipo_calzada;
                $reescribir["calzada"] = $array[$i]->Calzada;
                $reescribir["descripcion"] = $array[$i]->Descripcion;
                $estructura->objectTipoCalzada = $reescribir;
                break;
            }
        }
    }

    private function setTipoObraById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_tipo_obra == $array[$i]->id) {
                $reescribir["identificador"] = $array[$i]->id;
                $reescribir["nombre"] = $array[$i]->nombre;
                $estructura->objectTipoObra = $reescribir;
                break;
            }
        }
    }

    private function setTipoEstructuraById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_tipo_estructura == $array[$i]->id) {
                $reescribir["identificador"] = $array[$i]->id;
                $reescribir["tipo"] = $array[$i]->TIP_1;
                $reescribir["descripcion"] = $array[$i]->TIPO_DE_ESTRUCTURA;
                $reescribir["actividad"] = $array[$i]->actividad;
                $estructura->objectTipoEstructura = $reescribir;
                break;
            }
        }
    }

    private function setMaterialPresupuestadoById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_material_presupuestado == $array[$i]->id) {
                $reescribir["identificador"] = $array[$i]->id;
                $reescribir["nombre"] = $array[$i]->nombre;
                $estructura->objectMaterialPresupuestado = $reescribir;
                break;
            }
        }
    }

    private function setTipoAdaptacionById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_tipo_adaptacion == $array[$i]->id) {
                $reescribir["identificador"] = $array[$i]->id;
                $reescribir["nombre"] = $array[$i]->nombre;
                $estructura->objectTipoAdaptacion = $reescribir;
                break;
            }
        }
    }

    private function setAccionEstructuraById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_accion_estructura == $array[$i]) {
                $reescribir["identificador"] = $array[$i]->id;
                $reescribir["accionAmbiental"] = $array[$i]->accion_ambiental;
                $reescribir["statusAcionAmbiental"] = $array[$i]->status_acion_ambiental;
                $reescribir["tipoDePasoDeFauna"] = $array[$i]->tipo_de_paso_de_fauna;
                $reescribir["tipoDe_Adaptacion"] = $array[$i]->tipo_de_adaptacion;
                $reescribir["obraAdyacente"] = $array[$i]->obra_adyacente;
                $estructura->objectAccionEstructura = $reescribir;
                break;
            }
        }
    }

    private function setEstadoById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_estado == $array[$i]->id_estados) {
                $reescribir["identificador"] = $array[$i]->id_estados;
                $reescribir["nombre"] = $array[$i]->descripcion_estado;
                $estructura->objectEstado = $reescribir;
                break;
            }
        }
    }

    private function setLicenciaAmbientalById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_licencia_ambiental == $array[$i]->id) {
                $reescribir["identificador"] = $array[$i]->id;
                $reescribir["nombre"] = $array[$i]->nombre;
                $reescribir["entidadDeLicencia"] = $array[$i]->entidad_de_licencia;
                $reescribir["licencia"] = $array[$i]->licencia;
                $estructura->objectlicenciaAmbiental = $reescribir;
                break;
            }
        }
    }

    public function delete(Request $request, $id)
    {
        if(!is_numeric($id)) {
            return $this->handleAlert('Estructura no valida');
        }
        $estructuraEliminar = estructuras::find($id);
        if($estructuraEliminar == null) {
            return $this->handleAlert('Estructura no encontrada');
        }
        $estructuraEliminar->estado_registro = 0;
        if($estructuraEliminar->save()) {
            return $this->handleResponse($request, [], 'Estructura eliminada');
        }
        return $this->handleAlert('Estructura no eliminada');
    }

    public function get(Request $request)
    {
        $tipoEstructuras = estruc_tipos::all();
        $materialesPresupuestado = WbMaterialPresupuestado::all();
        $tiposAdaptacion = WbTipoDeAdaptacion::all();
        $accionesEstructura = WbAccionEstructura::all();
        $estados = estado::all();
        $licenciaAmbiental = WbLicenciaAmbiental::all();
        $tiposVia = WbTipoVia::all();
        $tiposCalzada = WbTipoCalzada::all();
        $consulta = estructuras::where('estado_registro','=','1')->get();
        foreach ($consulta as $estructura) {
            $this->setTipoEstructuraById($estructura, $tipoEstructuras);
            $this->setMaterialPresupuestadoById($estructura, $materialesPresupuestado);
            $this->setTipoAdaptacionById($estructura, $tiposAdaptacion);
            $this->setAccionEstructuraById($estructura, $accionesEstructura);
            $this->setEstadoById($estructura, $estados);
            $this->setLicenciaAmbientalById($estructura, $licenciaAmbiental);
            $this->setTipoObraById($estructura, $tiposVia);
            $this->setTipoCalzadaById($estructura, $tiposCalzada);
        }
        return $this->handleResponse($request, $this->estructuraToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
