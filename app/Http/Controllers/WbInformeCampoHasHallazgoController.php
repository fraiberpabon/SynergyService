<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\wbInformeCampoHazHallazgo;
use Illuminate\Http\Request;

class WbInformeCampoHasHallazgoController extends BaseController implements Vervos
{

    /**
     * @param Request $req
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
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
        // TODO: Implement get() method.
    }

    public function getByInformeHallazgo(Request $request, $id)
    {
        // TODO: Implement get() method.
        if (!is_numeric($id)) {
            return $this->handleAlert(__('messages.informe_de_hallazgo_no_valido'));
        }
        $consulta = wbInformeCampoHazHallazgo::select(
            'Wb_informe_campo_has_hallazgo.*',
            'Wb_hallazgo.nombre as nombreHallazgo'
        )
            ->leftJoin('Wb_hallazgo', 'Wb_hallazgo.id_hallazgo', 'Wb_informe_campo_has_hallazgo.fk_id_hallazgo')
            ->where('fk_id_informe_campo', $id);
        $consulta = $this->filtrar($request, $consulta, 'Wb_informe_campo_has_hallazgo')->get();
        return $this->handleResponse($request, $this->informeHallazgoHasHallazgoToArray($consulta), __('messages.consultado'));
    }


    public function getBy(Request $request, $id)
    {
        $consulta = wbInformeCampoHazHallazgo::with('Hallazgos')->where('fk_id_informe_campo', $id);
        return json_encode($consulta->get());
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
