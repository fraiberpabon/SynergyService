<?php

namespace App\Http\Controllers\ParteDiario;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\interfaces\Vervos;
use App\Models\ParteDiario\Interrupciones;
use App\Models\ParteDiario\WbInterrupciones;
use App\Models\ParteDiario\WbParteDiario;
use App\Models\ParteDiario\WbDistribucionesParteDiario;
use Illuminate\Support\Facades\Validator;
use Exception;
class InterrupcionesController extends BaseController implements Vervos
{

    public  $fk_id_parte_diario;
    public function post(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'fecha_registro' => 'required|string',
                'fecha_creacion' => 'required|string',
                'fk_equipo_id' => 'required|string',
                'observacion' => 'nullable',
                'fk_turno'=> 'required|string',
                'horometro_inicial' => 'required|string',
                'horometro_final' => 'required|string',
                'matricula_operador' => 'required|string',
                'hash' => 'required|string',
                'estado' => 'required|string',
                'usuario'=> 'required|string',
                'usuario_actualizacion'=> 'nullable',
                'proyecto' => 'required|string',
            
            ]);
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            $id_parte_diario = null;
            $respuesta = collect();
            $respuesta->put('hash', $req->hash);
            $find = WbParteDiario::select('id_parte_diario')->where('hash', $req->hash)->first();
                $model = new WbParteDiario();
                $model->fecha_registro = $req->fecha_registro ? $req->fecha_registro : null;
                $model->fecha_creacion_registro = $req->fecha_creacion  ? $req->fecha_creacion : null;
                $model->fk_equiment_id = $req->fk_equipo_id;
                $model->observacion = $req->observacion ? $req->observacion : null;
                $model->fk_id_seguridad_sitio_turno = $req->fk_turno ? $req->fk_turno : null;
                $model->horometro_inicial = $req->horometro_inicial ? $req->horometro_inicial : null;
                $model->horometro_final = $req->horometro_final ? $req->horometro_final : null;
                $model->fk_matricula_operador = $req->matricula_operador ? $req->matricula_operador : null;
                $model->hash = $req->hash ? $req->hash : null;
                $model->fk_id_user_created = $req->usuario ? $req->usuario : null;
                $model->fk_id_user_updated = $req->usuario_actualizacion ? $req->usuario_actualizacion : null;
                $model->fk_id_project_Company = $req->proyecto ? $req->proyecto : null;
                $model->estado = 1;
                if (!$model->save()) {
                    return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
                }
                $itemRespuesta = collect();
                $itemRespuesta->put('id_parte_diario', $id_parte_diario);
                $itemRespuesta->put('hash', $req->hash ? $req->hash : null);
                $itemRespuesta->put('estado', '1');
                $respuesta->push($itemRespuesta);
                $this->postInterrupciones($req);
                return $this->handleResponse($req, $model, 'Parte diario registrado');
         //   }
        } catch (\Throwable $th) {
            \Log::error('Sy parte diario' . $th->getMessage());
            return $this->handleAlert($th->getMessage());
        }
    }


    public function postInterrupciones(Request $req) {
        try {
            $data = json_decode($req->interrupciones, true);
                if (is_array($data)) {
                    foreach ($data  as $interrupcion) {
                        $ultimoIdParteDiario = null; 
                        $model = new WbDistribucionesParteDiario();
                        if ($ultimoIdParteDiario === null) {
                            $ultimoIdParteDiario = WbParteDiario::orderBy('id_parte_diario', 'desc')->pluck('id_parte_diario')->first();
                        }
                        $model->fk_id_parte_diario = $ultimoIdParteDiario ?? null;
                        $model->descripcion_trabajo = $interrupcion['descripcion_trabajo'] ?? null;
                        $model->fk_id_centro_costo = $interrupcion['fk_centro_costo'] ?? null;
                        $model->hr_trabajo = $interrupcion['hr_trabajo'] ?? null;
                        $model->cant_viajes = $interrupcion['cant_viajes'] ?? null;
                        $model->fk_id_interrupcion = $interrupcion['fk_interrupcion'] ?? null;
                        $model->fk_id_project_Company = $interrupcion['proyecto'] ?? null;
                        $model->fk_id_user_created = $interrupcion['usuario'] ?? null;
                        $model->hash = $interrupcion['hash'] ?? null;
                        $model->fk_id_user_updated = $interrupcion['usuario_actualizacion'] ?? null;
                        $model->fecha_creacion_registro = $interrupcion['fecha_creacion_registro'] ?? null;
                        $model->estado = 1;
                        if (!$model->save()) {
                            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
                        }
                    }

            }
        } catch (\Throwable $th) {
            \Log::error('Sy parte diario' . $th->getMessage());
            return $this->handleAlert($th->getMessage());
        }
    }

    public function postArray(Request $req)
    {
        $usuario = $this->traitGetIdUsuarioToken($req);
        $general = $req->all();
        try {
            $validate = Validator::make($req->all(), [
                'datos' => 'required',
            ]);
    
            if ($validate->fails()) {
                return $this->handleAlert($validate->errors());
            }
    
            $respuesta = collect();
    
            $listaGuardar = json_decode($req->datos, true);
    
            if (is_array($listaGuardar) && sizeof($listaGuardar) > 0) {
                $guardados = 0;
                $idParteDiarioArray = []; // Array para almacenar los id_parte_diario generados
    
                foreach ($listaGuardar as $key => $info) {
                    $validator = Validator::make($info, [
                        'fecha_registro' => 'required|string',
                        'fecha_creacion' => 'required|string',
                        'fk_equipo_id' => 'required|string',
                        'observacion' => 'nullable',
                        'fk_turno' => 'required|string',
                        'horometro_inicial' => 'required|string',
                        'horometro_final' => 'required|string',
                        'matricula_operador' => 'required|string',
                        'hash' => 'required|string',
                        'estado' => 'required|string',
                        'usuario' => 'required|string',
                        'usuario_actualizacion' => 'nullable',
                        'proyecto' => 'required|string',
                    ]);
    
                    if ($validator->fails()) {
                        return $this->handleAlert($validator->errors());
                    }
    
                    $find = WbParteDiario::select('id_parte_diario')->where('hash', $info['hash'])->first();
                    if ($find != null) {
                        $guardados++;
                        $itemRespuesta = collect();
                        $itemRespuesta->put('id_parte_diario', $info['id_parte_diario']);
                        $itemRespuesta->put('estado', '1');
                        $respuesta->push($itemRespuesta);
                        continue;
                    }
    
                    $model_parte_diario = new WbParteDiario();
                    $model_parte_diario->fecha_registro = $info['fecha_registro'] ?? null;
                    $model_parte_diario->fecha_creacion_registro = $info['fecha_creacion'] ?? null;
                    $model_parte_diario->fk_equiment_id = $info['fk_equipo_id'] ?? null;
                    $model_parte_diario->observacion = $info['observacion'] ?? null;
                    $model_parte_diario->fk_id_seguridad_sitio_turno = $info['fk_turno'] ?? null;
                    $model_parte_diario->horometro_inicial = $info['horometro_inicial'] ?? null;
                    $model_parte_diario->horometro_final = $info['horometro_final'] ?? null;
                    $model_parte_diario->estado = 1;
                    $model_parte_diario->fk_id_project_Company = $info['proyecto'] ?? null;
                    $model_parte_diario->fk_matricula_operador = $info['matricula_operador'] ?? null;
                    $model_parte_diario->fk_id_user_created = $info['usuario'] ?? null;
                    $model_parte_diario->hash = $info['hash'] ?? null;
                    $model_parte_diario->fk_id_user_updated = $info['usuario_actualizacion'] ?? null;
    
                    if (!$model_parte_diario->save()) {
                        \Log::error('sync_array_horometers ' . ' Usuario:' . $usuario . ' Error: ' . json_encode($info));
                        continue;
                    }
    
                    $id_parte_diario = $model_parte_diario->id_parte_diario;
                    $idParteDiarioArray[] = $id_parte_diario; // Almacenar el id_parte_diario generado
    
                    $guardados++;
                    $itemRespuesta = collect();
                    $itemRespuesta->put('id_parte_diario', $id_parte_diario);
                    $itemRespuesta->put('hash', $info['hash'] ?? null);
                    $itemRespuesta->put('estado', '1');
                    $respuesta->push($itemRespuesta);
                }
    
                if ($guardados == 0) return $this->handleAlert("empty");

                return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
            } else {
                return $this->handleAlert("empty");
            }
        } catch (\Throwable $th) {
            \Log::error( ' info: ' . json_encode($general) . ' Error: ' . $th->getMessage());
            \Log::error($th->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'));
        }
    }

    public function postArrayDistribuciones(Request $req)
{
    $usuario = $this->traitGetIdUsuarioToken($req);
    $general = $req->all();
    try {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $respuesta = collect();

        $listaGuardar = json_decode($req->datos, true);
        if (is_array($listaGuardar) && sizeof($listaGuardar) > 0) {
            $guardados = 0;
            $ultimoIdParteDiario = null; // Variable para almacenar el último id_parte_diario

            foreach ($listaGuardar as $key => $info) {
                $validator = Validator::make($info, [
                    'fk_parte_diario' => 'nullable',
                    'descripcion_trabajo' => 'nullable',
                    'fk_centro_costo_id' => 'nullable',
                    'hr_trabajo' => 'nullable',
                    'cant_viajes' => 'nullable',
                    'fk_interrupcion' => 'nullable',
                    'proyecto' => 'nullable',
                    'usuario' => 'nullable',
                    'hash' => 'nullable',
                    'fecha_creacion_registro' => 'nullable',
                ]);

                if ($validator->fails()) {
                    return $this->handleAlert($validator->errors());
                }

                // Buscar si ya existe un registro con el mismo hash
                $find = WbDistribucionesParteDiario::select('id_distribuciones')->where('hash', $info['hash'])->first();
                if ($find != null) {
                    $guardados++;
                    $itemRespuesta = collect();
                    $itemRespuesta->put('id_distribuciones', $info['id_distribucion']);
                    $itemRespuesta->put('estado', '1');
                    $respuesta->push($itemRespuesta);
                    continue;
                }

                // Si no existe, crear un nuevo registro
                $model = new WbDistribucionesParteDiario();

                // Obtener el último id_parte_diario si no se ha obtenido antes
                if ($ultimoIdParteDiario === null) {
                    $ultimoIdParteDiario = WbParteDiario::orderBy('id_parte_diario', 'desc')->pluck('id_parte_diario')->first();
                }

                // Asignar el id_parte_diario al modelo
                $model->fk_id_parte_diario = $info['fk_parte_diario_server'] ??null;

                // Asignar los demás campos
                $model->fk_id_centro_costo = $info['fk_centro_costo'] ?? null;
                $model->fecha_creacion_registro = $info['fecha_creacion_registro'] ?? null;
                $model->descripcion_trabajo = $info['descripcion_trabajo'] ?? null;
                $model->hr_trabajo = $info['hr_trabajo'] ?? null;
                $model->fk_id_interrupcion = $info['fk_interrupcion'] ?? null;
                $model->estado = 1;
                $model->hash = $info['hash'] ?? null;
                $model->fk_id_project_Company = $info['proyecto'] ?? null;
                $model->fk_id_user_created = $info['usuario'] ?? null;
                $model->fk_id_user_updated = $info['usuario_actualizacion'] ?? null;

                // Guardar el modelo
                if (!$model->save()) {
                    \Log::error('sync_array_horometers ' . ' Usuario:' . $usuario . ' Error: ' . json_encode($info));
                    continue;
                }

                // Incrementar el contador de guardados
                $guardados++;

                // Agregar la respuesta
                $itemRespuesta = collect();
                $itemRespuesta->put('id_distribuciones', $info['id_distribucion']);
                $itemRespuesta->put('estado', '1');
                $respuesta->push($itemRespuesta);

                // Si hay un cambio en el id_parte_diario, actualizar la variable
                if (isset($info['fk_parte_diario'])) {
                    $ultimoIdParteDiario = $info['fk_parte_diario'];
                }
            }

            if ($guardados == 0) return $this->handleAlert("empty");
            return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
        } else {
            return $this->handleAlert("empty");
        }
    } catch (\Throwable $th) {
        \Log::error('sync_array_horometers ' . ' Usuario:' . $usuario . ' info: ' . json_encode($general) . ' Error: ' . $th->getMessage());
        \Log::error($th->getMessage());
        return $this->handleAlert(__('messages.error_interno_del_servidor'));
    }
}







  /**
     * Funcion de update no tocar por la interface de vervos
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

      /**
     * Funcion de delete no tocar por la interface de vervos
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

     /**
     * Funcion de get no tocar por la interface de vervos  un comentario
     */
    public function get(Request $request)
    {
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $query = WbInterrupciones::where('estado', 1)
                ->where('fk_id_project_Company', $proyecto);
            $result =$query->get();

            return $this->handleResponse(
                $request,
                $this->WbInterrupcionesToArray($result),
                __('messages.consultado')
            );
        } catch (Exception $e) {
             \Log::error('error get conductores ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }


    public function GetParteDiarioWeb(Request $request){
        try {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $ids = WbParteDiario::where('fk_id_project_Company', $proyecto)->get('id_parte_diario');
        $resultados = collect();
        $ids->chunk(2000)->each(function ($chunk) use (&$resultados) {
            $consulta = WbParteDiario::wherein('id_parte_diario', $chunk)
                ->with([
                    'usuario_creador',
                    'equipos',
                    'turno',
                    'operador',
                    'distribuciones'
                ])->get();
            $resultados = $resultados->merge($consulta);
        });
        //$resultados = $consulta->get();
        $limitePaginas = 0;
        $sorted = $resultados->sortByDesc('id_parte_diario');
        return $this->handleResponse($request, $this->WbParteDiarioToArray($sorted->values()), __('messages.consultado'), $limitePaginas);
        } catch (Exception $e) {
             \Log::error('error al obtener parte diario ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }






    public function GetParteDiarioWeb2(Request $request){
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $query = WbParteDiario::where('estado', 1)
                ->where('fk_id_project_Company', $proyecto);
            $result =$query->get();

            return $this->handleResponse(
                $request,
                $this->WbInterrupcionesToArray($result),
                __('messages.consultado')
            );
        } catch (Exception $e) {
             \Log::error('error get conductores ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }
    
}
