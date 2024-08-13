<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\UsuPlanta;
use App\Models\WbCentroProduccionHitos;
use App\Models\WbHitos;
use DateTime;
use Exception;
use Illuminate\Http\Request;

class WbCentroProduccionHitosController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('planta')) {
            return $this->handleAlert('Falta campo fk_id_planta.');
        }
        if(!$req->json()->has('hito')) {
            return $this->handleAlert('Falta campo fk_id_Hito.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo Estado.');
        }
        if($req->validate([
            'planta' => 'required|numeric',
            'hito' => 'required',
            'estado' => 'required|string',
        ])) {
            if(UsuPlanta::find($req->planta) == null) {
                return $this->handleAlert('Planta no encontrada.');
            }
            if(WbHitos::where('Id_Hitos', $req->hito)->count() == 0) {
                return $this->handleAlert('Hito no encontrado.');
            }
            $modeloRegistrar = new WbCentroProduccionHitos();
            $modeloRegistrar->fk_id_planta = $req->planta;
            $modeloRegistrar->fk_id_Hito = $req->hito;
            $modeloRegistrar->Estado = $req->estado;
            $modeloRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->id_centroProduccion_hito = $modeloRegistrar->latest('id_centroProduccion_hito')->first()->id_centroProduccion_hito;
                    return $this->handleResponse($req, $modeloRegistrar, 'Centro de produccion hito registrado.');
                }
            } catch(Exception $exc) { }
            return $this->handleAlert('Centro de produccion hito no registrado.');
        }
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Centro de produccio no valido.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if($req->validate([
            'estado' => 'string',
        ])) {
            $modeloModificar = WbCentroProduccionHitos::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert('Centro de produccion hito no encontrada.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Centro de produccion hito no valido.');
            }
            $datetime_variable = new DateTime();
            $datetime_formatted = date_format($datetime_variable, 'd/m/Y H:i:s');
            $modeloModificar->Estado = $req->estado;
            $modeloModificar->userCreator = $this->traitGetIdUsuarioToken($req);
            if (!($req->estado == 'A' || $req->estado == 'I')) {
                return $this->handleAlert('Estado no valido.');
            }
            switch($req->estado) {
                case 'A':
                    $modeloModificar->dateUpdate = $datetime_formatted;
                    break;
                case 'I':
                    $modeloModificar->dateClose = $datetime_formatted;
                    break;
            }
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Centro de produccion hito modificado.');
                }
            } catch(Exception $exc) { }
            return $this->handleAlert('Centro de produccion hito no modificado.');
        }
    }

    public function getByPlanta(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleResponse($req, [], 'Consultado.');
        }
        $consulta = WbCentroProduccionHitos::select()
        ->where('fk_id_planta', $id);
        $consulta = $this->filtrar($req, $consulta, 'Wb_CentroProduccion_Hitos')->get();
        $hitos = WbHitos::all();
        foreach ($consulta as $item) {
            $this->setHitoById($item, $hitos);
        }
        return $this->handleResponse($req, $this->wbCentroProduccionHitoToArray($consulta), __("messages.consultado"));
    }

    public function setHitoById($model, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($model->fk_id_Hito == $array[$i]->Id_Hitos) {
                $reescribir = $this->wbHitosToModel($array[$i]);
                $model->objectHito = $reescribir;
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

    /**
     * @return void
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
