<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Http\Resources\ContratistaCollection;
use App\Models\ts_Contratistas;
use App\Models\ts_Contratos;
use Illuminate\Http\Request;

class ContratistaController extends BaseController
{
   public function ContratistasActivosAPI(Request $request){
    // return  $this->handleResponse(new ContratistaCollection(ts_Contratistas::where('Status','A')),'Consulta exitosa',true);
    return  $this->handleResponse($request, new ContratistaCollection(ts_Contratistas::where('Status','=','A')->orderby('SubcontractorDesc')->get()),'Consulta exitosa',true);
   }

   public function CrearContratistaAPI(Request $request){
            //se valida la informacion recibida
            $validator = Validator::make($request->all(), [
                'CONTRATISTA' => 'required|max:50|min:1',
            ]);
            //si no cumple validaciones imprime error
            if($validator->fails()){
                return $this->handleAlert($validator->errors());
            }
            //se guardan lo datos
            $datos=$request->all();
            //se extrae el numero de contrtistas creados actualmente
            $n_contratistas=ts_Contratistas::where('SubcontractorID','like','C00%')->count();
            //parametros del codigo de contratista en caso de que no exista
            $length=6;

            // se busca el contratista seleccionado y si no existe lo inserta
            $contratista=ts_Contratistas::firstOrCreate(
                ['SubcontractorDesc'=>$datos['CONTRATISTA']],
                ['SubcontractorID'=>'C'.str_pad($n_contratistas+1,$length,"0", STR_PAD_LEFT)]
            );

            return $this->handleResponse($request, '','Dato insertado con exito',true);
   }

   public function EstadoContratistaAPI(Request $request){
            //se valida la informacion recibida
            $validator = Validator::make($request->all(), [
                'CONTRATISTAID' => 'required|max:20|min:1',
            ]);
            //se guardan lo datos
            $datos=$request->all();
            $contratista=ts_Contratistas::find($datos['CONTRATISTAID']);
            switch($contratista->Status){
                case 'A':
                    $contratista->Status='I';
                    $contratista->save();

                    $contratista->refresh();
                    $mensaje='El contratista '.$contratista->SubcontractorDesc.' a sido inactivado';
                    break;

                case 'I':
                    $contratista->Status='A';
                    $contratista->save();
                    $contratista->refresh();
                    $mensaje='El contratista '.$contratista->SubcontractorDesc.' a sido activado';
                    break;
            }

            return $this->handleAlert($mensaje,true);

   }

      public function ContratistaAPI(Request $request, $id){
           $contratista=ts_Contratistas::find($id);
           if(is_null($contratista)){
             return $this->handleAlert('Contratista no existe');
           }
           return $this->handleResponse($request, new ContratistaCollection($contratista),'Consulta exitosa',true);
   }

   public function Contratista($id){
           $contratista=ts_Contratistas::find($id);
           if(is_null($contratista)){
             return false;
           }
           return $contratista;
   }

   public function Contrato($idcontratista,$tipocontrato){

            $contrato=ts_Contratos::firstOrCreate(
                ['ContractID'=>$idcontratista.'-'.$tipocontrato],
                ['SubcontractorID'=>$idcontratista]
            );

            return $contrato;
   }

   public function CrearContratista(Request $request, $name){

            //se extrae el numero de contrtistas creados actualmente
            $n_contratistas=ts_Contratistas::where('SubcontractorID','like','C00%')->count();
            //parametros del codigo de contratista en caso de que no exista
            $length=6;

            // se busca el contratista seleccionado y si no existe lo inserta
            $contratista=ts_Contratistas::firstOrCreate(
                ['SubcontractorDesc'=>$name],
                ['SubcontractorID'=>'C'.str_pad($n_contratistas+1,$length,"0", STR_PAD_LEFT)]
            );

            return $this->handleResponse($request, new ContratistaCollection($contratista),'Contratista insertado con exito',true);
   }

}
