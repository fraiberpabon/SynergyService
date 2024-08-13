<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbLiberacionesFormatosAct;
use Illuminate\Http\Request;

class WbLiberacionesFormatosActController extends BaseController implements Vervos
{
         public function post(Request $req){

         }

        /**
         * @param Request $req
         * @param $id
         * @return void
         */
        public function update(Request $req, $id){

        }

        /**
         * @param $id
         * @return void
         */
        public function delete(Request $request, $id){

        }

        /**
         * @return void
         */
        public function get(Request $request ){

        }

    //listamos las actividades por departamento OBSOLETO
    public function getActividad($id){
            $Actividades=WbLiberacionesFormatosAct::select(
                'id_liberaciones_formatos_act',
                'fk_id_liberaciones_actividades',
                'fk_id_liberaciones_formatos',
                 'Wb_Liberaciones_Actividades.nombre'
                ,'Wb_Liberaciones_Actividades.criterios'
                ,'datecreate'
                ,'Wb_Liberaciones_Formatos_Act.estado'
                ,'Wb_Liberaciones_Formatos_Act.userCreator'
                )
            ->leftjoin('Wb_Liberaciones_Actividades','Wb_Liberaciones_Formatos_Act.fk_id_liberaciones_actividades','=','Wb_Liberaciones_Actividades.id_liberaciones_actividades')
            ->where('Wb_Liberaciones_Actividades.estado','=',1)
            ->where('fk_id_liberaciones_formatos','=',$id)
            ->get();
            return response()->json($this->toArray($Actividades));
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @deprecated
     */
    public function getActividadV2Deprecated(Request $request, $id) {
        $Actividades=WbLiberacionesFormatosAct::select(
            'id_liberaciones_formatos_act',
            'fk_id_liberaciones_actividades',
            'fk_id_liberaciones_formatos',
            'Wb_Liberaciones_Actividades.nombre'
            ,'Wb_Liberaciones_Actividades.criterios'
            ,'datecreate'
            ,'Wb_Liberaciones_Formatos_Act.estado'
            ,'Wb_Liberaciones_Formatos_Act.userCreator'
        )
            ->leftjoin('Wb_Liberaciones_Actividades','Wb_Liberaciones_Formatos_Act.fk_id_liberaciones_actividades','=','Wb_Liberaciones_Actividades.id_liberaciones_actividades')
            ->where('Wb_Liberaciones_Actividades.estado','=',1)
            ->where('fk_id_liberaciones_formatos','=',$id)
            ->get();
        return $this->handleResponse($request, $this->toArray($Actividades),"success");
    }

    //listamos las actividades por departamento
    public function getActividadV2(Request $request, $id) {
            $actividades=WbLiberacionesFormatosAct::select(
                'id_liberaciones_formatos_act',
                'fk_id_liberaciones_actividades',
                'fk_id_liberaciones_formatos',
                 'Wb_Liberaciones_Actividades.nombre'
                ,'Wb_Liberaciones_Actividades.criterios'
                ,'datecreate'
                ,'Wb_Liberaciones_Formatos_Act.estado'
                ,'Wb_Liberaciones_Formatos_Act.userCreator'
                ,'Wb_tipo_formato.id_tipo_formato as idFormato'
                ,'Wb_tipo_equipo.id_tipo_equipo as idTipoEquipo'
                ,'Wb_tipo_equipo.nombre as nombreTipoEquipo'
                )
            ->leftjoin('Wb_Liberaciones_Actividades','Wb_Liberaciones_Formatos_Act.fk_id_liberaciones_actividades','=','Wb_Liberaciones_Actividades.id_liberaciones_actividades')
            ->leftjoin('Wb_Liberaciones_Formatos','Wb_Liberaciones_Formatos.id_liberaciones_formatos','=','Wb_Liberaciones_Formatos_Act.fk_id_liberaciones_formatos')
            ->leftJoin('Wb_tipo_formato', 'Wb_tipo_formato.id_tipo_formato', 'Wb_Liberaciones_Formatos.fk_tipo_formato')
                ->leftJoin('Wb_tipo_equipo', 'Wb_tipo_equipo.id_tipo_equipo', 'Wb_Liberaciones_Formatos.fk_id_tipo_equipo')
            ->where('Wb_Liberaciones_Actividades.estado','=',1)
            ->where('Wb_Liberaciones_Formatos.fk_tipo_formato','=',$id);
        /**
         *  valido que
         */
        $actividades = $this->filtrarPorProyecto($request, $actividades, 'Wb_Liberaciones_Actividades')->get();
        return $this->handleResponse($request, $this->toArray($actividades),"success");
    }

    function toModel($data): array{
        return
            [
                'IDFORMAACTIVIDAD'=>$data['id_liberaciones_formatos_act'],
                'IDLIBACTIVIDAD'=>$data['fk_id_liberaciones_actividades'],
                'IDLIBFORMA'=>$data['fk_id_liberaciones_formatos'],
                'DATECREATE'=>$data['datecreate'],
                'ESTADO'=>$data['estado'],
                'IDUSUARIO'=>$data['userCreator'],
                'NOMBRE'=>$data['nombre'],
                'CRITERIOS'=>$data['criterios'],
                'idFormato'=>$data['idFormato'],
                'idTipoEquipo'=>$data['idTipoEquipo'],
                'nombreTipoEquipo'=>$data['nombreTipoEquipo'],
            ];
    }


    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
