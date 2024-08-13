<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\CnfCostCenter;
use App\Models\Compania;
use App\Models\location;
use App\Models\Planta;
use App\Models\UsuPlanta;
use Exception;
use Illuminate\Http\Request;

class UsuPlantaController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        if (!$req->json()->has('ubicacion')) {
            return $this->handleAlert('Falta campo ubicacion.');
        }
        if (!$req->json()->has('tipoPlanta')) {
            return $this->handleAlert('Falta campo tipoPlanta.');
        }
        if (!$req->json()->has('compañia')) {
            return $this->handleAlert('Falta campo compañia.');
        }
        if (!$req->json()->has('nombre')) {
            return $this->handleAlert('Falta campo nombrePlanta.');
        }
        if (
            $req->validate([
                'ubicacion' => 'string',
                'tipoPlanta' => 'string',
                'compañia' => 'numeric',
                'nombrePlanta' => 'string',
                'centroDeCosto' => 'numeric',
                'descripcion' => 'string',
                'estado' => 'numeric',
                'planta' => 'numeric',
                'tipo' => 'string',
                'locationID' => 'string',
            ])
        ) {
            if (Compania::find($req->compañia) == null) {
                return $this->handleAlert('Compañia no encontrada.');
            }
            if (UsuPlanta::where('NombrePlanta', $req->nombrePlanta)->get()->count() > 0) {
                return $this->handleAlert('Este nombre de planta esta siendo usado por otra planta.');
            }
            $modeloRegistrar = new UsuPlanta;
            $modeloRegistrar->ubicacion = $req->ubicacion;
            $modeloRegistrar->tipoPlanta = $req->tipoPlanta;
            $modeloRegistrar->fk_compañia = $req->compañia;
            if ($req->json()->has('centroCosto')) {
                $modeloRegistrar->fk_id_centroCosto = $req->centroCosto;
            }
            if ($req->json()->has('descripcion')) {
                $modeloRegistrar->descripcion = $req->descripcion;
            }
            $modeloRegistrar->estado = 1;
            if ($req->json()->has('planta')) {
                if (Planta::find($req->planta) == null) {
                    return $this->handleAlert('Planta no encontrada.', false);
                } else {
                    $modeloRegistrar->fk_planta = $req->planta;
                }
            }
            if ($req->json()->has('tipo')) {
                $modeloRegistrar->tipo = $req->tipo;
            }
            if ($req->json()->has('location')) {
                $modeloRegistrar->fk_LocationID = $req->location;
            }
            $modeloRegistrar->NombrePlanta = $req->nombre;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if ($modeloRegistrar->save()) {
                    $modeloRegistrar->id_plata = $modeloRegistrar->latest('id_plata')->first()->id_plata;
                    return $this->handleResponse($req, $modeloRegistrar, 'Usu planta registrado.');
                }
            } catch (Exception $exc) {
            }
            return $this->handleAlert('Usu planta no registrado.', false);
        }
    }

    public function getByCompania(Request $request, $compania)
    {
        $consulta = UsuPlanta::select(
            'usuPlanta.*',
        )->leftjoin('compañia', 'compañia.id_compañia', '=', 'usuPlanta.fk_compañia')
            ->where('compañia.id_compañia', $compania)
            ->where('usuPlanta.estado', 1)
            ->get();
        return $this->handleResponse($request, $this->usuPlantaToArray($consulta), __("messages.consultado"));
    }

    public function getWithCompania(Request $request)
    {
        $consulta = UsuPlanta::select(
            'id_plata',
            'usuPlanta.ubicacion',
            'tipoPlanta',
            'NombrePlanta',
            'compañia.nombreCompañia as nombreCompania',
            'tipo',
            'usuPlanta.estado',
        )->leftjoin('compañia', 'compañia.id_compañia', '=', 'usuPlanta.fk_compañia');
        $consulta = $this->filtrar($request, $consulta, 'usuPlanta')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    /**
     * @param Request $req
     * @param $id
     * @return void
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
        $consulta = UsuPlanta::select(
            'usuPlanta.*',
            'compañia.nombreCompañia as nombreCompania',
        )->leftjoin('compañia', 'compañia.id_compañia', '=', 'usuPlanta.fk_compañia');
        $consulta = $this->filtrar($request, $consulta, 'usuPlanta');
        if ($request->has('estado')) {
            $consulta = $consulta->where('estado', $request->estado);
        }
        if ($request->has('id')) {
            if (!is_numeric($request->id)) {
                $consulta = $consulta->where('id_plata', 0);
            } else {
                $consulta = $consulta->where('id_plata', $request->id);
            }
        }
        if ($request->has('tipo')) {
            if (strcmp($request->tipo, 'AOC') == 0) {
                $consulta = $consulta->where(function ($query) {
                    $query->orWhere('tipo', 'A')
                        ->orWhere('tipo', 'C');
                });
            } else {
                $consulta = $consulta->where('tipo', $request->tipo);
            }
        }
        $consulta = $consulta->get();
        $companias = Compania::all();
        $plantas = Planta::all();
        $constsControl = CnfCostCenter::all();
        $locacions = location::all();
        foreach ($consulta as $item) {
            $this->setLocationById($item, $locacions);
            $this->setPlantaById($item, $plantas);
            $this->setCompaniById($item, $companias);
            $this->setCostCenterById($item, $constsControl);
        }
        return $this->handleResponse($request, $this->usuPlantaToArray($consulta), __("messages.consultado"));
    }

    public function getActivos(Request $request)
    {
        $consulta = UsuPlanta::select(
            'usuPlanta.*',
        )->where('estado', 1);
        $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        return $this->handleResponse($request, $this->usuPlantaToArraySimplificado($consulta), __("messages.consultado"));
    }
    public function setCompaniById($formulaCapa, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($formulaCapa->fk_compañia == $array[$i]->id_compañia) {
                $reescribir = $this->companiaToModel($array[$i]);
                $formulaCapa->objectCompania = $reescribir;
                break;
            }
        }
    }

    public function setPlantaById($formulaCapa, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($formulaCapa->fk_planta == $array[$i]->id) {
                $reescribir = $this->plantaToModel($array[$i]);
                $formulaCapa->objectPlanta = $reescribir;
                break;
            }
        }
    }

    public function setCostCenterById($formulaCapa, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($formulaCapa->fk_id_centroCosto == $array[$i]->COCEIDENTIFICATION) {
                $reescribir = $this->cnfCostControlToModel($array[$i]);
                $formulaCapa->objectCostControl = $reescribir;
                break;
            }
        }
    }

    public function setLocationById($formulaCapa, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($formulaCapa->fk_LocationID == $array[$i]->LocationID) {
                $reescribir = $this->locationToModel($array[$i]);
                $formulaCapa->objectLocation = $reescribir;
                break;
            }
        }
    }



    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function find($id)
    {
        return UsuPlanta::find($id);
    }

    public function findActive($id)
    {
        return UsuPlanta::where('id_plata', $id)->where('estado', 1)->first();
    }
}
