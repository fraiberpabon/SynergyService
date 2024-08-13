<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Http\trait\TraitEliminarImagen;
use App\Http\trait\TraitGuardarImagenBase64;
use App\Models\Compania;
use App\Models\ProjectCompany;
use App\Models\WbCompanieProyecto;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompaniaController extends BaseController implements Vervos
{
    use TraitGuardarImagenBase64, TraitEliminarImagen;
    /**
     * @param Request $req
     * @return JsonResponse|void
     */
    public function post(Request $req) {
        if(!$req->json()->has('nombreCompania')) {
            return $this->handleAlert('Falta campo nombreCompañia.');
        }
        if(!$req->json()->has('ubicacion')) {
            return $this->handleAlert('Falta campo ubicacion.');
        }
        if(!$req->json()->has('proyecto')) {
            return $this->handleAlert('Falta campo proyecto.');
        }
        if($req->validate([
            'nombreCompania' => 'required|string|max:50',
            'ubicacion' => 'required|string|max:50',
            'numeroIdentificacion' => 'required|string|max:20',
            'proyecto' => 'required|numeric'
        ])) {
            if($req->proyecto !== -1 && ProjectCompany::find($req->proyecto) == null) {
                return $this->handleAlert('Project Company no enconrado.');
            }
            $companiaEncontrada = Compania::where(function ($query) use ($req) {
                $query->where('nombreCompañia', $req->nombreCompania)
                    ->orWhere('numero_identificacion', $req->numeroIdentificacion);
            })->first();
            if($companiaEncontrada != null) {
                return $this->handleAlert('Nombre o numero de identificacion en uso.');
            }
            $modeloRegistrar = new Compania;
            $modeloRegistrar->nombreCompañia = $req->nombreCompania;
            $modeloRegistrar->ubicacion = $req->ubicacion;
            $modeloRegistrar->numero_identificacion = $req->numeroIdentificacion;
            if(strlen($req->logo) > 0) {
                $imageName = uniqid() . '.png';
                $this->guardarImagenTrait($req->logo, env('COMPANY_PATCH'), $imageName );
                $modeloRegistrar->logo = $imageName;
            }
            $companiaProyecto = new WbCompanieProyecto;
            try {
                if($modeloRegistrar->save()) {
                    if($req->proyecto !== -1) {
                        $modeloRegistrar = $modeloRegistrar->where('nombreCompañia', $req->nombreCompania)->first();
                        $companiaProyecto->fk_compañia = $modeloRegistrar->id_compañia;
                        $companiaProyecto->fk_id_project_Company = $req->proyecto;
                        $companiaProyecto->save();
                    }
                    return $this->handleResponse($req, $modeloRegistrar, 'Compañia registrada.');
                }
            } catch(Exception $exc) { }
            return $this->handleAlert('Compañia no registrada.');
        }
    }

    public function getByProyecto(Request $request, $proyecto) {
        if(!is_numeric($proyecto)) {
            return $this->handleAlert('Proyecto no valido.');
        }
        $consulta = Compania::select('compañia.*')
            ->where('Wb_Companie_Proyecto.fk_id_project_Company', $proyecto)
            ->leftjoin('Wb_Companie_Proyecto', 'Wb_Companie_Proyecto.fk_compañia', '=', 'compañia.id_compañia')->get();
        return $this->handleResponse($request, $this->companiaToArray($consulta), __("messages.consultado"));
    }

    public function get(Request $request) {
        $proyecto = $this->traitGetProyectoCabecera($request);
        if(!is_numeric($proyecto)) {
            return $this->handleAlert('Proyecto no valido.');
        }
        $consulta = Compania::select('compañia.*')
            ->where('Wb_Companie_Proyecto.fk_id_project_Company', $proyecto)
            ->leftjoin('Wb_Companie_Proyecto', 'Wb_Companie_Proyecto.fk_compañia', '=', 'compañia.id_compañia')->orderBy('nombreCompañia', 'asc')->get();
        return $this->handleResponse($request, $this->companiaToArray($consulta), __("messages.consultado"));
    }

    public function getAll(Request $request) {
        $consulta = Compania::get();
        return $this->handleResponse($request, $this->companiaToArray($consulta), __("messages.consultado"));
    }


    public function update(Request $req, $id)
    {
        if(!$req->json()->has('nombreCompania')) {
            return $this->handleAlert('Falta campo nombreCompañia.');
        }
        if(!$req->json()->has('ubicacion')) {
            return $this->handleAlert('Falta campo ubicacion.');
        }
        if($req->validate([
            'nombreCompania' => 'required|string|max:50',
            'ubicacion' => 'required|string|max:50',
            'numeroIdentificacion' => 'required|string|max:20',
        ])) {
            $companiaEncontrada = Compania::where(function ($query) use ($req) {
                $query->where('nombreCompañia', $req->nombreCompania)
                    ->orWhere('numero_identificacion', $req->numeroIdentificacion);
            })
                ->where('id_compañia', '!=',  $id)->first();
            if($companiaEncontrada != null) {
                return $this->handleAlert('Nombre o numero de identificacion en uso.');
            }
            $modeloRegistrar = Compania::find($id);
            if (!$modeloRegistrar) {
                return $this->handleAlert('Companñia no encontrada');
            }

            $modeloRegistrar->nombreCompañia = $req->nombreCompania;
            $modeloRegistrar->ubicacion = $req->ubicacion;
            $modeloRegistrar->numero_identificacion = $req->numeroIdentificacion;
            if (strlen($modeloRegistrar->logo) > 0) {
                $this->eliminarImagenTrait($modeloRegistrar->logo, 'company/');
            }
            if(strlen($req->logo) > 0) {
                $imageName = uniqid() . '.png';
                $this->guardarImagenTrait($req->logo, env('COMPANY_PATCH'), $imageName );
                $modeloRegistrar->logo = $imageName;
            }
            try {
                if($modeloRegistrar->save()) {
                    return $this->handleResponse($req, [], 'Compañia modificada.');
                }
            } catch(Exception $exc) {}
            return $this->handleAlert('Compañia no registrada.');
        }
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        $consulta = Compania::select(
            'id_compañia',
            'nombreCompañia',
            'ubicacion',
        )->get();
        return $this->handleResponse($request, $this->companiaToArray($consulta), __("messages.consultado"));
    }
}
