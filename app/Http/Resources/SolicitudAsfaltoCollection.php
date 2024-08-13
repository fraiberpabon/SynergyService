<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class SolicitudAsfaltoCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        date_default_timezone_set('Etc/GMT-1');
        return 
        $this->collection->map(function($data) {
                return 
                [
                    'id_solicitudAsf'=>$data['id_solicitudAsf'],
                    'nombreCompañia'=>$data['nombreCompañia'],
                    'compania'=>$data['nombreCompañia'],
                    // 'fechaSolicitud'=>date("d/m/Y",strtotime(str_replace('/','-',$data['fechaSolicitud']))),
                    //'fechaSolicitud'=>date("M-d-Y H:i:s",strtotime(str_replace('/','-',$data['fechaSolicitud'])."+ 16 seconds")),
                    'fechaSolicitud'=>$data['fechaSolicitud'],
                    'FechaSolicitudExcel'=>date("M-d-Y H:i:s",strtotime(str_replace('/','-',$data['fechaSolicitud'])."+ 16 seconds")),
                    'formula'=>$data['formula'],
                    'abscisas'=>$data['abscisas'],
                    'AbscisaInicial'=>strpos($data['abscisas'],'Inicial ')===false?str_replace('K','', str_replace('+','',str_replace(' ','',$data['abscisas']))):str_replace('K','', str_replace('+','',str_replace(' ','',substr($data['abscisas'],strpos($data['abscisas'],'Inicial ')+8,7)))),
                    //'Abscisa Inicial'=>substr($data['abscisas'],strstr($data['abscisas'],'Inicial ')+8,strstr($data['abscisas'],'-')),
                    'AbscisaFinal'=>strpos($data['abscisas'],'Final ')===false?'':str_replace('K','', str_replace('+','',str_replace(' ','',substr($data['abscisas'],strpos($data['abscisas'],'Final ')+6,7)))),
                    'cantidadToneladas'=>$data['cantidadToneladas'], 
                    'FechaHoraProgramacion'=>$data['FechaHoraProgramacion'],
                    'FechaProgramacionExcel'=>str_replace(' ','',$data['FechaHoraProgramacion'])===''?'':date("M-d-Y h:i:s",strtotime(str_replace(' ',' ',str_replace('/','-',$data['FechaHoraProgramacion']))."+ 16 seconds")),
                    'estado'=>$data['estado'] ,
                    'observaciones'=>$data['observaciones'],
                    'CompañiaDestino'=>$data['CompañiaDestino'] ,
                    'companiaD'=>$data['CompañiaDestino'] ,
                    'fechaAceptacion'=>date("m-d-Y H:i:s",strtotime($data['fechaAceptacion'])),
                    'nombre_completo'=>$data['Nombre'].' '.$data['Apellido'] ,
                    'nombre'=>$data['Nombre'].' '.$data['Apellido'] ,
                    'Ubicacion'=>'Tramo: '.$data['tramo'].' Hito: '.$data['hito'] ,
                    'Tramo'=>$data['tramo'],
                    'Hito'=>$data['hito'] ,
                    'toneFaltante'=>$data['toneFaltante'],
                    'CostCode'=>str_replace(',','',str_replace(' ','',str_replace('.','',str_replace('_','',str_replace('-','',$data['CostCode']))))),
                    'Correo'=>$data['Correo'],
                    'fk_LocationID'=>$data['fk_LocationID'],
                    'MSOID'=>$data['MSOID']

                ];
            })
            
        ;
    }
}
