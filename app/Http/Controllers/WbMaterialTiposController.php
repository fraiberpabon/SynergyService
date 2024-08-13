<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbMaterialTipos;
use Exception;
use Illuminate\Http\Request;

class WbMaterialTiposController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('tipoDescripcion')) {
            return $this->handleAlert('Falta campo tipoDescripcion.', false);
        }
        if(!$req->json()->has('compuesto')) {
            return $this->handleAlert('Falta campo Compuesto.', false);
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo Estado.', false);
        }
        if($req->validate([
            'tipoDescripcion' => 'required',
            'compuesto' => 'required',
            'estado' => 'required',
        ])) {
            $materialTipos = new WbMaterialTipos;
            $materialTipos->tipoDescripcion = $req->tipoDescripcion;
            $materialTipos->Compuesto = $req->compuesto;
            $materialTipos->Estado = $req->estado;
            $materialTipos->userCreator = $this->traitGetIdUsuarioToken($req);
            $materialTipos = $this->traitSetProyectoYCompania($req, $materialTipos);
            try {
                $materialTipos->save();
                return $this->handleResponse($req, $materialTipos, 'Tipo de material regisrado.');
            } catch(Exception $exc) {
                return $this->handleAlert('El tipo de material no pudo ser registrado.', false);
            }
        }
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Material tipo no valido.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo Estado.');
        }
        if($req->validate([
            'estado' => 'string',
        ])) {
            $materialTipos = WbMaterialTipos::find($id);
            if($materialTipos != null) {
                $proyecto = $this->traitGetProyectoCabecera($req);
                if ($materialTipos->fk_id_project_company != $proyecto) {
                    return $this->handleAlert('Tipo de material no valido.');
                }
                $materialTipos->Estado = $req->estado;
                $materialTipos->userCreator = $this->traitGetIdUsuarioToken($req);
                try {
                    if ($materialTipos->save()) {
                        return $this->handleResponse($req, $materialTipos, 'Tipo de material modificado.');
                    }
                } catch(Exception $exc) {
                }
                return $this->handleAlert('Tipo de material no pudo ser modificado.');
            } else {
                return $this->handleAlert('Tipo de material no encontrado.');
            }
        }
    }

    public function get(Request $request) {
        try {
            $consulta = WbMaterialTipos::select();
            if ($request->estado && $request->estado != 'null') {
                $consulta = $consulta->where('Estado', 'A');
            }
            $consulta = $this->filtrar2($request, $consulta)->get();
            return $this->handleResponse($request, $this->wbMaterialTipoToArray($consulta), '¡Consultado!');
        } catch(Exception $exc) {
            return $this->handleAlert('Ocurrio un error en la consulta.', false);
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

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
