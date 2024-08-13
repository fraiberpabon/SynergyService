<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbHitos;
use App\Models\WbHitosAbcisas;
use App\Models\WbTipoCalzada;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbHitosAbcisasController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        if (!$req->json()->has('hito')) {
            return $this->handleAlert('Falta campo hitos.', false);
        }
        if (!$req->json()->has('inicio')) {
            return $this->handleAlert('Falta campo inicio.', false);
        }
        if (!$req->json()->has('fin')) {
            return $this->handleAlert('Falta campo fin.', false);
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.', false);
        }
        if (
            $req->validate([
                'hito' => 'string|max:10',
                'inicio' => 'string|max:6',
                'fin' => 'string|max:6',
                'estado' => 'string|max:2',
                'coordenadaInicial' => 'string',
                'coordenadaFinal' => 'string',
            ])
        ) {
            if (WbHitos::where('Id_Hitos', $req->hito)->first() == null) {
                return $this->handleAlert('Hito no encontrado.', false);
            }
            if ($req->inicio > $req->fin) {
                return $this->handleResponse($req, [], 'La abscisa inicial no puede ser mayor o Igual a la Final.');
            }
            $modeloRegistrar = new WbHitosAbcisas;
            $modeloRegistrar->fk_id_Hitos = $req->hito;
            $modeloRegistrar->Inicio = $req->inicio;
            $modeloRegistrar->Fin = $req->fin;
            $modeloRegistrar->Estado = $req->estado;
            $modeloRegistrar->coordenada_inicial = $req->coordenadaInicial;
            $modeloRegistrar->coordenada_final = $req->coordenadaFinal;
            if ($req->json()->has('tipoCalzada') && $req->tipoCalzada != 'null') {
                if (WbTipoCalzada::find($req->tipoCalzada) == null) {
                    return $this->handleAlert('Tipo de calzada no encontrado.', false);
                }
                $modeloRegistrar->fk_id_tipo_calzada = $req->tipoCalzada;
            }
            $modeloRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if ($modeloRegistrar->save()) {
                    $modeloRegistrar->Id_hitos_abscisas = $modeloRegistrar->latest('Id_hitos_abscisas')->first()->Id_hitos_abscisas;
                    return $this->handleResponse($req, $modeloRegistrar, 'Hito abcisa registrado.');
                }
            } catch (Exception $exc) {
            }
            return $this->handleAlert('Hito abcisa no registrado.', false);
        }
    }

    public function update(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Hito abcisa no valida.');
        }
        if (!$req->json()->has('inicio')) {
            return $this->handleAlert('Falta campo inicio.');
        }
        if (!$req->json()->has('fin')) {
            return $this->handleAlert('Falta campo fin.');
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if (
            $req->validate([
                'inicio' => 'string|max:6',
                'fin' => 'string|max:6',
                'estado' => 'string|max:2',
                'coordenadaInicial' => 'string',
                'coordenadaFinal' => 'string',
            ])
        ) {
            if ($req->inicio > $req->fin) {
                return $this->handleResponse($req, [], 'La abscisa inicial no puede ser mayor o Igual a la Final.');
            }
            $modeloModificar = WbHitosAbcisas::find($id);
            if ($modeloModificar == null) {
                return $this->handleAlert('Hito abcisa no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Hito abcisa no valido.');
            }
            if ($req->json()->has('calzada') && $req->calzada != 0) {
                if (WbTipoCalzada::find($req->calzada) == null) {
                    return $this->handleAlert('Tipo de calzada no encontrado.');
                }
                $modeloModificar->fk_id_tipo_calzada = $req->calzada;
            }
            $modeloModificar->Inicio = $req->inicio;
            $modeloModificar->Fin = $req->fin;
            $modeloModificar->Estado = $req->estado;
            $modeloModificar->coordenada_inicial = $req->coordenadaInicial;
            $modeloModificar->coordenada_final = $req->coordenadaFinal;
            $modeloModificar->userCreator = $this->traitGetIdUsuarioToken($req);
            try {
                if ($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Hito abcisa modificado.');
                }
            } catch (Exception $exc) {

            }
            return $this->handleAlert('Hito abcisa no modificado.');
        }
    }

    public function getByHito(Request $req, $id)
    {
        $consulta = WbHitosAbcisas::select(
            'Wb_Hitos_Abscisas.Id_hitos_abscisas as Id',
            'fk_id_Hitos as fk_Hitos',
            'Inicio',
            'Fin',
            DB::raw("CONVERT(varchar,Wb_Hitos_Abscisas.DateCreate,22) as [DateCreate]"),
            'Wb_Hitos_Abscisas.Estado',
            'Wb_Tipo_Calzada.Descripcion as Calzada',
            DB::raw("'K'+SUBSTRING(Inicio, 1, 2) +'+'+ SUBSTRING(Inicio,3 , 5)  AS convInicio"),
            DB::raw("'K'+SUBSTRING([Fin], 1, 2) +'+'+ SUBSTRING([Fin],3 , 5)  AS convFin"),
            'coordenada_inicial',
            'coordenada_final'
        )->leftjoin('Wb_Tipo_Calzada', 'Wb_Tipo_Calzada.id_tipo_calzada', '=', 'Wb_Hitos_Abscisas.fk_id_tipo_calzada')
            ->where('Wb_Hitos_Abscisas.fk_id_Hitos', $id);

        if ($req->has('estado')) {
            $consulta = $consulta->where('Wb_Hitos_Abscisas.Estado', $req->estado);
        }
        return $this->handleResponse($req, $this->wbHitosAbcisaToArray($consulta->get()), __("messages.consultado"));
    }

    public function getParaSync(Request $request)
    {
        $consulta = WbHitosAbcisas::select(
            'Wb_Hitos_Abscisas.fk_id_Hitos AS  HITO',
            'INICIO',
            'FIN',
            'fk_id_Tramo AS  TRAMO',
            'Wb_Hitos_Abscisas.ESTADO'
        )->leftJoin('Wb_Tramos_Hitos_Asign as TR', 'TR.fk_id_Hitos', 'Wb_Hitos_Abscisas.fk_id_Hitos')
            ->leftJoin('Wb_Tramos as T', 'T.Id_Tramo', 'TR.fk_id_Tramo')
            ->leftJoin('Wb_Hitos as H', 'H.Id_Hitos', 'TR.fk_id_Hitos')
            ->where('T.Estado', 'A')
            ->where('H.Estado', 'A')
            ->where('tr.Estado', 'A')
            ->where('Wb_Hitos_Abscisas.Estado', 'A');
        $consulta = $this->filtrarPorProyecto($request, $consulta, 'Wb_Hitos_Abscisas')->get();
        return $this->handleResponse($request, $consulta, 'Consultado');
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