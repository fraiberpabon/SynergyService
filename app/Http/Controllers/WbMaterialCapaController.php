<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\usuarios_M;
use App\Models\WbMaterialCapa;
use App\Models\WbMaterialLista;
use App\Models\WbTipoCapa;
use Exception;
use Illuminate\Http\Request;

class WbMaterialCapaController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('fk_id_tipo_capa')) {
            return $this->handleAlert('Falta campo fk_id_tipo_capa.', false);
        }
        if(!$req->json()->has('fk_id_material_lista')) {
            return $this->handleAlert('Falta campo fk_id_material_lista.', false);
        }
        if(!$req->json()->has('Estado')) {
            return $this->handleAlert('Falta campo Estado.', false);
        }
        if(!$req->json()->has('userCreator')) {
            return $this->handleAlert('Falta campo userCreator.', false);
        }
        if($req->validate([
            'fk_id_tipo_capa'=> 'required',
            'fk_id_material_lista'=> 'required',
            'Estado'=> 'required',
            'userCreator'=> 'required'
        ])) {
            if(WbTipoCapa::find($req->fk_id_tipo_capa) == null) {
                return $this->handleAlert('Tipo de capa no encontrado.', false);
            }
            if(WbMaterialLista::find($req->fk_id_material_lista) == null) {
                return $this->handleAlert('Material no encontrado.', false);
            }
            if(usuarios_M::find($req->userCreator) == null) {
                return $this->handleAlert('Usuario no encontrado.', false);
            }
            $tipoCapaRegistrar = new WbMaterialCapa;
            $tipoCapaRegistrar->fk_id_tipo_capa = $req->fk_id_tipo_capa;
            $tipoCapaRegistrar->fk_id_material_lista = $req->fk_id_material_lista;
            $tipoCapaRegistrar->Estado = $req->Estado;
            $tipoCapaRegistrar->userCreator = $req->userCreator;
            $tipoCapaRegistrar = $this->traitSetProyectoYCompania($req, $tipoCapaRegistrar);
            try {
                if($tipoCapaRegistrar->save()) {
                    $tipoCapaRegistrar->id_material_capa = $tipoCapaRegistrar->latest('id_material_capa')->first()->id_material_capa;
                    return $this->handleResponse($req, $tipoCapaRegistrar, 'Tipo de capa registrado.');
                } else {
                    return $this->handleAlert('No se pudo registrar el tipo de capa.', false);
                }
            } catch(Exception $exc) {
                return $this->handleAlert('No se pudo registrar el tipo de capa.', false);
            }
        }
    }

    public function postMasivo(Request $req) {
        if (is_array($req->data)) {
            foreach ($req->data as $data) {
                if(WbTipoCapa::find($data['tipoCapa']) == null) {
                    return $this->handleAlert('Tipo de capa no encontrado.', false);
                }
                if(WbMaterialLista::find($data['materialLista']) == null) {
                    return $this->handleAlert('Material no encontrado.', false);
                }
            }
            foreach ($req->data as $data) {
                $tipoCapaRegistrar = new WbMaterialCapa;
                $tipoCapaRegistrar->fk_id_tipo_capa = $data['tipoCapa'];
                $tipoCapaRegistrar->fk_id_material_lista = $data['materialLista'];
                $tipoCapaRegistrar->Estado = $data['estado'];
                $tipoCapaRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
                $tipoCapaRegistrar = $this->traitSetProyectoYCompania($req, $tipoCapaRegistrar);
                try {
                    $tipoCapaRegistrar->save();
                } catch(Exception $exc) {
                    return $this->handleAlert('No se pudo registrar el material de capa.', false);
                }
            }
            return $this->handleResponse($req, $tipoCapaRegistrar, 'Material de capa registrado.');
        }
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Material capa no valido.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if($req->validate([
            'estado' => 'required',
        ])) {
            $modeloModificar = WbMaterialCapa::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert('Material capa no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Material capa no valido.');
            }
            $modeloModificar->Estado = $req->estado;
            $modeloModificar->userCreator = $this->traitGetIdUsuarioToken($req);
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Material capa modificado.');
                }
            } catch(Exception $exc) {}
            return $this->handleAlert('Material capa no modificado.');
        }
    }

    public function getByMaterialLista(Request $req, $id) {
        if(is_numeric($id)){
            $consulta = WbMaterialCapa::select(
                'Wb_Material_Capa.id_material_capa',
                'fk_id_tipo_capa',
                'fk_id_material_lista',
                'Wb_Tipo_Capa.Estado',
                'Wb_Tipo_Capa.dateCreate',
                'Wb_Tipo_Capa.userCreator',
                'Wb_Tipo_Capa.Descripcion')
            ->leftjoin('Wb_Tipo_Capa', 'Wb_Tipo_Capa.id_tipo_capa', '=', 'Wb_Material_Capa.fk_id_tipo_capa')
            ->where('Wb_Material_Capa.Estado', '=', 'A')
            ->where('Wb_Material_Capa.fk_id_material_lista', '=', $id);
            $consulta = $this->filtrar($req, $consulta, 'Wb_Material_Capa')->get();
            return $this->handleResponse($req, $consulta, __("messages.consultado"));
        } else {
            return $this->handleAlert([], false);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
        $consulta = WbMaterialCapa::select();
        if ($request->material) {
            $consulta = $consulta->where('Wb_Material_Capa.fk_id_material_lista', $request->material);
        }
        if ($request->estado) {
            $consulta = $consulta->where('Wb_Material_Capa.Estado', '=', 'A');
        }
        $consulta = $this->filtrar($request, $consulta)->get();
        $tiposCapa = WbTipoCapa::all();
        foreach ($consulta as $item) {
            $this->setTipoCapaById($item, $tiposCapa);
        }
        return $this->handleResponse($request, $this->wbMaterialCapaToArray($consulta), __("messages.consultado"));
    }

    public function materialesPorCapaDisponible(Request $request, $tipoDeCapa)
    {
        $consulta = WbMaterialCapa::select(
            'Wb_Material_Lista.Nombre as nombre',
            'Wb_Material_Lista.unidadMedida as unidadMedida',
            'Wb_Material_Capa.fk_id_material_lista as materialLista'
        )
        ->leftJoin('Wb_Material_Lista', 'Wb_Material_Lista.id_material_lista', 'Wb_Material_Capa.fk_id_material_lista')
        ->where('Wb_Material_Capa.Estado', 'A')
        ->where('Wb_Material_Lista.estado', 'A')
        ->orderBy('Wb_Material_Lista.Nombre')
        ->orderBy('Wb_Material_Lista.unidadMedida')
        ->orderBy('Wb_Material_Capa.fk_id_material_lista');
        if (strcmp($tipoDeCapa, 'all') != 0) {
            $consulta->select(
                'Wb_Material_Capa.id_material_capa as identificador',
                'Wb_Material_Capa.fk_id_tipo_capa tipoCapa',
                'Wb_Material_Capa.fk_id_material_lista as materialLista',
                'Wb_Material_Capa.Estado as estado',
                'Wb_Material_Capa.dateCreate as fechaCreacion',
                'Wb_Material_Capa.userCreator as usuario',
                'Wb_Material_Lista.Nombre as nombre',
                'Wb_Material_Lista.unidadMedida as unidadMedida',
            );
            $consulta = $consulta->where('Wb_Material_Capa.fk_id_tipo_capa', $tipoDeCapa);
        }
        $consulta = $this->filtrar($request, $consulta, 'Wb_Material_Lista')->get();
        return $this->handleResponse($request, $consulta->get(), __('messages.consultado'));
    }

    public function setTipoCapaById($modelo, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($modelo->fk_id_tipo_capa == $array[$i]->id_tipo_capa) {
                $reescribir = $this->wbTipoCapaToModel($array[$i]);
                $modelo->objectTipoCapa = $reescribir;
                break;
            }
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getMaterialCapa(Request $request,$id){
        //se consultan los materiales autorizados
        $consulta=WbMaterialCapa::with('Material')->where('estado','A');
        //variable que guarda el estado de no aprobado
        $no=1;
        //se filtra por proyecto
        $consulta = $this->filtrar($request, $consulta, 'Wb_Material_Capa')->get();
        $consulta=$consulta->where('Material.Estado','A')->where('Material.Solicitable','S');
        $permitidas=$consulta;
        if ($id!='all') {
            $permitidas=$permitidas->where('fk_id_tipo_capa',$id);
            $no=0;
        }

        //se extrae la lista de materiales sin autorizacion
        $nopermitidos=$consulta->whereNotIn('Material.id_material_lista',$permitidas->pluck('Material.id_material_lista'));
        //se crea la colleccion que va a rtener los dos datos.
        //se formatean los datos aprobados
        $respuesta=collect($this->WbMaterialAutorizadoToArray($permitidas->pluck('Material')->sortBy('Nombre'),1));

        //se formatean los no aprobados
        $respuesta=$respuesta->merge($this->WbMaterialAutorizadoToArray($nopermitidos->pluck('Material')->sortBy('Nombre'),$no));

        return $this->handleResponse($request,$respuesta->unique('identificador')->values(), __('messages.consultado'));
    }

}
