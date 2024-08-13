<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Http\interfaces\Vervos;
use App\Http\Resources\Wb_solicitud_liberaciones_act_resources;
use App\Models\Wb_Solicitud_Liberaciones_Act;
use Illuminate\Http\Request;

class Wb_solicitud_liberaciones_act_controller extends BaseController implements Vervos
{
    //listamos las actividades por solicitud
    public function getActividadDeprecated(Request $request, $id)
    {
        $proyecto = $this->traitGetProyectoCabecera($request);

        $Actividades = Wb_Solicitud_Liberaciones_Act::select(
            'id_solicitud_liberaciones_act',
            'fk_id_solicitud_liberaciones',
            'fk_id_liberaciones_actividades',
            'calificacion',
            'Wb_Solicitud_Liberaciones_Act.estado',
            'fk_id_usuario',
            'Wb_Liberaciones_Actividades.nombre',
            'Wb_Liberaciones_Actividades.criterios',
            'nota'
        )
            ->leftjoin('Wb_Liberaciones_Actividades', 'Wb_Solicitud_Liberaciones_Act.fk_id_liberaciones_actividades', '=', 'Wb_Liberaciones_Actividades.id_liberaciones_actividades')
            ->where('fk_id_solicitud_liberaciones', '=', $id);
        //$Actividades = $this->filtrar($request, $Actividades, 'Wb_Solicitud_Liberaciones_Act')->get();
        if ($proyecto != null) {
            $Actividades = $this->filtrar($request, $Actividades, 'Wb_Solicitud_Liberaciones_Act')->get();
        }
        return json_encode(new Wb_solicitud_liberaciones_act_resources($Actividades->get()));
    }

    //listamos las actividades por solicitud por proyecto v1 
    public function getActividad(Request $request, $id)
    {
        $Actividades = Wb_Solicitud_Liberaciones_Act::select(
            'id_solicitud_liberaciones_act',
            'fk_id_solicitud_liberaciones',
            'fk_id_liberaciones_actividades',
            'calificacion'
            ,
            'Wb_Solicitud_Liberaciones_Act.estado'
            ,
            'fk_id_usuario'
            ,
            'Wb_Liberaciones_Actividades.nombre'
            ,
            'Wb_Liberaciones_Actividades.criterios'
            ,
            'nota'
        )
            ->leftjoin('Wb_Liberaciones_Actividades', 'Wb_Solicitud_Liberaciones_Act.fk_id_liberaciones_actividades', '=', 'Wb_Liberaciones_Actividades.id_liberaciones_actividades')
            ->where('fk_id_solicitud_liberaciones', '=', $id);
        $Actividades = $this->filtrar($request, $Actividades, 'Wb_Solicitud_Liberaciones_Act')->get();
        return $this->handleResponse($request, $this->liberacionesActividadToArray($Actividades), '1');
    }

    /*
     * CAMBIAR ESTADO DE UNA CALIFICACION
     */
    public function CambiarEstadoDeprecated(Request $request)
    {
        try {
            $datos = $request->all();
            $actividades = Wb_Solicitud_Liberaciones_Act::find($datos['IDSOLIACTIVIDAD']);
            $estado = 1;
            if ($actividades->fk_id_usuario != $datos['IDUSUARIO']) {
                if ($actividades->calificacion != $datos['CALIFICACION']) {
                    switch ($actividades->estado) {
                        case '0':
                            $estado = 2;
                            break;
                        case '1':
                            $estado = 2;
                            break;
                        case '2':
                            $estado = 0;
                            break;
                    }
                }
            }

            $now = new \DateTime();

            $actividades->calificacion = $datos['CALIFICACION'];
            $actividades->fk_id_usuario = $datos['IDUSUARIO'];
            $actividades->nota = $datos['NOTA'];
            $actividades->estado = $estado;
            $actividades->ubicacionGPS = $datos['UBICACION'];
            $actividades->dateUpdate = $now->format('d-m-Y H:i:s');
            $actividades->save();
            $actividades->refresh();
            return $this->handleAlert('1', true);
        } catch (\Throwable $th) {
            return $this->handleError('Error', $th->getMessage());
        }
    }


