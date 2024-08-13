<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbMaterialCapa;
use App\Models\WbMaterialCentroProduccion;
use App\Models\WbMaterialLista;
use App\Models\WbMaterialTipos;
use App\Models\WbTipoCapa;
use Exception;
use Illuminate\Http\Request;

class WbMaterialListaController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        if (!$req->json()->has('materialLista')) {
            return $this->handleAlert('Falta campo materialLista.');
        }
        if (!$req->json()->has('capas')) {
            return $this->handleAlert('Falta campo capas.');
        }
        if (!$req->json()->has('centrosProduccion')) {
            return $this->handleAlert('Falta campo centrosProduccion.');
        }
        if (!is_array($req->capas)) {
            return $this->handleAlert('Capas no validas.');
        }
        if (!is_array($req->centrosProduccion)) {
            return $this->handleAlert('Centros de produccion no valido.');
        }
        foreach ($req->capas as $capa) {
            if (WbTipoCapa::find($capa) == null) {
                return $this->handleAlert('Tipo de capa no encontrado.');
            }
        }
        foreach ($req->centrosProduccion as $centroProduccion) {
            if (UsuPlanta::find($centroProduccion) == null) {
                return $this->handleAlert('Planta no encontrada.');
            }
        }
        if (WbMaterialTipos::find($req->materialLista['materialTipo']) == null) {
            return $this->handleAlert('Material tipo no encontrado.');
        }
        if (!($req->materialLista['estado'] == 'A' || $req->materialLista['estado'] == 'I')) {
            return $this->handleAlert('Estado no valido.');
        }
        if (!($req->materialLista['solicitable'] == 'S' || $req->materialLista['solicitable'] == 'N')) {
            return $this->handleAlert('Valor de solicitable no valido.');
        }
        $materialListaRegistrar = new WbMaterialLista;
        $materialListaRegistrar->Nombre = $req->materialLista['nombre'];
        $materialListaRegistrar->Descripcion = $req->materialLista['descripcion'];
        $materialListaRegistrar->unidadMedida = $req->materialLista['unidadMedida'];
        $materialListaRegistrar->fk_id_material_tipo = $req->materialLista['materialTipo'];
        $materialListaRegistrar->Estado = $req->materialLista['estado'];
        $materialListaRegistrar->Solicitable = $req->materialLista['solicitable'];
        $materialListaRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
        $materialListaRegistrar = $this->traitSetProyectoYCompania($req, $materialListaRegistrar);
        try {
            if ($materialListaRegistrar->save()) {
                $materialListaRegistrar->id_material_lista = $materialListaRegistrar->latest('id_material_lista')->first()->id_material_lista;
                foreach ($req->capas as $capa) {
                    $materialCapa = new WbMaterialCapa;
                    $materialCapa->fk_id_tipo_capa = $capa;
                    $materialCapa->fk_id_material_lista = $materialListaRegistrar->id_material_lista;
                    $materialCapa->Estado = 'A';
                    $materialCapa->userCreator = $this->traitGetIdUsuarioToken($req);
                    $materialCapa->save();
                }
                foreach ($req->centrosProduccion as $centroProduccion) {
                    $centroProduccionRegistrar = new WbMaterialCentroProduccion;
                    $centroProduccionRegistrar->fk_id_material_lista = $materialListaRegistrar->id_material_lista;
                    $centroProduccionRegistrar->fk_id_planta = $centroProduccion;
                    $centroProduccionRegistrar->Estado = 'A';
                    $centroProduccionRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
                    $centroProduccionRegistrar->save();
                }
                return $this->handleResponse($req, $materialListaRegistrar, 'Tipo de material desactivado.');
            }
        } catch (Exception $exc) {
            return $this->handleAlert('Tipo de material no encontrado.', false);
        }
    }

    public function update(Request $req, $id)
    {
        $isModificable = false;
        if (!is_numeric($id)) {
            return $this->handleAlert('Material no valido.');
        }
        if (!$req->json()->has('estado')) {
            $isModificable = true;
        }
        if (!$req->json()->has('solicitable')) {
            $isModificable = true;
        }
        if (!$req->json()->has('userCreator')) {
            $isModificable = true;
            return $this->handleAlert('Falta campo userCreator.');
        }
        if (!$req->json()->has('id_material_lista')) {
            return $this->handleAlert('Falta campo id_material_lista.');
        }
        if (
            $req->validate([
                'estado' => 'numeric',
                'solicitable' => 'string',
                'userCreator' => 'required',
            ]) && $isModificable
        ) {
            if (usuarios_M::find($req->userCreator) == null) {
                return $this->handleAlert('Usuario no encontrado.');
            }
            $materiaListaModificar = WbMaterialLista::find($id);
            if ($materiaListaModificar == null) {
                return $this->handleAlert('Materia lista no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($materiaListaModificar->fk_id_project_company != $proyecto) {
                return $this->handleAlert('Materia lista no valido.');
            }
            $modificado = false;
            if ($req->json()->has('estado')) {
                $modificado = true;
                $materiaListaModificar->Estado = $req->estado;
            }
            if ($req->json()->has('solicitable')) {
                $modificado = true;
                $materiaListaModificar->Solicitable = $req->solicitable;
            }
            $materiaListaModificar->userCreator = $req->userCreator;
            try {
                if ($modificado && $materiaListaModificar->save()) {
                    return $this->handleResponse($req, $materiaListaModificar, 'Materia lista modificada.');
                } else {
                    return $this->handleAlert('Materia lista no modificada.');
                }
            } catch (Exception $exc) {

            }
        }
        return $this->handleAlert('Materia lista no modificada.');
    }

    public function activar(Request $req, $id)
    {
        return $this->activarDesactivar($req, $id, 'A');
    }

    public function desactivar(Request $req, $id)
    {
        return $this->activarDesactivar($req, $id, 'I');
    }

    private function activarDesactivar(Request $req, $id, $estado)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Material no valido.');
        }
        $materiaListaModificar = WbMaterialLista::find($id);
        if ($materiaListaModificar == null) {
            return $this->handleAlert('Materia lista no encontrado.');
        }
        $proyecto = $this->traitGetProyectoCabecera($req);
        if ($materiaListaModificar->fk_id_project_company != $proyecto) {
            return $this->handleAlert('Materia lista no valido proro.');
        }
        if (!($estado == 'A' || $estado == 'I')) {
            return $this->handleAlert('Estado no valido.');
        }
        $materiaListaModificar->Estado = $estado;
        $materiaListaModificar->userCreator = $this->traitGetIdUsuarioToken($req);
        try {
            if ($materiaListaModificar->save()) {
                return $this->handleResponse($req, $materiaListaModificar, 'Materia lista modificada.');
            }
        } catch (Exception $exc) {
            return $this->handleAlert('Materia lista no modificada.');
        }
    }

    public function solicitar(Request $req, $id)
    {
        return $this->solicitarNosolicitar($req, $id, 'S');
    }

    public function noSolicitar(Request $req, $id)
    {
        return $this->solicitarNosolicitar($req, $id, 'N');
    }

    private function solicitarNosolicitar(Request $req, $id, $estado)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Material no valido.');
        }
        $materiaListaModificar = WbMaterialLista::find($id);
        if ($materiaListaModificar == null) {
            return $this->handleAlert('Materia lista no encontrado.');
        }
        $proyecto = $this->traitGetProyectoCabecera($req);
        if ($materiaListaModificar->fk_id_project_company != $proyecto) {
            return $this->handleAlert('Materia lista no valido proro.');
        }
        if (!($estado == 'S' || $estado == 'N')) {
            return $this->handleAlert('Estado no valido.');
        }
        $materiaListaModificar->Solicitable = $estado;
        $materiaListaModificar->userCreator = $this->traitGetIdUsuarioToken($req);
        try {
            if ($materiaListaModificar->save()) {
                return $this->handleResponse($req, $materiaListaModificar, 'Materia lista modificada.');
            }
        } catch (Exception $exc) {
            return $this->handleAlert('Materia lista no modificada.');
        }
    }

    public function get(Request $request)
    {
        $response = WbMaterialLista::select();
        $response = $this->filtrar($request, $response)->get();
        $materialesTipo = WbMaterialTipos::all();
        $ususPlanta = UsuPlanta::all();
        $tiposCapa = WbTipoCapa::all();
        foreach ($response as $item) {
            $this->setMaterialTipoById($item, $materialesTipo);
            $capasPorMaterialLista = WbMaterialCapa::where('fk_id_material_lista', $item->id_material_lista)->where('Estado', 'A')->get();
            foreach ($capasPorMaterialLista as $capa) {
                $this->setTipoCapaById($capa, $tiposCapa);
            }
            $item->capas = $this->wbMaterialCapaToArray($capasPorMaterialLista);
            $materialCentroProduccionByMaterialLista = WbMaterialCentroProduccion::where('fk_id_material_lista', $item->id_material_lista)->where('Estado', 'A')->get();
            foreach ($materialCentroProduccionByMaterialLista as $materialCentroProduccion) {
                $this->setUsuPlantaById($materialCentroProduccion, $ususPlanta);
            }
            $item->centrosProduccion = $this->wbMaterialCentroProduccionToArray($materialCentroProduccionByMaterialLista);
        }
        return $this->handleResponse($request, $this->wbMaterialListaToArray($response), __("messages.consultado"));
    }

    public function setMaterialTipoById($model, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($model->fk_id_material_tipo == $array[$i]->id_material_tipo) {
                $reescribir = $this->wbMaterialTipoToModel($array[$i]);
                $model->objectMaterialTipo = $reescribir;
                break;
            }
        }
    }

    public function setTipoCapaById($model, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($model->fk_id_tipo_capa == $array[$i]->id_tipo_capa) {
                $reescribir = $this->wbTipoCapaToModel($array[$i]);
                $model->objectTipoCapa = $reescribir;
                break;
            }
        }
    }

    public function setUsuPlantaById($model, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($model->fk_id_planta == $array[$i]->id_plata) {
                $reescribir = $this->usuPlantaToModel($array[$i]);
                $model->objectUsuPlanta = $reescribir;
                break;
            }
        }
    }


    /**
     * @param $id
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getListaMaterialParaFormulario(Request $request)
    {
        $response = WbMaterialLista::where('Estado', 'A');
        $response = $this->filtrar($request, $response)->get();
        if (sizeof($response) == 0) {
            return $this->handleAlert(__("messages.sin_registros_por_mostrar"), false);
        }
        return $this->handleResponse($request, $this->wbMaterialListaToArray($response), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        return WbMaterialLista::where('estado', 'A')->where('fk_id_project_Company', $proyecto)->get();
    }

    public function find($id)
    {
        return WbMaterialLista::find($id);
    }
}
