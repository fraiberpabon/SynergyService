<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbHitos;
use App\Models\WbTipoVia;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbHitosController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('idHitos')) {
            return $this->handleAlert(__("messages.consultado"));
        }
        if(!$req->json()->has('descripcion')) {
            return $this->handleAlert(__("messages.falta_campo_descripcion"));
        }
        if(!$req->json()->has('apuntador')) {
            return $this->handleAlert(__("messages.falta_campo_apuntador"));
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert(__("messages.falta_campo_estado"));
        }
        if(!$req->json()->has('via')) {
            return $this->handleAlert(__("messages.falta_campo_via"));
        }
        if($req->validate([
            'idHitos' => 'string|max:10',
            'descripcion' => 'string|max:40',
            'apuntador' => 'string|max:1',
            'estado' => 'string|max:1',
            'via' => '',
        ])) {
            $proyecto = $this->traitGetProyectoCabecera($req);
            if($req->tipoVia != 'null' && WbTipoVia::find($req->via) == null) {
                return $this->handleAlert(__("messages.tipo_de_via_no_encontrada"));
            }
            if(WbHitos::where('Id_Hitos', $req->idHitos)->where('fk_id_project_Company', $proyecto)->first() != null) {
                return $this->handleAlert(__("messages.el_id_hito_ya_se_encuentra_registrado"));
            }
            $usuario = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar = new WbHitos;
            $modeloRegistrar->Id_Hitos = $req->idHitos;
            $modeloRegistrar->Descripcion = $req->descripcion;
            $modeloRegistrar->Apuntador = $req->apuntador;
            $modeloRegistrar->Estado = $req->estado;
            if($req->via != 'null') {
                $modeloRegistrar->fk_id_tipo_via = $req->via;
            }
            $modeloRegistrar->userCreator = $usuario;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->Id = $modeloRegistrar->latest('Id')->first()->Id;
                    return $this->handleResponse($req, $modeloRegistrar, __("messages.hito_registrado"));
                }
            } catch(Exception $exc) {
            }
            return $this->handleAlert(__("messages.hito_no_registrado"));
        }
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Hito no valido.');
        }
        if(!$req->json()->has('hito')) {
            return $this->handleAlert('Falta campo hitos.');
        }
        if(!$req->json()->has('descripcion')) {
            return $this->handleAlert('Falta campo descripcion.');
        }
        if(!$req->json()->has('apuntador')) {
            return $this->handleAlert('Falta campo apuntador.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if (!($req->estado == 'I' || $req->estado == 'A')) {
            return $this->handleAlert('Estado no valido.');
        }
        if($req->validate([
            'hito' => 'alpha_num:ascii',
            'descripcion' => 'string',
            'apuntador' => 'string',
            'estado' => 'string',
        ])) {
            $modeloModificar = WbHitos::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert('Hito no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Hito no valido.');
            }
            if($modeloModificar->Id_Hitos != $req->hitos) {
                if(WbHitos::where('Id_Hitos', $req->hitos)->where('fk_id_project_Company', $modeloModificar->fk_id_project_Company)->where('Id', '!=', $id)->first() != null) {
                    return $this->handleAlert('Este hito ya se encuentra registrado.');
                }
            }
            $modeloModificar->Id_Hitos = $req->hito;
            $modeloModificar->Descripcion = $req->descripcion;
            $modeloModificar->Apuntador = $req->apuntador;
            $modeloModificar->Estado = $req->estado;
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, [], 'Hito modificado.');
                }
            } catch(Exception $exc){ }
            return $this->handleAlert('Hito no modificado.', false);
        }
    }

    public function getSinAsignacionAnterior(Request $request) {
        $consulta = WbHitos::select(
            'Id as id',
            'Id_Hitos as hito',
            'Descripcion as descripcion',
            'Apuntador as apuntador',
            'Estado as estado',
            'DateCreated as fechaRegistro'
        )
            ->whereNotExists(function($query)
            {
                $query->select(DB::raw('null'))
                    ->from('Wb_Tramos_Hitos_Asign')
                    ->whereRaw("Wb_Tramos_Hitos_Asign.fk_id_Hitos = Wb_Hitos.Id_Hitos  and estado = 'A'");
            })
            ->where('Estado', 'A')
            ->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function get(Request $request) {

        $consulta = WbHitos::select(
            'Id',
            'Id_Hitos',
            'Wb_Hitos.Descripcion',
            'Apuntador',
            'Wb_Tipo_Via.Descripcion as via',
            'Wb_Hitos.Estado',
            DB::raw("CONVERT(varchar,DateCreated,22) as DateCreated")
        )->leftjoin('Wb_Tipo_Via', 'Wb_Tipo_Via.id_tipo_via', '=', 'Wb_Hitos.fk_id_tipo_via');
        $consulta = $this->filtrar($request, $consulta, 'Wb_Hitos')->get();
        return $this->handleResponse($request, $consulta->map(function($data) {
                                        return [
                                            'identificador' => $data['Id'],
                                            'hito' => $data['Id_Hitos'],
                                            'descripcion' => $data['Descripcion'],
                                            'apuntador' => $data['Apuntador'],
                                            'estado' => $data['Estado'],
                                            'fechaRegistro' => $data['DateCreated'],
                                            'via' => $data['via'],
                                        ];
                                    }), __("messages.consultado"));
    }

    public function getByHitos(Request $req, $id) {
        $consulta = WbHitos::select(
            'Id',
            'Id_Hitos',
            'Descripcion',
            'Apuntador',
            'Estado',
            DB::raw("CONVERT(varchar,DateCreated,22) as DateCreated")
        )->where ('Id_Hitos', $id)
            ->where('Estado', 'A')
        ->get();
        return $this->handleResponse($req, $this->wbHitosToArray($consulta), __("messages.consultado"));
    }

    public function getByTramos(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Tramo no valido.', false);
        }
        $consulta = WbHitos::select(
            'Id',
            'Id_Hitos',
            'Descripcion',
            'Apuntador',
            'Wb_Hitos.Estado',
            DB::raw("CONVERT(varchar,DateCreated,22) as DateCreated")
        )->leftjoin('Wb_Tramos_Hitos_Asign', 'Wb_Tramos_Hitos_Asign.fk_id_Hitos', '=', 'Wb_Hitos.Id_Hitos')
        ->where ('Wb_Tramos_Hitos_Asign.fk_id_Tramo', $id)
        ->get();
        return $this->handleResponse($req, $this->wbHitosToArray($consulta), __("messages.consultado"));
    }

    public function getByTramosEncrypt(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Tramo no valido.', false);
        }
        $consulta = WbHitos::select(
            'Id',
            'Id_Hitos',
            'Descripcion',
            'Apuntador',
            'Wb_Hitos.Estado',
            DB::raw("CONVERT(varchar,DateCreated,22) as DateCreated")
        )->leftjoin('Wb_Tramos_Hitos_Asign', 'Wb_Tramos_Hitos_Asign.fk_id_Hitos', '=', 'Wb_Hitos.Id_Hitos')
            ->where ('Wb_Tramos_Hitos_Asign.fk_id_Tramo', $id)
            ->get();
        return $this->handleResponse($req, $this->wbHitosToArray($consulta), __("messages.consultado"));
    }

    /**
     * @param Request $req
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @deprecated usar getActivosByTramos
     */
    public function getActivosByTramosDeprecated(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Tramo no valido.', false);
        }
        $consulta = WbHitos::select(
            'Id',
            'Id_Hitos',
            'Descripcion',
            'Apuntador',
            'Wb_Hitos.Estado',
            DB::raw("CONVERT(varchar,DateCreated,22) as DateCreated")
        )->leftjoin('Wb_Tramos_Hitos_Asign', 'Wb_Tramos_Hitos_Asign.fk_id_Hitos', '=', 'Wb_Hitos.Id_Hitos')
            ->where ('Wb_Tramos_Hitos_Asign.fk_id_Tramo', $id)
            ->where ('Wb_Hitos.Estado', 'A')->get();

        return $this->handleResponse($req, $this->wbHitosToArray($consulta), __("messages.consultado"));
    }

    public function getActivosByTramos(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Tramo no valido.', false);
        }
        $consulta = WbHitos::select(
            'Id',
            'Id_Hitos',
            'Descripcion',
            'Apuntador',
            'Wb_Hitos.Estado',
            DB::raw("CONVERT(varchar,DateCreated,22) as DateCreated")
        )->leftjoin('Wb_Tramos_Hitos_Asign', 'Wb_Tramos_Hitos_Asign.fk_id_Hitos', '=', 'Wb_Hitos.Id_Hitos')
            ->where ('Wb_Tramos_Hitos_Asign.fk_id_Tramo', $id)
            ->where ('Wb_Hitos.Estado', 'A');
        $consulta = $this->filtrar($req, $consulta, 'Wb_Hitos')->get();
        return $this->handleResponse($req, $this->wbHitosToArray($consulta), __("messages.consultado"));
    }

    /**
     * @param $modelo
     * @return array
     */


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