    /*
     * CAMBIAR ESTADO DE UNA CALIFICACION ADAPTADO A REQUEST NETWORK
     */
    public function CambiarEstado(Request $request)
    {
        try {
            $actividades = Wb_Solicitud_Liberaciones_Act::find($request->IDSOLIACTIVIDAD);
            $estado = 1;
            if ($actividades->fk_id_usuario != $request->IDUSUARIO) {
                if ($actividades->calificacion != $request->CALIFICACION) {
                    switch ($actividades->estado) {
                        case '0':
                            $estado = 2;
                            break;
                        case '1':
                            $estado = 2;
                            break;
                        case '2':
                            $estado = 0;
                            break;
                    }
                }
            }

            $now = new \DateTime();

            $idUsuario = $this->traitGetIdUsuarioToken($request);

            $actividades->calificacion = $request->CALIFICACION;
            $actividades->fk_id_usuario = $idUsuario;
            $actividades->nota = $request->NOTA;
            $actividades->estado = $estado;
            $actividades->ubicacionGPS = $request->UBICACION;
            $actividades->dateUpdate = $now->format('d-m-Y H:i:s');
            $actividades->save();
            $actividades->refresh();
            return $this->handleAlert('1', true);

        } catch (\Throwable $th) {
            return $this->handleError('Error', $th->getMessage());
        }
    }

    /**
     * Cambiar estado adaptado a la nueva funcion para registro de la firma
     */
    public function cambiarEstadoV2(Request $req)
    {
        try {
            //Consultamos si tenemos el array de calificaciones
            if (!$req->has('CALIFICACIONES')) {
                //en caso de no existir enviamos el error
                //return $this->handleAlert('Antes de poder firmar debe calificar todas las actividades');

                //devolvemos false en caso de no tener calificaciones
                return false;
            }

            if ($req->CALIFICACIONES == null) return true;

            //Convertimos el json del array de calificaciones a un array
            $calificacionesArray = json_decode($req->CALIFICACIONES, true);

            if (sizeof($calificacionesArray) == 0) {
                //en caso de la lista estar vacia enviamos el error
                //return $this->handleAlert('Antes de poder firmar debe calificar todas las actividades');

                //devolvemos false en caso de no tener datos en el array de calificaciones
                return false;
            }

            //obtenemos el idenficador del usuario que viene del token
            $idUsuario = $this->traitGetIdUsuarioToken($req);

            foreach ($calificacionesArray as $key) {
                //buscamos la actividad
                $actividades = Wb_Solicitud_Liberaciones_Act::find($key['identificador']);

                //consultamos si encontramos la actividad
                if ($actividades == null) {
                    //return $this->handleAlert('Error al registrar calificaciones por favor volver a intentar');

                    //en caso de no encontrar actividad devolvemos false
                    return false;
                }
                //definimos el estado inicial
                $estado = 1;

                //consultamos si el usuario de la actividad a calificar es diferente al usuario que esta realizando la calificacion
                if ($actividades->fk_id_usuario != $idUsuario) {
                    //consultamos si la calificacion es diferente a la que se encuentra actualemente registrada
                    if ($actividades->calificacion != $key['calificacion']) {
                        //aplicamos los criterios dependiendo del estado de la actividad a calificar
                        switch ($actividades->estado) {
                            case '0':
                                $estado = 2;
                                break;
                            case '1':
                                $estado = 2;
                                break;
                            case '2':
                                $estado = 0;
                                break;
                        }
                    }
                }
                
                //capturamos la fecha del sistema
                $now = new \DateTime();

                //ingresamos los datos que se desean actualizar
                $actividades->calificacion = $key['calificacion'];
                $actividades->fk_id_usuario = $idUsuario;
                $actividades->nota = $key['nota'];
                $actividades->estado = $estado;
                $actividades->ubicacionGPS = $req->UBICACION;
                if (array_key_exists('idMotivo', $key)) {
                    $actividades->fk_id_motivo_rechazo = $key['idMotivo'];
                } 
                //$actividades->fk_id_motivo_rechazo = $key['idMotivo'];
                $actividades->dateUpdate = $now->format('d-m-Y H:i:s');

                //guardamos y consultamos si el registro fue correcto
                if (!$actividades->save()) {
                    //return $this->handleAlert('Error al registrar calificaciones por favor volver a intentar');
                    
                    //en el caso de no se correcto entonces devolvemos false
                    return false;
                }
            }
            //return $this->handleAlert('1', true);

            //si todo sale correctamente devolvemos true
            return true;

        } catch (\Throwable $th) {
            //en caso de un error grave
            \Log::error('Error calificacion de actividad en firma de liberacion de capa -> ' . $th->getMessage());
            
            //enviamos el false para notificar el fallo
            return false;
        }
    }

    /**
     * @param Request $req
     * @return void
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
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
