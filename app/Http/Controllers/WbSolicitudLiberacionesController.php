<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\UsuPlanta;
use App\Models\WbMaterialCentroProduccion;
use App\Models\WbMaterialFormula;
use App\Models\WbMaterialLista;
use App\Models\wbSolicitudLiberaciones;
use App\Models\WbSolicitudMateriales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\WbFormulaCentroProduccion;
use Illuminate\Support\Facades\Log;

class WbSolicitudLiberacionesController extends BaseController implements Vervos
{

    /**
     * @param Request $req
     */
    public function post(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'tipoCapa' => 'required',
            'tramo' => 'required',
            'hito' => 'required',
            'abscisaInicialReferencia' => 'required',
            'abscisaFinalReferencia' => 'required',
            'tipoCalzada' => 'required',
            'material' => 'present|nullable',
            'espesor' => 'required',
            'ubicacion' => 'present|nullable',
            'notaUsuario' => 'nullable',
            'estado' => 'required',
            'formula' => 'nullable',
            'planta' => 'required',
            'panoramica' => 'nullable',
            'fecha' => 'date'
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->messages());
        }
        try {
            $modeloRegistrar = new wbSolicitudLiberaciones;
            $modeloRegistrar = $this->traitSetProyectoYCompania2($req, $modeloRegistrar);
            /*$encontrado = wbSolicitudLiberaciones::where('fk_id_usuarios',$this->traitGetIdUsuarioToken($req))
                ->where('fk_id_tramo', $req->tramo)
                ->where('fk_id_hito', $req->hito)
                ->where('fk_id_tipo_capa', $req->tipoCapa)
                ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                ->where('abscisaInicialReferencia', $req->abscisaInicialReferencia)
                ->where('abscisaFinalReferencia', $req->abscisaFinalReferencia)
                ->where('notaUsuario', $req->notaUsuario)
                ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                ->where('fk_compania', $modeloRegistrar->fk_compania)
                ->where('espesor', $req->espesor)
                ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) < 5")
                ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) >= 0")
                ->first();*/
            $encontrado = wbSolicitudLiberaciones::where('fk_id_tramo', $req->tramo)
                ->where('fk_id_hito', $req->hito)
                ->where('fk_id_tipo_capa', $req->tipoCapa)
                ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                ->where('fk_compania', $modeloRegistrar->fk_compania)
                ->where('ubicacion', $req->ubicacion)
                ->where('abscisaInicialReferencia', '<', $req->abscisaFinalReferencia)
                ->where('abscisaFinalReferencia', '>', $req->abscisaInicialReferencia)
                ->where('ubicacion', $req->ubicacion)
                ->whereNotIn('fk_id_estados', [13, 14]);
            if (isset($req->carril)) {
                $encontrado = $encontrado->where('fk_tipo_carril', $req->carril);
            }

            $encontrado = $encontrado->first();
            if ($encontrado || !str_contains($req->ubicacion, 'Capa Intermedia')) {
                return $this->handleAlert(__('messages.solicitud_liberacion_no_registrada') . $encontrado->id_solicitud_liberaciones);
            } else {
                $foto = str_replace('*', '/', $req->panoramica);

                $modeloRegistrar->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
                $modeloRegistrar->fk_id_tipo_capa = $req->tipoCapa;
                $modeloRegistrar->fk_id_tramo = $req->tramo;
                $modeloRegistrar->fk_id_hito = $req->hito;
                $modeloRegistrar->abscisaInicialReferencia = $req->abscisaInicialReferencia;
                $modeloRegistrar->abscisaFinalReferencia = $req->abscisaFinalReferencia;
                $modeloRegistrar->fk_id_tipo_calzada = $req->tipoCalzada;
                $modeloRegistrar->fk_id_material = $req->material;
                $modeloRegistrar->espesor = $req->espesor;
                $modeloRegistrar->ubicacion = $req->ubicacion;
                $modeloRegistrar->notaUsuario = $req->notaUsuario;
                $modeloRegistrar->fk_id_estados = $req->estado;
                $modeloRegistrar->fk_id_formula = $req->formula;
                $modeloRegistrar->fk_id_planta = $req->planta;
                $modeloRegistrar->foto = $foto;
                if (isset($req->carril)) {
                    $modeloRegistrar->fk_tipo_carril = $req->carril;
                }
                if (isset($req->fecha)) {
                    $modeloRegistrar->fecha_solicitud = $req->fecha;
                }

                $modeloRegistrar->save();
                $encontrado = wbSolicitudLiberaciones::where('fk_id_usuarios', $this->traitGetIdUsuarioToken($req))
                    ->where('fk_id_tramo', $req->tramo)
                    ->where('fk_id_hito', $req->hito)
                    ->where('fk_id_tipo_capa', $req->tipoCapa)
                    ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                    ->where('abscisaInicialReferencia', $req->abscisaInicialReferencia)
                    ->where('abscisaFinalReferencia', $req->abscisaFinalReferencia)
                    ->where('notaUsuario', $req->notaUsuario)
                    ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                    ->where('fk_compania', $modeloRegistrar->fk_compania)
                    ->where('espesor', $req->espesor)
                    ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) < 5")
                    ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) >= 0");
                if (isset($req->carril)) {
                    $encontrado->where('fk_tipo_carril', $req->carril);
                }
                $encontrado = $encontrado->first();
                return $this->handleResponse($req, $encontrado->id_solicitud_liberaciones, __('messages.solicitud_liberacion_registrada'));
            }
        } catch (\Exception $exception) {
            Log::error('SOLICITUD LIBERACION: ' . ' Error: ' . $exception);
            return $this->handleAlert('Error al registrar la solicitud.', 0);
        }
    }

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param Request $request
     */
    public function get(Request $request)
    {
        $consulta = wbSolicitudLiberaciones::select(
            'id_solicitud_liberaciones',
            'fk_id_usuarios',
            'capa.Descripcion',
            'fk_id_tramo',
            'fk_id_hito',
            'abscisaInicialReferencia',
            'abscisaFinalReferencia',
            DB::raw("'Inicial: K'+ RIGHT('00000' + left(abscisaInicialReferencia, len(abscisaInicialReferencia)-3),2) +'+'+  RIGHT('00000' +abscisaInicialReferencia,3) + ' - Final: K'+ RIGHT('00000' +left(abscisaFinalReferencia,len(abscisaFinalReferencia)-3),2) +'+'+ RIGHT('00000' +abscisaFinalReferencia,3) as inicialfinal"),
            DB::raw("IIF (Wb_Solicitud_Liberaciones.fk_tipo_carril IS NULL,  calzada.Descripcion , calzada.Descripcion + '\n' + carril.Descripcion) as Calzada"),
            'fk_id_material',
            'material.Nombre',
            'material.unidadMedida',
            'notaUsuario',
            'fk_id_estados',
            'esta.descripcion_estado',
            DB::raw("convert(varchar, fecha_solicitud, 0)+char(10)+convert(varchar, Wb_Solicitud_Liberaciones.date_create) as fecha_solicitud"),
            'fk_id_formula',
            'pla.NombrePlanta',
            'espesor',
            'Wb_Solicitud_Liberaciones.ubicacion',
            DB::raw("ISNULL(Formula.Nombre,material.Nombre) as nombreFormula"),
            'foto'
        )->leftJoin('Wb_Tipo_Capa as capa', 'capa.id_tipo_capa', 'Wb_Solicitud_Liberaciones.fk_id_tipo_capa')
            ->leftJoin('Wb_Tipo_Calzada as calzada', 'calzada.id_tipo_calzada', 'Wb_Solicitud_Liberaciones.fk_id_tipo_calzada')
            ->leftJoin('Wb_Tipo_Carril as carril', 'carril.id_tipo_carril', 'Wb_Solicitud_Liberaciones.fk_tipo_carril')
            ->leftJoin('Wb_Material_Lista as material', 'material.id_material_lista', 'Wb_Solicitud_Liberaciones.fk_id_material')
            ->leftJoin('estados as esta', 'esta.id_estados', 'Wb_Solicitud_Liberaciones.fk_id_estados')
            ->leftJoin('usuPlanta as pla', 'pla.id_plata', 'Wb_Solicitud_Liberaciones.fk_id_planta')
            ->leftJoin('Wb_Formula_Lista as Formula', 'Formula.id_formula_lista', 'Wb_Solicitud_Liberaciones.fk_id_formula')
            //->whereBetween('Wb_Solicitud_Liberaciones.fecha_solicitud', [DB::raw("CONVERT(DATETIME,GETDATE() + ' 00:00:00',120)"), DB::raw("CONVERT(DATETIME,GETDATE() + ' 23:59:59',105)")])
            ->where('Wb_Solicitud_Liberaciones.fecha_solicitud', DB::raw("convert(date,getdate())"))
            ->orderBy('id_solicitud_liberaciones', 'desc');
        $consulta = $this->filtrar($request, $consulta, 'Wb_Solicitud_Liberaciones');
        return $this->handleResponse($request, $consulta->get(), __('messages.consultado'));
    }

    public function getByFecha(Request $request)
    {
        $consulta = wbSolicitudLiberaciones::select(
            'id_solicitud_liberaciones',
            'fk_id_usuarios',
            'capa.Descripcion',
            'fk_id_tramo',
            'fk_id_hito',
            'abscisaInicialReferencia',
            'abscisaFinalReferencia',
            DB::raw("'Inicial: K'+ RIGHT('00000' + left(abscisaInicialReferencia, len(abscisaInicialReferencia)-3),2) +'+'+  RIGHT('00000' +abscisaInicialReferencia,3) + ' - Final: K'+ RIGHT('00000' +left(abscisaFinalReferencia,len(abscisaFinalReferencia)-3),2) +'+'+ RIGHT('00000' +abscisaFinalReferencia,3) as inicialfinal"),
            DB::raw("IIF (Wb_Solicitud_Liberaciones.fk_tipo_carril IS NULL,  calzada.Descripcion , calzada.Descripcion + '\n' + carril.Descripcion) as Calzada"),
            'fk_id_material',
            'material.Nombre',
            'material.unidadMedida',
            'notaUsuario',
            'fk_id_estados',
            'esta.descripcion_estado',
            DB::raw("convert(varchar, fecha_solicitud, 0)+char(10)+convert(varchar, Wb_Solicitud_Liberaciones.date_create) as fecha_solicitud"),
            'fk_id_formula',
            'pla.NombrePlanta',
            'espesor',
            'Wb_Solicitud_Liberaciones.ubicacion',
            DB::raw("ISNULL(Formula.Nombre,material.Nombre) as nombreFormula"),
            'foto'
        )->leftJoin('Wb_Tipo_Capa as capa', 'capa.id_tipo_capa', 'Wb_Solicitud_Liberaciones.fk_id_tipo_capa')
            ->leftJoin('Wb_Tipo_Calzada as calzada', 'calzada.id_tipo_calzada', 'Wb_Solicitud_Liberaciones.fk_id_tipo_calzada')
            ->leftJoin('Wb_Tipo_Carril as carril', 'carril.id_tipo_carril', 'Wb_Solicitud_Liberaciones.fk_tipo_carril')
            ->leftJoin('Wb_Material_Lista as material', 'material.id_material_lista', 'Wb_Solicitud_Liberaciones.fk_id_material')
            ->leftJoin('estados as esta', 'esta.id_estados', 'Wb_Solicitud_Liberaciones.fk_id_estados')
            ->leftJoin('usuPlanta as pla', 'pla.id_plata', 'Wb_Solicitud_Liberaciones.fk_id_planta')
            ->leftJoin('Wb_Formula_Lista as Formula', 'Formula.id_formula_lista', 'Wb_Solicitud_Liberaciones.fk_id_formula')
            //->whereBetween('Wb_Solicitud_Liberaciones.fecha_solicitud', [DB::raw("CONVERT(DATETIME,'". $request->fecha." 00:00:00',120)"), DB::raw("CONVERT(DATETIME,'". $request->fecha." 23:59:59',120)")])
            ->where('Wb_Solicitud_Liberaciones.fecha_solicitud', DB::raw("convert(date,'" . $request->fecha . "')"))
            ->orderBy('id_solicitud_liberaciones', 'desc');
        $consulta = $this->filtrar($request, $consulta, 'Wb_Solicitud_Liberaciones');
        return $this->handleResponse($request, $consulta->get(), __('messages.consultado'));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function post1(Request $req) //compatible hasta la version 2.0.3.0 
    {
        $validator = Validator::make($req->all(), [
            'tipoCapa' => 'required',
            'tramo' => 'required',
            'hito' => 'required',
            'abscisaInicialReferencia' => 'required',
            'abscisaFinalReferencia' => 'required',
            'tipoCalzada' => 'required',
            'material' => 'present|nullable',
            'espesor' => 'required',
            'ubicacion' => 'present|nullable',
            'notaUsuario' => 'nullable',
            'estado' => 'required',
            'formula' => 'nullable',
            'planta' => 'required',
            'panoramica' => 'nullable',
            'fecha' => 'date'
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->messages());
        }
        try {
            $modeloRegistrar = new wbSolicitudLiberaciones;
            $modeloRegistrar = $this->traitSetProyectoYCompania2($req, $modeloRegistrar);
            /*$encontrado = wbSolicitudLiberaciones::where('fk_id_usuarios',$this->traitGetIdUsuarioToken($req))
                ->where('fk_id_tramo', $req->tramo)
                ->where('fk_id_hito', $req->hito)
                ->where('fk_id_tipo_capa', $req->tipoCapa)
                ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                ->where('abscisaInicialReferencia', $req->abscisaInicialReferencia)
                ->where('abscisaFinalReferencia', $req->abscisaFinalReferencia)
                ->where('notaUsuario', $req->notaUsuario)
                ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                ->where('fk_compania', $modeloRegistrar->fk_compania)
                ->where('espesor', $req->espesor)
                ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) < 5")
                ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) >= 0")
                ->first();*/
            if (str_contains($req->ubicacion, 'Reproceso')) {
                $encontrado = false;
            } else {
                $encontrado = wbSolicitudLiberaciones::where('fk_id_tramo', $req->tramo)
                    ->where('fk_id_hito', $req->hito)
                    ->where('fk_id_tipo_capa', $req->tipoCapa)
                    ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                    ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                    ->where('fk_compania', $modeloRegistrar->fk_compania)
                    //->where('ubicacion', $req->ubicacion)
                    ->where('abscisaInicialReferencia', '<', $req->abscisaFinalReferencia)
                    ->where('abscisaFinalReferencia', '>', $req->abscisaInicialReferencia)
                    ->whereNotIn('fk_id_estados', [13, 14]);
                if (isset($req->carril)) {
                    $encontrado = $encontrado->where('fk_tipo_carril', $req->carril);
                }

                if (str_contains($req->ubicacion, 'Capa Intermedia')) {
                    $encontrado = $encontrado->where('ubicacion', 'like', 'Capa Final');
                } else {
                    $encontrado = $encontrado->where('ubicacion', $req->ubicacion);
                }

                $encontrado = $encontrado->first();
            }


            if ($encontrado) {
                return $this->handleAlert(__('messages.solicitud_liberacion_no_registrada') . $encontrado->id_solicitud_liberaciones);
            } else {
                $foto = str_replace('*', '/', $req->panoramica);

                $modeloRegistrar->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
                $modeloRegistrar->fk_id_tipo_capa = $req->tipoCapa;
                $modeloRegistrar->fk_id_tramo = $req->tramo;
                $modeloRegistrar->fk_id_hito = $req->hito;
                $modeloRegistrar->abscisaInicialReferencia = $req->abscisaInicialReferencia;
                $modeloRegistrar->abscisaFinalReferencia = $req->abscisaFinalReferencia;
                $modeloRegistrar->fk_id_tipo_calzada = $req->tipoCalzada;
                $modeloRegistrar->fk_id_material = $req->material;
                $modeloRegistrar->espesor = $req->espesor;
                $modeloRegistrar->ubicacion = $req->ubicacion;
                $modeloRegistrar->notaUsuario = $req->notaUsuario;
                $modeloRegistrar->fk_id_estados = $req->estado;
                $modeloRegistrar->fk_id_formula = $req->formula;
                $modeloRegistrar->fk_id_planta = $req->planta;
                $modeloRegistrar->foto = $foto;
                if (isset($req->carril)) {
                    $modeloRegistrar->fk_tipo_carril = $req->carril;
                }
                if (isset($req->fecha)) {
                    $modeloRegistrar->fecha_solicitud = $req->fecha;
                }

                $modeloRegistrar->save();
                $encontrado = wbSolicitudLiberaciones::where('fk_id_usuarios', $this->traitGetIdUsuarioToken($req))
                    ->where('fk_id_tramo', $req->tramo)
                    ->where('fk_id_hito', $req->hito)
                    ->where('fk_id_tipo_capa', $req->tipoCapa)
                    ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                    ->where('abscisaInicialReferencia', $req->abscisaInicialReferencia)
                    ->where('abscisaFinalReferencia', $req->abscisaFinalReferencia)
                    ->where('notaUsuario', $req->notaUsuario)
                    ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                    ->where('fk_compania', $modeloRegistrar->fk_compania)
                    ->where('espesor', $req->espesor)
                    ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) < 5")
                    ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) >= 0");
                if (isset($req->carril)) {
                    $encontrado->where('fk_tipo_carril', $req->carril);
                }
                $encontrado = $encontrado->first();
                try {
                    $confirmationController = new SmsController();
                    $id_usuarios = $this->traitGetIdUsuarioToken($req);
                    $mensaje = 'WEBU, La solicitud de liberacion de capas No. ' . $encontrado->id_solicitud_liberaciones . ' ha sido radicada';
                    $nota = 'Solicitar Liberaciones';
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                } catch (\Throwable $th) { }
                return $this->handleResponse($req, $encontrado->id_solicitud_liberaciones, __('messages.solicitud_liberacion_registrada'));
            }
        } catch (\Exception $exception) {
            Log::error('SOLICITUD LIBERACION: ' . ' Error: ' . $exception);
            return $this->handleAlert('Error al registrar la solicitud.', 0);
        }
    }

    public function post2(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'tipoCapa' => 'required',
            'tramo' => 'required',
            'hito' => 'required',
            'abscisaInicialReferencia' => 'required',
            'abscisaFinalReferencia' => 'required',
            'tipoCalzada' => 'required',
            'material' => 'present|nullable',
            'espesor' => 'required',
            'ubicacion' => 'present|nullable',
            'notaUsuario' => 'nullable',
            'estado' => 'required',
            'formula' => 'nullable',
            'planta' => 'required',
            'panoramica' => 'nullable',
            'fecha' => 'date'
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->messages());
        }
        try {
            $modeloRegistrar = new wbSolicitudLiberaciones;
            $modeloRegistrar = $this->traitSetProyectoYCompania2($req, $modeloRegistrar);
            /*$encontrado = wbSolicitudLiberaciones::where('fk_id_usuarios',$this->traitGetIdUsuarioToken($req))
                ->where('fk_id_tramo', $req->tramo)
                ->where('fk_id_hito', $req->hito)
                ->where('fk_id_tipo_capa', $req->tipoCapa)
                ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                ->where('abscisaInicialReferencia', $req->abscisaInicialReferencia)
                ->where('abscisaFinalReferencia', $req->abscisaFinalReferencia)
                ->where('notaUsuario', $req->notaUsuario)
                ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                ->where('fk_compania', $modeloRegistrar->fk_compania)
                ->where('espesor', $req->espesor)
                ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) < 5")
                ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) >= 0")
                ->first();*/
            if (str_contains($req->ubicacion, 'Reproceso')) {
                $encontrado = false;
            } else {
                $encontrado = wbSolicitudLiberaciones::where('fk_id_tramo', $req->tramo)
                    ->where('fk_id_hito', $req->hito)
                    ->where('fk_id_tipo_capa', $req->tipoCapa)
                    ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                    ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                    ->where('fk_compania', $modeloRegistrar->fk_compania)
                    //->where('ubicacion', $req->ubicacion)
                    ->where('abscisaInicialReferencia', '<', $req->abscisaFinalReferencia)
                    ->where('abscisaFinalReferencia', '>', $req->abscisaInicialReferencia)
                    ->whereNotIn('fk_id_estados', [13, 14]);
                if (isset($req->carril)) {
                    $encontrado = $encontrado->where('fk_tipo_carril', $req->carril);
                }

                if (str_contains($req->ubicacion, 'Capa Intermedia')) {
                    $encontrado = $encontrado->where('ubicacion', 'like', 'Capa Final');
                } else {
                    $encontrado = $encontrado->where('ubicacion', $req->ubicacion);
                }

                $encontrado = $encontrado->first();
            }


            if ($encontrado) {
                return $this->handleAlert(__('messages.solicitud_liberacion_no_registrada') . $encontrado->id_solicitud_liberaciones);
            } else {
                $foto = str_replace('*', '/', $req->panoramica);

                $modeloRegistrar->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
                $modeloRegistrar->fk_id_tipo_capa = $req->tipoCapa;
                $modeloRegistrar->fk_id_tramo = $req->tramo;
                $modeloRegistrar->fk_id_hito = $req->hito;
                $modeloRegistrar->abscisaInicialReferencia = $req->abscisaInicialReferencia;
                $modeloRegistrar->abscisaFinalReferencia = $req->abscisaFinalReferencia;
                $modeloRegistrar->fk_id_tipo_calzada = $req->tipoCalzada;
                $modeloRegistrar->fk_id_material = $req->material;
                $modeloRegistrar->espesor = $req->espesor;
                $modeloRegistrar->ubicacion = $req->ubicacion;
                $modeloRegistrar->notaUsuario = $req->notaUsuario;
                $modeloRegistrar->fk_id_estados = $req->estado;
                $modeloRegistrar->fk_id_formula = $req->formula;
                $modeloRegistrar->fk_id_planta = $req->planta;
                $modeloRegistrar->foto = $foto;
                if (isset($req->carril)) {
                    $modeloRegistrar->fk_tipo_carril = $req->carril;
                }
                if (isset($req->fecha)) {
                    $modeloRegistrar->fecha_solicitud = $req->fecha;
                }

                $modeloRegistrar->save();
                $encontrado = wbSolicitudLiberaciones::with([
                    'capa' => function ($info) {
                        $info->select('id_tipo_capa', 'Descripcion');
                    },
                    'calzada' => function ($info) {
                        $info->select('id_tipo_calzada', 'Descripcion');
                    },
                    'carril' => function ($info) {
                        $info->select('id_tipo_carril', 'Descripcion');
                    },
                    'estado' => function ($info) {
                        $info->select('id_estados', 'descripcion_estado');
                    },
                    'planta' => function ($info) {
                        $info->select('id_plata', 'NombrePlanta');
                    },
                    'material' => function ($info) {
                        $info->select('id_material_lista', 'Nombre');
                    },
                    'formula' => function ($info) {
                        $info->select('id_formula_lista', 'Nombre');
                    },
                    'firmas' => function ($info) {
                        $info->with([
                            'area' => function ($sub) {
                                $sub->select('id_area', 'Area');
                            }
                        ])->select(
                                'id_solicitudes_liberaciones_firmas',
                                'fk_id_solicitudes_liberaciones',
                                'fk_id_area',
                                'estado',
                                'fk_id_usuario',
                                'nota',
                                //'panoramica'
                            );
                    },
                    'lib_actividad' => function ($info) {
                        $info->with([
                            'responsable' => function ($sub) {
                                $sub->select('id_liberacion_responsable', 'fk_id_liberaciones_actividades', 'fk_id_area', 'estado');
                            },
                            'actividad' => function ($sub) {
                                $sub->select('id_liberaciones_actividades', 'nombre', 'criterios', 'imagen', 'nivel_tolerancia', 'ExcentoNC');
                            }
                        ])->select(
                                'id_solicitud_liberaciones_act',
                                'fk_id_solicitud_liberaciones',
                                'fk_id_liberaciones_actividades',
                                'calificacion',
                                'estado',
                                'nota'
                            );
                    }
                ])->select(
                    'id_solicitud_liberaciones',
                    'fk_id_usuarios',
                    'fk_id_tramo',
                    'fk_id_hito',
                    'fk_id_tipo_capa',
                    'fk_id_tipo_calzada',
                    'abscisaInicialReferencia',
                    'abscisaFinalReferencia',
                    'fk_id_material',
                    'fk_id_formula',
                    'fk_id_planta',
                    'notaUsuario',
                    'espesor',
                    'ubicacion',
                    'fk_id_estados',
                    'date_create',
                    'Fecha_solicitud',
                    //'foto'
                )->where('fk_id_usuarios', $this->traitGetIdUsuarioToken($req))
                    ->where('fk_id_tramo', $req->tramo)
                    ->where('fk_id_hito', $req->hito)
                    ->where('fk_id_tipo_capa', $req->tipoCapa)
                    ->where('fk_id_tipo_calzada', $req->tipoCalzada)
                    ->where('abscisaInicialReferencia', $req->abscisaInicialReferencia)
                    ->where('abscisaFinalReferencia', $req->abscisaFinalReferencia)
                    ->where('notaUsuario', $req->notaUsuario)
                    ->where('fk_id_project_company', $modeloRegistrar->fk_id_project_company)
                    ->where('fk_compania', $modeloRegistrar->fk_compania)
                    ->where('espesor', $req->espesor)
                    ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) < 5")
                    ->whereRaw("DATEDIFF(Minute,date_create,GETDATE()) >= 0");
                if (isset($req->carril)) {
                    $encontrado->where('fk_tipo_carril', $req->carril);
                }
                $encontrado = $encontrado->first();
                try {
                    $confirmationController = new SmsController();
                    $id_usuarios = $this->traitGetIdUsuarioToken($req);
                    $mensaje = 'WEBU, La solicitud de liberacion de capas No. ' . $encontrado->id_solicitud_liberaciones . ' ha sido radicada';
                    $nota = 'Solicitar Liberaciones';
                    $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                } catch (\Throwable $th) { }
                return $this->handleResponse($req, $this->WbSolicitudLiberacionToModel($encontrado), __('messages.solicitud_liberacion_registrada'));
            }
        } catch (\Exception $exception) {
            Log::error('SOLICITUD LIBERACION: ' . ' Error: ' . $exception);
            return $this->handleAlert('Error al registrar la solicitud.', 0);
        }
    }

    /**
     * Devuelve la composicion de la formula de una solicitud de liberacion de capa.
     */
    public function getComposicionPorSolicitudLiberacion(Request $req)
    {
        //verificamos si existe una solicitud y en caso de existir preguntamos si es numerico.
        if (!$req->has('solicitud') || !is_numeric($req->solicitud)) {
            //en el caso de no existir y no ser numercio entonces devolvemos el error.
            return $this->handleAlert(__("messages.parametros_incorrectos"));
        }

        //consultamos la solicitud deseada.
        $solicitud = wbSolicitudLiberaciones::find($req->solicitud);

        //preguntamos si existe esa solicitud.
        if ($solicitud == null) {
            //en dado caso de no existir se devuelve el error.
            return $this->handleAlert(__("messages.solicitud_no_encontrada"));
        }

        //Consultamos la formula en el centro de produccion.
        $formulaCDP = WbFormulaCentroProduccion::select('id_formula_centroProduccion', 'codigoFormulaCdp')
            ->where('fk_id_formula_lista', $solicitud->fk_id_formula)
            ->where('fk_id_planta', $solicitud->fk_id_planta)
            ->where('Estado', 'A');

        $formulaCDP = $this->filtrar($req, $formulaCDP)
            ->first();

        //consultamos si la formula existe.
        if ($formulaCDP == null) {
            //si la formula no existe entonces devolvemos el error.
            //return $this->handleAlert(__("messages.codigo_de_formula_de_centro_de_produccion_no_encontrado"));
            return $this->handleResponse($req, null, __("messages.codigo_de_formula_de_centro_de_produccion_no_encontrado"));
        }

        //consultamos el material de la formula utilizando el codigo de la formula en el centro de produccion anteriormente consultado.
        //de aqui solo tomaremos el porcentaje y la clave foranea del material.
        $materialFormula = WbMaterialFormula::where('fk_codigoFormulaCdp', $formulaCDP->codigoFormulaCdp)
            ->where('Estado', 'A');

        $materialFormula = $this->filtrar($req, $materialFormula)
            ->get(['fk_material_CentroProduccion as identificador', 'Porcentaje as porcentaje']);

        //consultamos si existen registros.
        if ($materialFormula->count() == 0) {
            //en caso de no encontrar entonces devolvemos el error.
            return $this->handleAlert(__("messages.material_de_la_formula_no_encontrada"));
        }

        //recorremos los registros de material de la formula.
        foreach ($materialFormula as $key) {
            //consultamos el material.
            $materialCDP = WbMaterialCentroProduccion::where('id_material_centroProduccion', $key->identificador)
                ->first();
            if ($materialCDP == null) {
                continue;
            }

            //luego consultamos el origen del material.
            $origen = UsuPlanta::where('id_plata', $materialCDP->fk_id_planta)
                ->first();

            //si no encontramos su origuen lo omitimos y continuamos con el ciclo.
            if ($origen == null) {
                continue;
            }

            //Consultamos el nombre del material.
            $material = WbMaterialLista::find($materialCDP->fk_id_material_lista);

            //consultamos si encontramos el material.
            if ($material == null) {
                //si no encontramos el material continuamos y omitimos el dato.
                continue;
            }

            //agregamos el origen del material.
            $key->origen = $origen->NombrePlanta;

            //agregamos el nombre del material.
            $key->material = $material->Nombre;
        }

        //devolvemos la respuesta de la composicion de la formula.
        return $this->handleResponse($req, $materialFormula, __('messages.consultado'));
    }

    public function getV2(Request $request, $fecha = null)
    {
        $consulta = wbSolicitudLiberaciones::with(['capa', 'calzada', 'carril', 'estado', 'planta', 'material', 'formula', 'firmas' => ['area']]);
        if ($fecha == null) {
            $consulta = $consulta->where('fecha_solicitud', DB::raw("convert(date,getdate())"));
        } else {
            $consulta = $consulta->where('Wb_Solicitud_Liberaciones.fecha_solicitud', DB::raw("convert(date,'" . $request->fecha . "')"));
        }
        //
        //$consulta = $this->filtrar($request, $consulta, 'Wb_Solicitud_Liberaciones');
        return $this->handleResponse($request, $this->WbSolicitudLiberacionToArray($consulta->get()), __('messages.consultado'));
    }

    public function getV3(Request $request, $fecha = null)
    {
        $consulta = wbSolicitudLiberaciones::with([
            'capa' => function ($info) {
                $info->select('id_tipo_capa', 'Descripcion');
            },
            'calzada' => function ($info) {
                $info->select('id_tipo_calzada', 'Descripcion');
            },
            'carril' => function ($info) {
                $info->select('id_tipo_carril', 'Descripcion');
            },
            'estado' => function ($info) {
                $info->select('id_estados', 'descripcion_estado');
            },
            'planta' => function ($info) {
                $info->select('id_plata', 'NombrePlanta');
            },
            'material' => function ($info) {
                $info->select('id_material_lista', 'Nombre');
            },
            'formula' => function ($info) {
                $info->select('id_formula_lista', 'Nombre');
            },
            'firmas' => function ($info) {
                $info->with([
                    'area' => function ($sub) {
                        $sub->select('id_area', 'Area');
                    }
                ])->select(
                        'id_solicitudes_liberaciones_firmas',
                        'fk_id_solicitudes_liberaciones',
                        'fk_id_area',
                        'estado',
                        'fk_id_usuario',
                        'nota',
                        //'panoramica'
                    );
            },
            'lib_actividad' => function ($info) {
                $info->with([
                    'responsable' => function ($sub) {
                        $sub->select('id_liberacion_responsable', 'fk_id_liberaciones_actividades', 'fk_id_area', 'estado');
                    },
                    'actividad' => function ($sub) {
                        $sub->select('id_liberaciones_actividades', 'nombre', 'criterios', 'imagen', 'nivel_tolerancia', 'ExcentoNC');
                    }
                ])->select(
                        'id_solicitud_liberaciones_act',
                        'fk_id_solicitud_liberaciones',
                        'fk_id_liberaciones_actividades',
                        'calificacion',
                        'estado',
                        'nota'
                    );
            }
        ])->select(
                'id_solicitud_liberaciones',
                'fk_id_usuarios',
                'fk_id_tramo',
                'fk_id_hito',
                'fk_id_tipo_capa',
                'fk_id_tipo_calzada',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'notaUsuario',
                'espesor',
                'ubicacion',
                'fk_id_estados',
                'date_create',
                'Fecha_solicitud',
                //'foto'
            );

        if ($request->has('id') && $request->id != null) {
            $consulta = $consulta->where('id_solicitud_liberaciones', $request->id);
        } else {
            if ($fecha == null) {
                $consulta = $consulta->where('fecha_solicitud', DB::raw("convert(date,getdate())")); //
            } else {
                $consulta = $consulta->where('Wb_Solicitud_Liberaciones.fecha_solicitud', DB::raw("convert(date,'" . $request->fecha . "')"));
            }
        }
        
        //
        //$consulta = $this->filtrar($request, $consulta, 'Wb_Solicitud_Liberaciones');
        //return $this->handleResponse($request, $consulta->get(), __('messages.consultado'));
        return $this->handleResponse($request, $this->WbSolicitudLiberacionToArray($consulta->get()), __('messages.consultado'));
    }

}
