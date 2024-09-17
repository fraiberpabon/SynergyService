<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbMaterialCentroProduccion;
use App\Models\WbCentroProduccionHitos;
use App\Models\WbMaterialLista;
use Exception;
use Illuminate\Http\Request;

class WbMaterialCentroProduccionController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        if (!$req->json()->has('fk_id_material_lista')) {
            return $this->handleAlert('Falta campo fk_id_material_lista.', false);
        }
        if (!$req->json()->has('fk_id_planta')) {
            return $this->handleAlert('Falta campo fk_id_planta.', false);
        }
        if (!$req->json()->has('Estado')) {
            return $this->handleAlert('Falta campo Estado.', false);
        }
        if (!$req->json()->has('userCreator')) {
            return $this->handleAlert('Falta campo userCreator.', false);
        }
        if (
            $req->validate([
                'fk_id_material_lista' => 'required',
                'fk_id_planta' => 'required',
                'Estado' => 'required',
                'userCreator' => 'required'
            ])
        ) {
            if (WbMaterialLista::find($req->fk_id_material_lista) == null) {
                return $this->handleAlert('Material lista no encontrado.', false);
            }
            if (UsuPlanta::find($req->fk_id_planta) == null) {
                return $this->handleAlert('Planta no encontrada.', false);
            }
            if (usuarios_M::find($req->userCreator) == null) {
                return $this->handleAlert('Usuario no encontrada.', false);
            }
            $materialCentroProduccionRegistrar = new WbMaterialCentroProduccion;
            $materialCentroProduccionRegistrar->fk_id_material_lista = $req->fk_id_material_lista;
            $materialCentroProduccionRegistrar->fk_id_planta = $req->fk_id_planta;
            $materialCentroProduccionRegistrar->Estado = $req->Estado;
            $materialCentroProduccionRegistrar->userCreator = $req->userCreator;
            $materialCentroProduccionRegistrar = $this->traitSetProyectoYCompania($req, $materialCentroProduccionRegistrar);
            try {
                if ($materialCentroProduccionRegistrar->save()) {
                    $materialCentroProduccionRegistrar->id_material_centroProduccion = $materialCentroProduccionRegistrar->latest('id_material_centroProduccion')->first()->id_material_centroProduccion;
                    return $this->handleResponse($req, $materialCentroProduccionRegistrar, 'Material centro produccion registrada.');
                } else {
                    return $this->handleAlert('Material centro produccion no registrada.', false);
                }
            } catch (Exception $exc) {
                return $this->handleAlert('Material centro produccion no registrada.', false);
            }
        }
    }

    public function postMasiva(Request $request)
    {
        if (is_array($request->data)) {
            foreach ($request->data as $data) {
                if (WbMaterialLista::find($data['materialLista']) == null) {
                    return $this->handleAlert('No se encontró un material lista.');
                }
                if (UsuPlanta::find($data['planta']) == null) {
                    return $this->handleAlert('No se encontró una planta.');
                }
                if (!($data['estado'] == 'A' || $data['estado'] == 'I')) {
                    return $this->handleAlert('Estado no válido.');
                }
            }
            foreach ($request->data as $data) {
                $modelo = new WbMaterialCentroProduccion;
                $modelo->fk_id_material_lista = $data['materialLista'];
                $modelo->fk_id_planta = $data['planta'];
                $modelo->Estado = $data['estado'];
                $modelo->userCreator = $this->traitGetIdUsuarioToken($request);
                try {
                    $modelo->save();
                } catch (Exception $exc) {
                    return $this->handleAlert('Materiales de centro producción no registrado.');
                }
            }
            return $this->handleResponse($request, [], 'Materiales de centro producción registrado con éxito.');
        } else {
            return $this->handleAlert('Materiales de centro producción no válido.');
        }
    }

    public function update(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Material centro de produccio no valido.');
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if (
            $req->validate([
                'estado' => 'required',
            ])
        ) {
            $modeloModificar = WbMaterialCentroProduccion::find($id);
            if ($modeloModificar == null) {
                return $this->handleAlert('Material centro produccion no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Material centro produccion no valido.');
            }
            $modeloModificar->Estado = $req->estado;
            $modeloModificar->userCreator = $this->traitGetIdUsuarioToken($req);
            try {
                if ($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Material centro produccion modificado.');
                }
            } catch (Exception $exc) {
            }
            return $this->handleAlert('Material centro produccion no modificado.');
        }
    }

    public function getPorMaterialYHito(Request $req, $material, $hito = 'all')
    {
        if (strcmp($hito, 'all') == 0) {
            $consulta = WbMaterialCentroProduccion::select(
                'Wb_Material_CentroProduccion.fk_id_planta as planta',
                'usuPlanta.NombrePlanta as nombrePlanta',
                'usuPlanta.fk_LocationID as location'
            )->leftJoin('usuPlanta', 'usuPlanta.id_plata', 'Wb_Material_CentroProduccion.fk_id_planta')
                ->where('Wb_Material_CentroProduccion.fk_id_material_lista', $material)
                ->where('Wb_Material_CentroProduccion.estado', 'A')
                ->where('usuPlanta.estado', 1)
                ->groupBy('Wb_Material_CentroProduccion.fk_id_planta')
                ->groupBy('usuPlanta.NombrePlanta')
                ->groupBy('usuPlanta.fk_LocationID');
        } else {
            $consulta = WbMaterialCentroProduccion::select(
                'Wb_Material_CentroProduccion.id_material_centroProduccion as identificador',
                'Wb_Material_CentroProduccion.fk_id_material_lista as materialLista',
                'Wb_Material_CentroProduccion.fk_id_planta as planta',
                'Wb_Material_CentroProduccion.Estado as estado',
                'Wb_Material_CentroProduccion.dateCreate as fechaCreacion',
                'Wb_Material_CentroProduccion.userCreator as usuario',
                'NombrePlanta as nombrePlanta',
                'fk_LocationID as location',
            )
                ->leftJoin('usuPlanta', 'usuPlanta.id_plata', 'Wb_Material_CentroProduccion.fk_id_planta')
                ->leftJoin('Wb_CentroProduccion_Hitos', 'Wb_CentroProduccion_Hitos.fk_id_planta', 'Wb_Material_CentroProduccion.fk_id_planta')
                ->where('fk_id_material_lista', $material)
                ->where('fk_id_Hito', $hito)
                ->where('Wb_Material_CentroProduccion.estado', 'A')
                ->where('usuPlanta.estado', 1)
                ->where('Wb_CentroProduccion_Hitos.Estado', 'A')
                ->groupBy(
                    'id_material_centroProduccion',
                    'fk_id_material_lista',
                    'Wb_Material_CentroProduccion.fk_id_planta',
                    'Wb_Material_CentroProduccion.Estado',
                    'Wb_Material_CentroProduccion.dateCreate',
                    'Wb_Material_CentroProduccion.userCreator',
                    'usuPlanta.NombrePlanta',
                    'usuPlanta.fk_LocationID',
                );
        }
        $consulta = $this->filtrar($req, $consulta, 'Wb_Material_CentroProduccion')->get();
        return $this->handleResponse($req, $consulta, __('consultado'));
    }

    public function getPorMaterialYHitoNoDIsponibleParaMateriales(Request $req, $material, $hito)
    {
        $consulta = WbMaterialCentroProduccion::select(
            'Wb_Material_CentroProduccion.id_material_centroProduccion as identificador',
            'Wb_Material_CentroProduccion.fk_id_material_lista as materialLista',
            'Wb_Material_CentroProduccion.fk_id_planta as planta',
            'Wb_Material_CentroProduccion.Estado as estado',
            'Wb_Material_CentroProduccion.dateCreate as fechaCreacion',
            'Wb_Material_CentroProduccion.userCreator as usuario',
            'NombrePlanta as nombrePlanta',
            'fk_LocationID as location',
        )
            ->leftJoin('usuPlanta', 'usuPlanta.id_plata', 'Wb_Material_CentroProduccion.fk_id_planta')
            ->leftJoin('Wb_CentroProduccion_Hitos', 'Wb_CentroProduccion_Hitos.fk_id_planta', 'Wb_Material_CentroProduccion.fk_id_planta')
            ->whereRaw("
            id_material_centroProduccion  not in( select [id_material_centroProduccion]
            FROM [Wb_Material_CentroProduccion] MP
            LEFT JOIN usuPlanta PL ON PL.id_plata = MP.fk_id_planta
            LEFT JOIN Wb_CentroProduccion_Hitos CP ON CP.fk_id_planta = MP.fk_id_planta
            WHERE fk_id_material_lista = '" . $material . "' AND fk_id_Hito = '" . $hito . "')
        ")
            ->where('fk_id_material_lista', $material)
            ->where('Wb_Material_CentroProduccion.estado', 'A')
            ->where('usuPlanta.estado', 1)
            ->where('Wb_CentroProduccion_Hitos.Estado', 'A')
            ->groupBy(
                'id_material_centroProduccion',
                'fk_id_material_lista',
                'Wb_Material_CentroProduccion.fk_id_planta',
                'Wb_Material_CentroProduccion.Estado',
                'Wb_Material_CentroProduccion.dateCreate',
                'Wb_Material_CentroProduccion.userCreator',
                'usuPlanta.NombrePlanta',
                'usuPlanta.fk_LocationID'
            );
        $consulta = $this->filtrar($req, $consulta, 'Wb_Material_CentroProduccion')->get();
        return $this->handleResponse($req, $consulta, __('consultado'));
    }

    public function getPorFormulaYHitoNoDIsponibleParaFormulas(Request $req, $material, $hito)
    {
        $consulta = WbMaterialCentroProduccion::select(
            'id_formula_centroProduccion',
            'fk_id_formula_lista',
            'Wb_Formula_CentroProduccion.fk_id_planta',
            'Wb_Formula_CentroProduccion.Estado',
            'Wb_Formula_CentroProduccion.dateCreate',
            'Wb_Formula_CentroProduccion.userCreator',
            'codigoFormulaCdp',
            'usuPlanta.NombrePlanta',
            'usuPlanta.fk_LocationID'
        )
            ->leftJoin('usuPlanta', 'usuPlanta.id_plata', 'Wb_Material_CentroProduccion.fk_id_planta')
            ->leftJoin('Wb_CentroProduccion_Hitos', 'Wb_CentroProduccion_Hitos.fk_id_planta', 'Wb_Material_CentroProduccion.fk_id_planta')
            ->whereRaw("
            id_material_centroProduccion  not in( select [id_material_centroProduccion]
            FROM [Wb_Material_CentroProduccion] MP
            LEFT JOIN usuPlanta PL ON PL.id_plata = MP.fk_id_planta
            LEFT JOIN Wb_CentroProduccion_Hitos CP ON CP.fk_id_planta = MP.fk_id_planta
            WHERE fk_id_formula_lista = '" . $material . "' AND fk_id_Hito = '" . $hito . "')
        ")
            ->where('fk_id_formula_lista', $material)
            ->where('Wb_Material_CentroProduccion.estado', 'A')
            ->where('usuPlanta.estado', 1)
            ->where('Wb_CentroProduccion_Hitos.Estado', 'A')
            ->groupBy(
                'id_material_centroProduccion',
                'fk_id_formula_lista',
                'Wb_Material_CentroProduccion.fk_id_planta',
                'Wb_Material_CentroProduccion.Estado',
                'Wb_Material_CentroProduccion.dateCreate',
                'Wb_Material_CentroProduccion.userCreator',
                'usuPlanta.NombrePlanta',
                'usuPlanta.fk_LocationID'
            );
        $consulta = $this->filtrar($req, $consulta, 'Wb_Material_CentroProduccion')->get();
        return $this->handleResponse($req, $consulta, __('consultado'));
    }

    public function getByMaterialLista(Request $req, $id)
    {
        if (is_numeric($id)) {
            $consulta = WbMaterialCentroProduccion::where('Estado', 'A')
                ->where('fk_id_material_lista', $id);
            $consulta = $this->filtrar($req, $consulta, 'Wb_Material_CentroProduccion')->get();
            $materialesLista = WbMaterialLista::all();
            $ususPlanta = UsuPlanta::all();
            foreach ($consulta as $item) {
                $this->setMaterialListanById($item, $materialesLista);
                $this->setUsuPlantaById($item, $ususPlanta);
            }
            return $this->handleResponse($req, $consulta, 'Consultado.');
        } else {
            return $this->handleResponse($req, [], __("messages.consultado"));
        }
    }

    public function getConMaterialListaYUsuPlanta(Request $request)
    {
        $consulta = WbMaterialCentroProduccion::where('Wb_Material_CentroProduccion.Estado', 'A');
        $consulta = $this->filtrar($request, $consulta, 'Wb_Material_CentroProduccion')->get();
        $materialesLista = WbMaterialLista::all();
        $ususPlanta = UsuPlanta::all();
        foreach ($consulta as $item) {
            $this->setMaterialListanById($item, $materialesLista);
            $this->setUsuPlantaById($item, $ususPlanta);
        }
        return $this->handleResponse($request, $this->wbMaterialCentroProduccionToArray($consulta), __("messages.consultado"));
    }

    public function setUsuPlantaById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($modelo->fk_id_planta == $array[$i]->id_plata) {
                $reescribir = $this->usuPlantaToModel($array[$i]);
                $modelo->objectUsuPlanta = $reescribir;
                break;
            }
        }
    }

    public function setMaterialListanById($modelo, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($modelo->fk_id_material_lista == $array[$i]->id_material_lista) {
                $reescribir = $this->wbMaterialListaToModel($array[$i]);
                $modelo->objectMaterialLista = $reescribir;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
        $consulta = WbMaterialCentroProduccion::select();
        if ($request->estado && $request->estado != 'null') {
            $consulta = $consulta->where('Estado', $request->estado);
        } else {
            $consulta = $consulta->where('Estado', 'A');
        }
        if ($request->materialLista) {
            $consulta = $consulta->where('fk_id_material_lista', $request->materialLista);
        }
        $consulta = $this->filtrar($request, $consulta)->get();
        $materialesLista = WbMaterialLista::all();
        $ususPlanta = UsuPlanta::all();
        foreach ($consulta as $item) {
            $this->setMaterialListanById($item, $materialesLista);
            $this->setUsuPlantaById($item, $ususPlanta);
        }
        return $this->handleResponse($request, $consulta, 'Consultado.');
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    /**
     * Obtiene el ID del centro de producción activo asociado a un material por su ID y su proyecto.
     *
     * @param int $id_material El ID del material.
     * @param int $proyecto El Proyecto en el que se esta trabajando.
     * @return Illuminate\Support\Collection|null La colección de IDs de plantas asociadas al material o null si no se encuentra.
     */
    public function getIdCentroProduccionActivoPorIdMaterialYProyecto($id_material, $proyecto)
    {
        try {
            // Utiliza el modelo Eloquent para realizar la consulta
            $plantas = WbMaterialCentroProduccion::select('fk_id_planta')
                ->where('fk_id_material_lista', $id_material)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $proyecto)
                ->get();

            // Retorna la colección de IDs de plantas asociadas al material
            return $plantas;
        } catch (\Throwable $e) {
            // En caso de error, puedes manejarlo según tus necesidades
            // Aquí se ha omitido el manejo del error para simplificar el ejemplo
            // Puedes agregar un retorno de respuesta con error si lo deseas
            //\Log::error($e->getMessage());
            return null;
        }
    }

    public function getCentrobyMaterialHito(Request $request, $material, $hito){
        //se consultan los centros de produccion activos que cuentan con la disponiblidad del material
        $centros=WbMaterialCentroProduccion::with('centro')
            ->where('fk_id_material_lista',$material)
            ->where('Estado','A');

        //se filtra por proyecto
        $centros = $this->filtrar($request, $centros, 'Wb_Material_CentroProduccion')->get();

        //se valida si se debe limitar por hito o no
        if($hito!='all'){
            //consultamos los centros de produccion que estan autorizados a despachar al hito
            $CentrosPermitidos=WbCentroProduccionHitos::select('fk_id_planta')->where('fk_id_Hito',$hito)->where('Estado','A')->get();

            //ahora extraemos los centros permitidos para despachar
            $autorizados=$centros->whereIn('centro.id_plata',$CentrosPermitidos->pluck('fk_id_planta'));

            //ahora sacamos los no autorizados
            $noautorizado=$centros->whereNotIn('centro.id_plata',$CentrosPermitidos->pluck('fk_id_planta'))->where('centro.estado',1);

           //se crea la colleccion que va a rtener los dos datos.
            //se formatean los datos aprobados
            $respuesta=collect($this->WbPlantaAutorizadaToArray($autorizados->pluck('centro')->sortBy('NombrePlanta'),1));

            //se formatean los no aprobados
            $respuesta=$respuesta->merge($this->WbPlantaAutorizadaToArray($noautorizado->pluck('centro')->sortBy('NombrePlanta'),0));
        }else{
            //se envian todos los datos recibido
            $respuesta=collect($this->WbPlantaAutorizadaToArray($centros->pluck('centro')->sortBy('NombrePlanta'),1));
        }


        return $this->handleResponse($request,$respuesta->unique('identificador')->values(), __('messages.consultado'));


    }

    public function getMaterialwithCenter(Request $request){
        //se consultan los materiales de produccion activos que cuentan con la disponiblidad del material
        $materiales=WbMaterialCentroProduccion::with('material')
            ->where('Estado','A');

        //se filtra por proyecto
        $materiales = $this->filtrar($request, $materiales, 'Wb_Material_CentroProduccion')->get();

        //se filtra por materiales activos y solicitables
        $materiales=$materiales->where('material.Estado','A')->where('material.Solicitable','S');

        //se envian todos los datos recibido
        $respuesta=collect($this->WbMaterialAutorizadoToArray($materiales->pluck('material')->sortBy('Nombre'),1));

        return $this->handleResponse($request,$respuesta->unique('identificador')->values(), __('messages.consultado'));

    }
}
