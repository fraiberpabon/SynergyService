<?php

namespace App\Http\Controllers\ParteDiario;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\interfaces\Vervos;
use App\Jobs\AnularParteDiarioAutomatico;
use App\Jobs\FirmarParteDiarioAutomatico;
use App\Models\ParteDiario\Interrupciones;
use App\Models\ParteDiario\WbInterrupciones;
use Illuminate\Support\Facades\DB;
use App\Models\ParteDiario\WbParteDiario;
use App\Models\ParteDiario\WbDistribucionesParteDiario;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use PhpParser\Node\Stmt\Else_;
use Illuminate\Support\Facades\Log;

class InterrupcionesController extends BaseController implements Vervos
{

    public function post(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'fecha_registro' => 'required|string',
                'fecha_creacion' => 'required|string',
                'fk_equipo_id' => 'required|string',
                'observacion' => 'nullable',
                'fk_turno' => 'required|string',
                'horometro_inicial' => 'nullable|numeric',
                'horometro_final' => 'nullable|numeric',
                'kilometraje_inicial' => 'nullable|numeric',
                'kilometraje_final' => 'nullable|numeric',
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
            $id_parte_diario = null;
            $respuesta = collect();
            $respuesta->put('hash', $req->hash);
            $respuesta->put('id_parte_diario', $id_parte_diario);
            $find = WbParteDiario::select('id_parte_diario')
            ->where('fecha_registro',$req->fecha_registro)
            ->where('fk_equiment_id',$req->fk_equipo_id)
            ->where('fk_id_project_Company',$req->proyecto)
            ->where('hash', $req->hash)->first();
            if ($find != null) {
                $respuesta->put('id_servidor', $find->id_parte_diario);
                $respuesta->put('estado', '1');
                $respuesta->put('distribuciones', $this->postInterrupciones($req, $find->id_parte_diario));
                return $this->handleResponse($req, $respuesta, 'Parte diario registrado');
            }
            $model = new WbParteDiario();
            $model->fecha_registro = $req->fecha_registro ? $req->fecha_registro : null;
            $model->fecha_creacion_registro = $req->fecha_creacion ? $req->fecha_creacion : null;
            $model->fk_equiment_id = $req->fk_equipo_id;
            $model->observacion = $req->observacion ? $req->observacion : null;
            $model->fk_id_seguridad_sitio_turno = $req->fk_turno ? $req->fk_turno : null;

            $model->horometro_inicial =  isset($req->horometro_inicial) && !empty($req->horometro_inicial) && $req->horometro_inicial > 0 ? $req->horometro_inicial : null;
            $model->horometro_final = isset($req->horometro_final) && !empty($req->horometro_final) && $req->horometro_final > 0 ? $req->horometro_final : null;
            $model->kilometraje_inicial = isset($req->kilometraje_inicial) && !empty($req->kilometraje_inicial) && $req->kilometraje_inicial > 0 ? $req->kilometraje_inicial : null;
            $model->kilometraje_final = isset($req->kilometraje_final) && !empty($req->kilometraje_final) && $req->kilometraje_final > 0 ? $req->kilometraje_final : null;

            /* if (isset($req->horometro_inicial) && !empty($req->horometro_inicial) && $req->horometro_inicial > 0) {
                $model->horometro_inicial = $req->horometro_inicial;
            }
            if (isset($req->horometro_final) && !empty($req->horometro_final) && $req->horometro_final > 0) {
                $model->horometro_final = $req->horometro_final;
            }
            if (isset($req->kilometraje_inicial) && !empty($req->kilometraje_inicial) && $req->kilometraje_inicial > 0) {
                $model->kilometraje_inicial = $req->kilometraje_inicial;
            }
            if (isset($req->kilometraje_final) && !empty($req->kilometraje_final) && $req->kilometraje_final > 0) {
                $model->kilometraje_final = $req->kilometraje_final;
            } */

            $model->fk_matricula_operador = $req->matricula_operador ? $req->matricula_operador : null;
            $model->hash = $req->hash ? $req->hash : null;
            $model->fk_id_user_created = $req->usuario ? $req->usuario : null;
            $model->fk_id_user_updated = $req->usuario_actualizacion ? $req->usuario_actualizacion : null;

            $model->fk_id_project_Company = $req->proyecto ? $req->proyecto : null;
            $model->estado = 1;
            if (!$model->save()) {
                $respuesta->put('estado', '0');
                return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
            }
            // dispatch para anular parte diario automatico
            AnularParteDiarioAutomatico::dispatch(
                $model->fk_equiment_id,
                $model->fk_id_user_created,
                $model->fk_id_seguridad_sitio_turno,
                $model->fecha_registro
            );


            FirmarParteDiarioAutomatico::dispatch(
                        $model->fk_equiment_id,
                        $model->id_parte_diario,
                        $model->fk_id_project_Company
                    );

            $respuesta->put('estado', '1');
            $respuesta->put('id_servidor', $model->id_parte_diario);
            $respuesta->put('distribuciones', $this->postInterrupciones($req, $model->id_parte_diario));
            return $this->handleResponse($req, $respuesta, 'Parte diario registrado');
            //   }
        } catch (\Throwable $th) {
            Log::error('Sy parte diario' . $th->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }


    public function postInterrupciones(Request $req, $id_parte_diario)
    {
        try {
            //Log::info($req);
            $respuesta = collect();
            $data = json_decode($req->interrupciones, true);
            if (is_array($data)) {
                foreach ($data as $interrupcion) {
                    $ultimoIdParteDiario = null;
                    $model = new WbDistribucionesParteDiario();

                    $model->fk_id_parte_diario = $id_parte_diario;
                    $model->descripcion_trabajo = $interrupcion['descripcion_trabajo'] ?? null;
                    $model->fk_id_centro_costo = $interrupcion['fk_centro_costo'] ?? null;
                    $model->hr_trabajo = $interrupcion['hr_trabajo'] ?? null;
                    $model->cant_viajes = $interrupcion['cant_viajes'] ?? null;
                    $model->fk_id_interrupcion = $interrupcion['fk_interrupcion'] ?? null;
                    $model->fk_id_project_Company = $interrupcion['proyecto'] ?? null;
                    $model->fk_id_user_created = $interrupcion['usuario'] ?? null;
                    $model->hash = $interrupcion['hash'] ?? null;
                    $model->fk_id_user_updated = $interrupcion['usuario_actualizacion'] ?? null;
                    $model->fk_interrupcion_motivo = $interrupcion['fk_motivo_interrupcion'] ?? null;
                    $model->fecha_creacion_registro = isset($interrupcion['fecha_creacion_registro'])
                        ? date('Y-m-d H:i:s.v', strtotime($interrupcion['fecha_creacion_registro']))
                        : null;
                    $model->estado = 1;
                    try {
                        if (!$model->save()) {
                            $itemRespuesta = collect();
                            $itemRespuesta->put('hash', $interrupcion['hash'] ?? null);
                            $itemRespuesta->put('estado', '0');
                            $respuesta->push($itemRespuesta);
                            continue;
                        }
                    } catch (\Exception $e) {
                        $itemRespuesta = collect();
                        $itemRespuesta->put('hash', $interrupcion['hash'] ?? null);
                        $itemRespuesta->put('estado', '0');
                        $respuesta->push($itemRespuesta);
                        continue;
                        //\Log::error('Sy parte diario' . $e->getMessage());
                    }


                    $itemRespuesta = collect();
                    $itemRespuesta->put('id_servidor', $model->id_distribuciones);
                    $itemRespuesta->put('hash', $interrupcion['hash'] ?? null);
                    $itemRespuesta->put('estado', '1');
                    $respuesta->push($itemRespuesta);
                }
                return $respuesta;
            }
        } catch (\Throwable $th) {
            \Log::error('2' . $th->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }

    // public function postArray(Request $req)
    // {
    //     //Log::info($req);
    //     $usuario = $this->traitGetIdUsuarioToken($req);
    //     $general = $req->all();
    //     try {
    //         $validate = Validator::make($req->all(), [
    //             'datos' => 'required',
    //         ]);

    //         if ($validate->fails()) {
    //             return $this->handleAlert($validate->errors());
    //         }

    //         $respuesta = collect();

    //         $listaGuardar = json_decode($req->datos, true);

    //         if (is_array($listaGuardar) && sizeof($listaGuardar) > 0) {
    //             $guardados = 0;
    //             $idParteDiarioArray = []; // Array para almacenar los id_parte_diario generados

    //             foreach ($listaGuardar as $key => $info) {
    //                 $validator = Validator::make($info, [
    //                     'fecha_registro' => 'required|string',
    //                     'fecha_creacion' => 'required|string',
    //                     'fk_equipo_id' => 'required|string',
    //                     'observacion' => 'nullable',
    //                     'fk_turno' => 'required|string',
    //                     'horometro_inicial' => 'nullable',
    //                     'horometro_final' => 'nullable',
    //                     'kilometraje_inicial' => 'nullable',
    //                     'kilometraje_final' => 'nullable',
    //                     'matricula_operador' => 'required|string',
    //                     'hash' => 'required|string',
    //                     'estado' => 'required|string',
    //                     'usuario' => 'required|string',
    //                     'usuario_actualizacion' => 'nullable',
    //                     'proyecto' => 'required|string',
    //                 ]);

    //                 if ($validator->fails()) {
    //                     return $this->handleAlert($validator->errors());
    //                 }
    //                  $find = WbParteDiario::select('id_parte_diario')
    //                 ->where('fecha_registro',$info['fecha_registro'])
    //                 ->where('fk_equiment_id',$info['fk_equipo_id'])
    //                 ->where('fk_id_project_Company',$info['proyecto'])
    //                 ->where('hash', $info['hash'])->first();
    //                 //\Log::info($find);
    //                 $model_parte_diario = new WbParteDiario();
    //                 if ($info['estado'] == 2) {
    //                     if ($find != null) {
    //                         $fechaFormateada = Carbon::now()->format('d-m-Y H:i:s.v');
    //                         $find->estado = 0;
    //                         $find->fk_usuario_anulacion = $usuario;
    //                         $find->motivo_anulacion = $info['motivo_anulacion'] ?? null;
    //                         $find->fecha_anulacion = $fechaFormateada;
    //                         if ($find->save()) {
    //                             $guardados++;
    //                             $itemRespuesta = collect();
    //                             $itemRespuesta->put('estado', '3');
    //                             $itemRespuesta->put('hash', $info['hash']);
    //                             $respuesta->push($itemRespuesta);
    //                             continue;
    //                         }
    //                     } else {
    //                         $guardados++;
    //                         $itemRespuesta = collect();
    //                         $itemRespuesta->put('estado', '3');
    //                         $itemRespuesta->put('hash', $info['hash']);
    //                         $respuesta->push($itemRespuesta);
    //                         continue;
    //                     }
    //                 } else {
    //                     if ($find != null) {
    //                         $guardados++;
    //                         $itemRespuesta = collect();
    //                         $itemRespuesta->put('id_servidor', $find->id_parte_diario);
    //                         $itemRespuesta->put('hash', $info['hash']);
    //                         $itemRespuesta->put('estado', '1');
    //                         $respuesta->push($itemRespuesta);
    //                         continue;
    //                     }
    //                     $model_parte_diario->fecha_registro = $info['fecha_registro'] ?? null;
    //                     $model_parte_diario->fecha_creacion_registro = $info['fecha_creacion'] ?? null;
    //                     $model_parte_diario->fk_equiment_id = $info['fk_equipo_id'] ?? null;
    //                     $model_parte_diario->observacion = $info['observacion'] ?? null;
    //                     $model_parte_diario->fk_id_seguridad_sitio_turno = $info['fk_turno'] ?? null;
    //                     if (isset($info['horometro_inicial']) && is_numeric($info['horometro_inicial'])) {
    //                         $model_parte_diario->horometro_inicial = $info['horometro_inicial'];
    //                     }
    //                     if (isset($info['horometro_final']) && is_numeric($info['horometro_final'])) {
    //                         $model_parte_diario->horometro_final = $info['horometro_final'];
    //                     }
    //                     if (isset($info['kilometraje_inicial']) && is_numeric($info['kilometraje_inicial'])) {
    //                         $model_parte_diario->kilometraje_inicial = $info['kilometraje_inicial'];
    //                     }
    //                     if (isset($info['kilometraje_final']) && is_numeric($info['kilometraje_final'])) {
    //                         $model_parte_diario->kilometraje_final = $info['kilometraje_final'];
    //                     }
    //                     $model_parte_diario->estado = 1;
    //                     $model_parte_diario->fk_id_project_Company = $info['proyecto'] ?? null;
    //                     $model_parte_diario->fk_matricula_operador = $info['matricula_operador'] ?? null;
    //                     $model_parte_diario->fk_id_user_created = $info['usuario'] ?? null;
    //                     $model_parte_diario->hash = $info['hash'] ?? null;
    //                     $model_parte_diario->motivo_anulacion = $info['motivo_anulacion'] ?? null;
    //                     $model_parte_diario->fk_id_user_updated = $info['usuario_actualizacion'] ?? null;
    //                     try {
    //                         if (!$model_parte_diario->save()) {
    //                             $itemRespuesta = collect();
    //                             $itemRespuesta->put('estado', '0');
    //                             $itemRespuesta->put('hash', $info['hash']);
    //                             $respuesta->push($itemRespuesta);

    //                             continue;
    //                         }
    //                     } catch (\Exception $e) {
    //                         $itemRespuesta = collect();
    //                         $itemRespuesta->put('estado', '0');
    //                         $itemRespuesta->put('hash', $info['hash']);
    //                         $respuesta->push($itemRespuesta);
    //                         Log::error('error al insertar parte diario' . $e->getMessage());
    //                         continue;
    //                     }
    //                     // dispatch para anular parte diario automatico
    //                     AnularParteDiarioAutomatico::dispatch(
    //                         $model_parte_diario->fk_equiment_id,
    //                         $model_parte_diario->fk_id_user_created,
    //                         $model_parte_diario->fk_id_seguridad_sitio_turno,
    //                         $model_parte_diario->fecha_registro
    //                     );

    //                     $id_parte_diario = $model_parte_diario->id_parte_diario;
    //                     $idParteDiarioArray[] = $id_parte_diario; // Almacenar el id_parte_diario generado

    //                     $guardados++;
    //                     $itemRespuesta = collect();
    //                     $itemRespuesta->put('id_servidor', $id_parte_diario);
    //                     $itemRespuesta->put('hash', $info['hash'] ?? null);
    //                     $itemRespuesta->put('estado', '1');
    //                     $respuesta->push($itemRespuesta);
    //                 }
    //             }
    //             if ($guardados == 0)
    //                 return $this->handleAlert("empty");

    //             return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
    //         } else {
    //             return $this->handleAlert("empty");
    //         }
    //     } catch (\Throwable $th) {
    //         Log::error($th->getMessage());
    //         return $this->handleAlert(__('messages.error_servicio'));
    //     }
    // }

    // public function postArrayDistribuciones(Request $req)
    // {
    //     $usuario = $this->traitGetIdUsuarioToken($req);
    //     $general = $req->all();
    //     try {
    //         $validate = Validator::make($req->all(), [
    //             'datos' => 'required',
    //         ]);

    //         if ($validate->fails()) {
    //             return $this->handleAlert($validate->errors());
    //         }

    //         $respuesta = collect();

    //         $listaGuardar = json_decode($req->datos, true);
    //         if (is_array($listaGuardar) && sizeof($listaGuardar) > 0) {
    //             $guardados = 0;


    //             foreach ($listaGuardar as $key => $info) {
    //                 $validator = Validator::make($info, [
    //                     'fk_parte_diario' => 'nullable',
    //                     'descripcion_trabajo' => 'nullable',
    //                     'fk_centro_costo_id' => 'nullable',
    //                     'hr_trabajo' => 'nullable',
    //                     'cant_viajes' => 'nullable',
    //                     'fk_interrupcion' => 'nullable',
    //                     'proyecto' => 'nullable',
    //                     'usuario' => 'nullable',
    //                     'hash' => 'nullable',
    //                     'fecha_creacion_registro' => 'nullable',
    //                 ]);

    //                 if ($validator->fails()) {
    //                     return $this->handleAlert($validator->errors());
    //                 }



    //                 // Buscar si ya existe un registro con el mismo hash
    //                 $find = WbDistribucionesParteDiario::select('id_distribuciones')->where('hash', $info['hash'])->first();
    //                 if ($find != null) {
    //                     $guardados++;
    //                     $itemRespuesta = collect();
    //                     $itemRespuesta->put('id_servidor', $find->id_distribuciones);
    //                     $itemRespuesta->put('hash', $info['hash']);
    //                     $itemRespuesta->put('estado', '1'); // Estado 1 porque ya existe
    //                     $respuesta->push($itemRespuesta);
    //                     continue;
    //                 }

    //                 // Si no existe, crear un nuevo registro
    //                 $model = new WbDistribucionesParteDiario();
    //                 // Asignar el id_parte_diario al modelo
    //                 $model->fk_id_parte_diario = $info['fk_parte_diario_server'] ?? null;

    //                 // Asignar los demás campos
    //                 $model->fk_id_centro_costo = $info['fk_centro_costo'] ?? null;
    //                 $model->fecha_creacion_registro = $info['fecha_creacion_registro'] ?? null;
    //                 $model->descripcion_trabajo = $info['descripcion_trabajo'] ?? null;
    //                 $model->hr_trabajo = $info['hr_trabajo'] ?? null;
    //                 $model->fk_id_interrupcion = $info['fk_interrupcion'] ?? null;
    //                 $model->estado = 1; // Estado 1 porque se guardó correctamente
    //                 $model->hash = $info['hash'] ?? null;
    //                 $model->fk_id_project_Company = $info['proyecto'] ?? null;
    //                 $model->fk_id_user_created = $info['usuario'] ?? null;
    //                 $model->fk_id_user_updated = $info['usuario_actualizacion'] ?? null;
    //                 $model->motivo_anulacion = $info['motivo_anulacion'] ?? null;
    //                 $model->fecha_anulacion = $info['fecha_anulacion'] ?? null;
    //                 $model->fk_usuario_anulacion = $info['usuario'] ?? null;
    //                 $model->fk_interrupcion_motivo = $info['fk_motivo_interrupcion'] ?? null;


    //                 try {
    //                     if (!$model->save()) {
    //                         $find = WbDistribucionesParteDiario::select('id_distribuciones')->where('hash', $info['hash'])->first();
    //                         $itemRespuesta = collect();
    //                         $itemRespuesta->put('id_servidor', $find->id_distribuciones ?? 'vacio');
    //                         $itemRespuesta->put('estado', '0'); // Estado 0 porque falló el guardado
    //                         $respuesta->push($itemRespuesta);

    //                         continue;
    //                     }
    //                 } catch (\Exception $e) {
    //                     $find = WbDistribucionesParteDiario::select('id_distribuciones')->where('hash', $info['hash'])->first();
    //                     $itemRespuesta = collect();
    //                     $itemRespuesta->put('id_servidor', $find->id_distribuciones ?? 'vacio2');
    //                     $itemRespuesta->put('estado', '0'); // Estado 0 porque falló el guardado
    //                     $respuesta->push($itemRespuesta);
    //                     \Log::error('error al insertar distribuciones' . $e->getMessage());
    //                     continue;
    //                 }
    //                 // Guardar el modelo

    //                 $id_distribuciones = $model->id_distribuciones;
    //                 $idParteDiarioArray[] = $id_distribuciones;
    //                 // Incrementar el contador de guardados
    //                 $guardados++;

    //                 // Agregar la respuesta
    //                 $find = WbDistribucionesParteDiario::select('id_distribuciones')->where('hash', $info['hash'])->first();
    //                 $itemRespuesta = collect();
    //                 $itemRespuesta->put('hash', $model->hash);
    //                 $itemRespuesta->put('id_servidor', $find->id_distribuciones ?? 'vacio3');
    //                 $itemRespuesta->put('estado', '1'); // Estado 1 porque se guardó correctamente
    //                 $respuesta->push($itemRespuesta);
    //             }

    //             if ($guardados == 0)
    //                 return $this->handleAlert("empty");
    //             return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
    //         } else {
    //             return $this->handleAlert("empty");
    //         }
    //     } catch (\Throwable $th) {
    //         \Log::error($th->getMessage());
    //         return $this->handleAlert(__('messages.error_servicio'));
    //     }
    // }


    public function postArrayParteDiarioConDistribuciones(Request $req)
{
    $usuario = $this->traitGetIdUsuarioToken($req);

    try {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $respuesta = collect();
        $listaGuardar = json_decode($req->datos, true);

        if (!is_array($listaGuardar) || sizeof($listaGuardar) === 0) {
            return $this->handleAlert("empty");
        }

        $guardados = 0;

        foreach ($listaGuardar as $key => $info) {
            // Validar estructura del parte diario
            $validator = Validator::make($info, [
                'fecha_registro' => 'required|string',
                'fecha_creacion' => 'required|string',
                'fk_equipo_id' => 'required|string',
                'observacion' => 'nullable',
                'fk_turno' => 'required|string',
                'horometro_inicial' => 'nullable',
                'horometro_final' => 'nullable',
                'kilometraje_inicial' => 'nullable',
                'kilometraje_final' => 'nullable',
                'matricula_operador' => 'required|string',
                'hash' => 'required|string',
                'estado' => 'required|string',
                'usuario' => 'required|string',
                'usuario_actualizacion' => 'nullable',
                'proyecto' => 'required|string',
                'distribuciones' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                Log::error('Validación fallida para parte diario: ' . json_encode($validator->errors()));
                continue;
            }

            // Iniciar transacción para garantizar atomicidad
            DB::beginTransaction();

            try {
                $itemRespuesta = collect();
                $distribucionesRespuesta = collect();

                // Buscar si ya existe el parte diario
                $find = WbParteDiario::where('fecha_registro', $info['fecha_registro'])
                    ->where('fk_equiment_id', $info['fk_equipo_id'])
                    ->where('fk_id_project_Company', $info['proyecto'])
                    ->where('hash', $info['hash'])
                    ->first();

                // === CASO 1: ANULACIÓN ===
                if ($info['estado'] == 2) {
                    if ($find != null) {
                        $fechaFormateada = Carbon::now()->format('d-m-Y H:i:s.v');
                        $find->estado = 0;
                        $find->fk_usuario_anulacion = $usuario;
                        $find->motivo_anulacion = $info['motivo_anulacion'] ?? null;
                        $find->fecha_anulacion = $fechaFormateada;

                        if ($find->save()) {
                            // Anular distribuciones asociadas
                            if (isset($info['distribuciones']) && is_array($info['distribuciones'])) {
                                foreach ($info['distribuciones'] as $dist) {
                                    WbDistribucionesParteDiario::where('hash', $dist['hash'])
                                        ->update([
                                            'estado' => 0,
                                            'fk_usuario_anulacion' => $usuario,
                                            'motivo_anulacion' => $dist['motivo_anulacion'] ?? null,
                                            'fecha_anulacion' => $fechaFormateada
                                        ]);
                                }
                            }

                            DB::commit();
                            $guardados++;
                            $itemRespuesta->put('estado', '3');
                            $itemRespuesta->put('hash', $info['hash']);
                            $respuesta->push($itemRespuesta);
                            continue;
                        }
                    }

                    DB::commit();
                    $guardados++;
                    $itemRespuesta->put('estado', '3');
                    $itemRespuesta->put('hash', $info['hash']);
                    $respuesta->push($itemRespuesta);
                    continue;
                }

                // === CASO 2: PARTE DIARIO YA EXISTE ===
                if ($find != null) {
                    $id_parte_diario = $find->id_parte_diario;

                    // Procesar distribuciones
                    if (isset($info['distribuciones']) && is_array($info['distribuciones'])) {
                        foreach ($info['distribuciones'] as $dist) {
                            $distRespuesta = $this->procesarDistribucion(
                                $dist,
                                $id_parte_diario,
                                $info['proyecto'],
                                $info['usuario']
                            );
                            $distribucionesRespuesta->push($distRespuesta);
                        }
                    }

                    DB::commit();
                    $guardados++;
                    $itemRespuesta->put('id_servidor', $id_parte_diario);
                    $itemRespuesta->put('hash', $info['hash']);
                    $itemRespuesta->put('estado', '1');
                    $itemRespuesta->put('distribuciones', $distribucionesRespuesta);
                    $respuesta->push($itemRespuesta);
                    continue;
                }

                // === CASO 3: CREAR NUEVO PARTE DIARIO ===
                $model_parte_diario = new WbParteDiario();
                $model_parte_diario->fecha_registro = $info['fecha_registro'];
                $model_parte_diario->fecha_creacion_registro = $info['fecha_creacion'];
                $model_parte_diario->fk_equiment_id = $info['fk_equipo_id'];
                $model_parte_diario->observacion = $info['observacion'] ?? null;
                $model_parte_diario->fk_id_seguridad_sitio_turno = $info['fk_turno'];

                // Horometros y kilometrajes
                if (isset($info['horometro_inicial']) && is_numeric($info['horometro_inicial'])) {
                    $model_parte_diario->horometro_inicial = $info['horometro_inicial'];
                }
                if (isset($info['horometro_final']) && is_numeric($info['horometro_final'])) {
                    $model_parte_diario->horometro_final = $info['horometro_final'];
                }
                if (isset($info['kilometraje_inicial']) && is_numeric($info['kilometraje_inicial'])) {
                    $model_parte_diario->kilometraje_inicial = $info['kilometraje_inicial'];
                }
                if (isset($info['kilometraje_final']) && is_numeric($info['kilometraje_final'])) {
                    $model_parte_diario->kilometraje_final = $info['kilometraje_final'];
                }

                $model_parte_diario->estado = 1;
                $model_parte_diario->fk_id_project_Company = $info['proyecto'];
                $model_parte_diario->fk_matricula_operador = $info['matricula_operador'];
                $model_parte_diario->fk_id_user_created = $info['usuario'];
                $model_parte_diario->hash = $info['hash'];
                $model_parte_diario->fk_id_user_updated = $info['usuario_actualizacion'] ?? null;

                if (!$model_parte_diario->save()) {
                    throw new Exception('Error al guardar el parte diario');
                }

                $id_parte_diario = $model_parte_diario->id_parte_diario;

                // Dispatch job para anulación automática
                AnularParteDiarioAutomatico::dispatch(
                    $model_parte_diario->fk_equiment_id,
                    $model_parte_diario->fk_id_user_created,
                    $model_parte_diario->fk_id_seguridad_sitio_turno,
                    $model_parte_diario->fecha_registro
                );


                  FirmarParteDiarioAutomatico::dispatch(
                        $model_parte_diario->fk_equiment_id,
                        $model_parte_diario->id_parte_diario,
                         $model_parte_diario->fk_id_project_Company
                    );


                // Procesar distribuciones del nuevo parte diario
                if (isset($info['distribuciones']) && is_array($info['distribuciones'])) {
                    foreach ($info['distribuciones'] as $dist) {
                        $distRespuesta = $this->procesarDistribucion(
                            $dist,
                            $id_parte_diario,
                            $info['proyecto'],
                            $info['usuario']
                        );
                        $distribucionesRespuesta->push($distRespuesta);
                    }
                }

                DB::commit();
                $guardados++;

                $itemRespuesta->put('id_servidor', $id_parte_diario);
                $itemRespuesta->put('hash', $info['hash']);
                $itemRespuesta->put('estado', '1');
                $itemRespuesta->put('distribuciones', $distribucionesRespuesta);
                $respuesta->push($itemRespuesta);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Error al procesar parte diario [' . $info['hash'] . ']: ' . $e->getMessage());
                Log::error($e->getTraceAsString());

                $itemRespuesta->put('estado', '0');
                $itemRespuesta->put('hash', $info['hash']);
                $itemRespuesta->put('error', $e->getMessage());
                $respuesta->push($itemRespuesta);
                continue;
            }
        }

        if ($guardados == 0) {
            return $this->handleAlert("No se guardaron registros");
        }

        //Fix parte diario no sincronizado
        $partesSinDistribuciones = WbParteDiario::where('fk_id_user_created', $usuario)
        ->where('estado', 1)
            ->doesntHave('distribuciones')
            ->get();

            foreach ($partesSinDistribuciones as $parte) {
                $itemRespuesta = collect();
                $itemRespuesta->put('estado', '0');
                $itemRespuesta->put('hash', $parte->hash);
                $respuesta->push($itemRespuesta);

            }


        return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));

    } catch (\Throwable $th) {
        Log::error('Error general en postArrayParteDiarioConDistribuciones: ' . $th->getMessage());
        Log::error($th->getTraceAsString());
        return $this->handleAlert(__('messages.error_servicio'));
    }
}

/**
 * Método auxiliar para procesar una distribución
 */
private function procesarDistribucion($dist, $id_parte_diario, $proyecto, $usuario)
{
    $respuesta = collect();

    try {
        // Buscar si ya existe la distribución
        $findDist = WbDistribucionesParteDiario::where('hash', $dist['hash'])
        ->where('fk_id_parte_diario',$id_parte_diario)
        ->first();
        //añador por fk_id_parte_diario,
        if ($findDist != null) {
            // Actualizar el fk_id_parte_diario si es necesario
            if ($findDist->fk_id_parte_diario != $id_parte_diario) {
                $findDist->fk_id_parte_diario = $id_parte_diario;
                $findDist->save();
            }

            $respuesta->put('id_servidor', $findDist->id_distribuciones);
            $respuesta->put('hash', $dist['hash']);
            $respuesta->put('estado', '1');
            return $respuesta;
        }

        // Crear nueva distribución
        // Validar que fecha_creacion_registro, hash, y hr_trabajo no sean null
        if (
            (empty($dist['fecha_creacion_registro']) || is_null($dist['fecha_creacion_registro'])) &&
            (empty($dist['hash']) || is_null($dist['hash'])) &&
            (empty($dist['hr_trabajo']) || is_null($dist['hr_trabajo']))
        ) {
            throw new Exception('Los campos fecha_creacion_registro, hash y hr_trabajo no pueden ser todos nulos.');
        }

        $model = new WbDistribucionesParteDiario();
        $model->fk_id_parte_diario = $id_parte_diario;
        $model->fk_id_centro_costo = $dist['fk_centro_costo'] ?? null;
        $model->fecha_creacion_registro = $dist['fecha_creacion_registro'] ?? null;
        $model->descripcion_trabajo = $dist['descripcion_trabajo'] ?? null;
        $model->hr_trabajo = $dist['hr_trabajo'] ?? null;
        $model->fk_id_interrupcion = $dist['fk_interrupcion'] ?? null;
        $model->fk_interrupcion_motivo = $dist['fk_motivo_interrupcion'] ?? null;
        $model->estado = 1;
        $model->hash = $dist['hash'];
        $model->fk_id_project_Company = $proyecto;
        $model->fk_id_user_created = $usuario;
        $model->fk_id_user_updated = $dist['usuario_actualizacion'] ?? null;



        if (!$model->save()) {
            throw new Exception('Error al guardar distribución');
        }

        $respuesta->put('id_servidor', $model->id_distribuciones);
        $respuesta->put('hash', $dist['hash']);
        $respuesta->put('estado', '1');

    } catch (Exception $e) {
        Log::error('Error al procesar distribución [' . ($dist['hash'] ?? 'sin hash') . ']: ' . $e->getMessage());
        $respuesta->put('estado', '0');
        $respuesta->put('hash', $dist['hash'] ?? null);
        $respuesta->put('error', $e->getMessage());
    }

    return $respuesta;
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
            $result = $query->get();

            return $this->handleResponse(
                $request,
                $this->WbInterrupcionesToArray($result),
                __('messages.consultado')
            );
        } catch (Exception $e) {
            \Log::error('error get conductores ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    /*
    Obtener la lista de parte diario con sus respectivas
     * distribuciones
     */

    public function GetParteDiarioWeb(Request $request)
    {
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $resultado = WbParteDiario::where('fk_id_project_Company', $proyecto)
                ->where('estado', 1)
                ->orderBy('id_parte_diario', 'desc')
                ->select(
                    'id_parte_diario',
                    'fecha_registro',
                    'fk_equiment_id',
                    'observacion',
                    'fk_id_seguridad_sitio_turno',
                    'horometro_inicial',
                    'horometro_final',
                    'kilometraje_inicial',
                    'kilometraje_final',
                    'fk_matricula_operador',
                    'fk_id_user_created'
                )
                ->with([
                    'usuario_creador' => function ($query) {
                        $query->select('Nombre', 'Apellido', 'id_usuarios');
                    },
                    'equipos' => function ($query) {
                        $query->select('equiment_id', 'descripcion', 'horometro_inicial', 'id', 'fk_id_tipo_equipo', 'fk_compania');
                    },
                    'turno' => function ($query) {
                        $query->select('id_turnos', 'nombre_turno', 'horas_turno');
                    },
                    'operador' => function ($query) {
                        $query->select('id', 'dni', 'nombreCompleto');
                    },
                    'distribuciones' => function ($query) {
                        $query->with('centro_costo', 'interrupciones')->select('id_distribuciones', 'fk_id_parte_diario', 'fk_id_centro_costo', 'descripcion_trabajo', 'hr_trabajo', 'fk_id_interrupcion', 'fk_id_user_created', 'fk_id_user_updated');
                    },
                    'compania' => function ($query) {
                        $query->select('id_compañia', 'nombreCompañia');
                    },
                    'tipo_equipo' => function ($query) {
                        $query->select('id_tipo_equipo', 'nombre');
                    }
                ])->get();
            return $this->handleResponse($request, $this->WbParteDiarioToArray($resultado), __('messages.consultado'), 0);
        } catch (Exception $e) {
            Log::error('Error al obtener parte diario: ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }


    /**
     * Anular parte diario con sus respectivas distribuciones
     */

    public function AnularParteDiario(Request $request, $id_parte_diario)
    {
        try {
            $fecha_anulacion = $this->traitGetDateTimeNow();
            $fk_usuario_anulacion = $this->traitGetIdUsuarioToken($request);
            if (!is_numeric($id_parte_diario)) {
                return $this->handleAlert(__('messages.validacion_numerico_parte_diario'));
            }
            $motivo = $request->input('motivo');
            if (empty($motivo)) {
                return $this->handleAlert(__('messages.ingrese_motivo_parte_diario'));
            }
            $AnularParteDiario = WbParteDiario::find($id_parte_diario);
            if ($AnularParteDiario == null) {
                return $this->handleAlert(__('messages.parte_diario_no_existe'));
            }
            if ($AnularParteDiario) {
                $AnularParteDiario->motivo_anulacion = $motivo;
                $AnularParteDiario->fk_usuario_anulacion = $fk_usuario_anulacion;
                $AnularParteDiario->fecha_anulacion = $fecha_anulacion;
                $AnularParteDiario->estado = 0;
                $AnularParteDiario->save();
            }

            // Anular todas las distribuciones relacionadas con el mismo id_parte_diario
            $AnularDistribucionesParteDiario = WbDistribucionesParteDiario::where('fk_id_parte_diario', $id_parte_diario)->get();

            foreach ($AnularDistribucionesParteDiario as $distribucion) {
                $distribucion->motivo_anulacion = $motivo;
                $distribucion->fk_usuario_anulacion = $fk_usuario_anulacion;
                $distribucion->fecha_anulacion = $fecha_anulacion;
                $distribucion->estado = 0;
                $distribucion->save();
            }
            return $this->handleAlert(__('messages.parte_diario_anulado'), true);
        } catch (\Exception $e) {
            \Log::error('error al anular parte diario ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }

    /**
     * Funcion que recibe un array de id_parte_diarios con su respectivo hash
     * al encontrar dicho registro se procede anular el parte diario con sus respectivas distribuciones,
     * ir devolviendo uno a uno la respuesta si se anulo correctamente con esto procedemos
     * eliminar del telefono el parte diario que se encuentra anulado en el servidor
     */

    public function AnularParteDiarioMobile(Request $request)
    {
        try {
            $respuesta = [
                'parte_diario' => collect(),
                'distribuciones' => collect()
            ];
            $partesDiarios = $request->input('partes_diarios');
            // Si es un string, decodificarlo
            if (is_string($partesDiarios)) {
                $partesDiarios = json_decode($partesDiarios, true);
                // Verificar si el decode falló
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $this->handleAlert(__('messages.validacion_array_parte_diario'));
                }
            }
            // Validar que sea un array y no esté vacío
            if (!is_array($partesDiarios)) {
                return $this->handleAlert(__('messages.validacion_array_parte_diario'));
            }

            // Filtrar elementos vacíos (opcional)
            $partesDiarios = array_filter($partesDiarios);

            if (empty($partesDiarios)) {
                return $this->handleAlert(__('messages.validacion_array_parte_diario_vacio'));
            }
            foreach ($partesDiarios as $parte) {
                // Validaciones de estructura (sin return, solo marcar error y continuar)
                if (!isset($parte['hash']) || !isset($parte['motivo'])) {
                    continue;
                }

                //  $id_parte_diario = $parte['id_parte_diario'];
                $motivo = $parte['motivo'];
                $fk_usuario = $parte['fk_usuario'];
                $hash = $parte['hash'];

                if (empty($motivo)) {
                    continue;
                }

                // Buscar el parte diario
                $AnularParteDiario = WbParteDiario::where('hash', $hash)->first();
                $id_parte_diario = $AnularParteDiario->id_parte_diario ?? null;

                if (!$AnularParteDiario) {
                    continue;
                }

                // Procesar anulación del parte diario
                try {
                    $fecha_anulacion = $this->traitGetDateTimeNow();

                    $AnularParteDiario->motivo_anulacion = $motivo;
                    $AnularParteDiario->fk_usuario_anulacion = $fk_usuario;
                    $AnularParteDiario->fecha_anulacion = $fecha_anulacion;
                    $AnularParteDiario->estado = 0;

                    if ($AnularParteDiario->save()) {
                        $respuesta['parte_diario']->push([
                            'estado' => '2',
                            'hash' => $AnularParteDiario->hash
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al anular parte diario: ' . $e->getMessage());
                    continue;
                }
                $AnularDistribucionesParteDiario = WbDistribucionesParteDiario::where('fk_id_parte_diario', $id_parte_diario)->get();
                foreach ($AnularDistribucionesParteDiario as $distribucion) {
                    try {
                        $distribucion->motivo_anulacion = $motivo;
                        $distribucion->fk_usuario_anulacion = $fk_usuario;
                        $distribucion->fecha_anulacion = $fecha_anulacion;
                        $distribucion->estado = 0;

                        if ($distribucion->save()) {
                            $respuesta['distribuciones']->push([
                                'estado' => '2',
                                'hash' => $distribucion->hash
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error al anular distribucion: ' . $e->getMessage());
                        continue;
                    }
                }
            }

            // Respuesta final (solo éxitos)
            $respuestaFinal = [
                'parte_diario' => $respuesta['parte_diario']->toArray(),
                'distribuciones' => $respuesta['distribuciones']->toArray()
            ];

            return $this->handleResponse($request, $respuestaFinal, __('messages.registro_exitoso'));
        } catch (\Exception $e) {
            \Log::error('Error general en anulación: ' . $e->getMessage());
            return $this->handleAlert($e->getMessage());
        }
    }



    public function editarParteDiario(Request $request)
    {
        $idParteDiario = $request->idParteDiario;
        $usuarioActualizacion = $this->traitGetIdUsuarioToken($request);
        $fechaActualizacion = $this->traitGetDateNow($request);
        $validacion = $request->validate([
            'idParteDiario' => 'required|string',
            'fechaRegistro' => 'required|date',
            'fkEquipo' => 'required|string',
            'observacion' => 'nullable',
            'horoIni' => 'nullable',
            'horoFin' => 'nullable',
            'operador' => 'required|string',
            'kiloIni' => 'nullable',
            'kiloFin' => 'nullable'
        ]);

        $parteDiario = WbParteDiario::where('id_parte_diario', $idParteDiario)->first();
        if (!$parteDiario) {
            return $this->handleAlert('No se encontró el parte diario', false);
        } else {
            $parteDiario->fecha_registro = $validacion['fechaRegistro'];
            $parteDiario->fecha_creacion_registro = $fechaActualizacion;
            $parteDiario->fk_equiment_id = $validacion['fkEquipo'];
            $parteDiario->observacion = $validacion['observacion'];
            $parteDiario->horometro_inicial = $validacion['horoIni'];
            $parteDiario->horometro_final = $validacion['horoFin'];
            $parteDiario->fk_matricula_operador = $validacion['operador'];
            $parteDiario->fk_id_user_updated = $usuarioActualizacion;
            $parteDiario->updated_at = $fechaActualizacion;
            $parteDiario->kilometraje_inicial = $validacion['kiloIni'];
            $parteDiario->kilometraje_final = $validacion['kiloFin'];
            $parteDiario->save();
        }
        $validacionDistribuciones = $request->validate([
            'distribuciones' => 'required|array',
            'distribuciones.*.id_distribuciones' => 'required|integer',
            'distribuciones.*.fkIdCentroCosto' => 'nullable',
            'distribuciones.*.descripcionTrabajo' => 'nullable|string',
            'distribuciones.*.hrTrabajo' => 'required|numeric',
            'distribuciones.*.fkInterrupcion' => 'nullable'
        ]);

        // Procesar cada distribución individualmente
        foreach ($validacionDistribuciones['distribuciones'] as $distribucionData) {
            $distribucion = WbDistribucionesParteDiario::findOrFail($distribucionData['id_distribuciones']);
            if ($distribucion->fk_id_parte_diario != $idParteDiario) {
                continue;
            }
            // Actualizar la distribución
            $distribucion->fk_id_centro_costo = $distribucionData['fkIdCentroCosto'];
            $distribucion->descripcion_trabajo = $distribucionData['descripcionTrabajo'];
            $distribucion->hr_trabajo = $distribucionData['hrTrabajo'];
            $distribucion->fk_id_interrupcion = $distribucionData['fkInterrupcion'];
            $distribucion->fk_id_user_updated = $usuarioActualizacion;
            $distribucion->updated_at = $fechaActualizacion;
            $distribucion->save();
        }

        return $this->handleResponse($request, [
            'parteDiario' => $parteDiario,
            'distribuciones' => WbDistribucionesParteDiario::where('fk_id_parte_diario', $idParteDiario)->get()
        ], __('messages.registro_exitoso'));
    }
}
