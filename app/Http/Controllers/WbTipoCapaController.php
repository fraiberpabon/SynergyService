<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbTipoCapa;
use Exception;
use Illuminate\Http\Request;

class WbTipoCapaController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        if (!$req->json()->has('descripcion')) {
            return $this->handleAlert('Falta campo Descripcion.', false);
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo Estado.', false);
        }
        if (
            $req->validate([
                'descripcion' => 'required|string',
                'estado' => 'required'
            ])
        ) {
            $tipoCapaRegistrar = new WbTipoCapa;
            $tipoCapaRegistrar->Descripcion = $req->descripcion;
            $tipoCapaRegistrar->Estado = $req->estado;
            $tipoCapaRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
            $tipoCapaRegistrar = $this->traitSetProyectoYCompania($req, $tipoCapaRegistrar);
            try {
                $tipoCapaRegistrar->save();
                $tipoCapaRegistrar->id_tipo_capa = $tipoCapaRegistrar->latest('id_tipo_capa')->first()->id_tipo_capa;
                return $this->handleResponse($req, [], 'Tipo de capa registrada.');
            } catch (Exception $exc) {
                return $this->handleAlert('Tipo de capa no registrada.', false);
            }
        }
    }

    public function update(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Tipo capa no valido.');
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo Estado.');
        }
        if (
            $req->validate([
                'estado' => 'required',
            ])
        ) {
            $tipoCapa = WbTipoCapa::find($id);
            if ($tipoCapa != null) {
                $proyecto = $this->traitGetProyectoCabecera($req);
                if ($tipoCapa->fk_id_project_Company != $proyecto) {
                    return $this->handleAlert('Tipo de capa no valido.');
                }
                $tipoCapa->Estado = $req->estado;
                $tipoCapa->userCreator = $this->traitGetIdUsuarioToken($req);
                try {
                    $tipoCapa->save();
                    return $this->handleResponse($req, [], 'Tipo de capa actualizado.');
                } catch (Exception $exc) {
                    return $this->handleAlert('No se pudo modificar el tipo de capa.');
                }
            } else {
                return $this->handleAlert('Tipo de capa no encontrado.');
            }
        }
    }

    public function get(Request $request)
    {
        try {
            $consulta = WbTipoCapa::select('*');
            $consulta = $this->filtrar($request, $consulta);
            if ($request->estado) {
                $consulta = $consulta->where('Estado', 'A');
            }
            $consulta = $consulta->get();
            return $this->handleResponse($request, $this->wbTipoDeCapaToArray($consulta), __("messages.consultado"));
        } catch (Exception $exc) {
            return $this->handleAlert('Ocurrio un error en la consulta.', false);
        }
    }

    public function getActivos(Request $request)
    {
        try {
            $consulta = WbTipoCapa::select('*');
            $consulta = $this->filtrar($request, $consulta)
                ->where('Estado', 'A')
                ->get();
            return $this->handleResponse($request, $this->wbTipoDeCapaToArray($consulta), __("messages.consultado"));
        } catch (Exception $exc) {
            return $this->handleAlert('Ocurrio un error en la consulta.', false);
        }
    }

    public function getActivosConActividad(Request $request)
    {
        $consulta = WbTipoCapa::select(
            'Wb_Tipo_Capa.id_tipo_capa',
            'Wb_Tipo_Capa.Descripcion',
            'Wb_Tipo_Capa.Estado',
            'Wb_Tipo_Capa.dateCreate',
            'Wb_Tipo_Capa.isAsfalto'
        )
            ->leftJoin('Wb_Liberaciones_Act_Capas', 'Wb_Tipo_Capa.id_tipo_capa', 'Wb_Liberaciones_Act_Capas.fk_tipo_capa')
            ->whereNotNull('Wb_Liberaciones_Act_Capas.fk_tipo_capa')
            ->where('Wb_Tipo_Capa.Estado', 'A')
            ->groupBy(
                'Wb_Tipo_Capa.id_tipo_capa',
                'Wb_Tipo_Capa.Descripcion',
                'Wb_Tipo_Capa.Estado',
                'Wb_Tipo_Capa.dateCreate',
                'Wb_Tipo_Capa.isAsfalto'
            );
        $consulta = $this->filtrar($request, $consulta, 'Wb_Tipo_Capa')->get();
        return $this->handleResponse($request, $this->wbTipoDeCapaToArray($consulta), __("messages.consultado"));
    }

    public function getActivosGeneral(Request $request)
    {
        try {
            $consulta = WbTipoCapa::select(
                'Wb_Tipo_Capa.id_tipo_capa',
                'Wb_Tipo_Capa.Descripcion',
                'Wb_Tipo_Capa.Estado',
                'Wb_Tipo_Capa.dateCreate',
                'Wb_Tipo_Capa.isAsfalto',
                \DB::raw('IIF(Wb_Liberaciones_Act_Capas.fk_tipo_capa IS NOT NULL, 1, 0) as is_actividad')
            )
                ->leftJoin('Wb_Liberaciones_Act_Capas', 'Wb_Tipo_Capa.id_tipo_capa', '=', 'Wb_Liberaciones_Act_Capas.fk_tipo_capa')
                ->where('Wb_Tipo_Capa.Estado', 'A')
                ->groupBy(
                    'Wb_Tipo_Capa.id_tipo_capa',
                    'Wb_Tipo_Capa.Descripcion',
                    'Wb_Tipo_Capa.Estado',
                    'Wb_Tipo_Capa.dateCreate',
                    'Wb_Tipo_Capa.isAsfalto',
                    'Wb_Liberaciones_Act_Capas.fk_tipo_capa',
                );

            $consulta = $this->filtrar($request, $consulta, 'Wb_Tipo_Capa')->get();

            return $this->handleResponse($request, $this->wbTipoDeCapaToArray($consulta), __("messages.consultado"));
        } catch (Exception $exc) {
            return $this->handleAlert('Ocurrio un error en la consulta.', false);
            //return $this->handleAlert($exc->getMessage(), false);
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

    public function find($id)
    {
        return WbTipoCapa::find($id);
    }
}
