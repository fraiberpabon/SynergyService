<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\EstructuraTipoElemento;
use Exception;
use Illuminate\Http\Request;

class EstructuraTipoElementoController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('elemento')) {
            return $this->handleAlert('Ingrese el elemento.');
        }
        try {
            $proyecto = $this->traitGetProyectoCabecera($req);
            if (EstructuraTipoElemento::where('Elemento', $req->elemento)->where('fk_id_project_Company', $proyecto)->first() != null) {
                return $this->handleAlert('Ya existe un elemento con el mismo nombre.');
            }
            $estructuraTipoElemento = new EstructuraTipoElemento;
            $estructuraTipoElemento->Elemento = $req->elemento;
            $estructuraTipoElemento->estado = 1;//colocar por defualt en la base de datos
            $estructuraTipoElemento = $this->traitSetProyectoYCompania($req, $estructuraTipoElemento);
            $estructuraTipoElemento->save();
            return $this->handleResponse($req, $estructuraTipoElemento, 'Estructura tipo elemento guardada en el sistema.');
        } catch(Exception $exc) { }
        return $this->handleAlert('No se pudo guardar la estructura tipo elemento en el sistema.');
    }


    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {
        $consulta = EstructuraTipoElemento::select();
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->estructuraTipoElementoToArray($consulta), __("messages.consultado"));
    }



    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
