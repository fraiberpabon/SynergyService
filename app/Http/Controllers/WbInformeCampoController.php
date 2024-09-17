<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\estado;
use App\Models\wbHallazgo;
use App\Models\wbInformeCampo;
use App\Models\wbInformeCampoHazHallazgo;
use App\Models\wbRutaNacional;
use App\Models\WbTipoCalzada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Exports\InformeHallazgoExport;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use App\Http\Controllers\SmsController;
use App\Models\Usuarios\usuarios_M;

class WbInformeCampoController extends BaseController implements Vervos
{

    /**
     * @param Request $req
     * Función que
     */
    public function post(Request $req)
    {

        // Validar los campos de la solicitud
        $validator = Validator::make($req->all(), [
            'fechaRegistroDispositivo' => 'required|date_format:Y-m-d',
            'tipoCalzada' => 'required|numeric|min:1',
            'rutaNacional' => 'required|numeric|min:1',
            'hallazgos' => 'required',
            'ubicacionHallazgo' => 'nullable|string|max:6',
            'fotoUno' => 'nullable|regex:/^[a-zA-Z0-9\/\+=]+$/',
            'fotoDos' => 'nullable|regex:/^[a-zA-Z0-9\/\+=]+$/',
            'fotoTres' => 'nullable|regex:/^[a-zA-Z0-9\/\+=]+$/',
            'fotoCuatro' => 'nullable|regex:/^[a-zA-Z0-9\/\+=]+$/',
            'fotoCinco' => 'nullable|regex:/^[a-zA-Z0-9\/\+=]+$/',
            'fotoSeis' => 'nullable|regex:/^[a-zA-Z0-9\/\+=]+$/',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
            Log::error('informes de campo error validador' . $validator->errors());
        }
        $hallazgos = json_decode($req->hallazgos);
        //Log::error('informes de campo 1'.json_encode($hallazgos));
        // Verificar si el tipo de calzada existe
        if (WbTipoCalzada::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->find($req->tipoCalzada) == null) {
            return $this->handleAlert(__('messages.tipo_calzada_no_encontrada'));
        }

        // Verificar si la ruta nacional existe
        if (wbRutaNacional::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->find($req->rutaNacional) == null) {
            return $this->handleAlert(__('messages.ruta_nacional_no_encontrada'));
        }
        // Verificar si hay hallazgos en formato de arreglo
        if (is_array($hallazgos)) {
            foreach ($hallazgos as $data) {
                // Verificar si cada hallazgo existe
                $hallazgo = wbHallazgo::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->find($data->hallazgo);
                if ($hallazgo == null) {
                    return $this->handleAlert(__('messages.hallazgo_no_encontrado'));
                }
                // Verificar si se requiere una descripción para el hallazgo y si se proporciona
                if ($hallazgo->necesita_descripcion != 0 && strlen($data->descripcion_otros)) {
                    return $this->handleAlert(__('messages.hay_un_hallazgo_que_necesita_descripcion_por_favor_agregue_la_descripcion'));
                }
                // Verificar si hay hallazgos duplicados
                $count = 0;
                foreach ($hallazgos as $dataComparar) {
                    if ($data->hallazgo == $dataComparar->hallazgo) {
                        $count++;
                    }
                }
                if ($count > 1) {
                    return $this->handleAlert(__('messages.se_ha_encontrado_duplicados_en_los_hallazgos'));
                }
            }
        }

        // Crear una nueva instancia del modelo wbInformeCampo
        $modeloRegistrar = new wbInformeCampo;

        // Establecer el proyecto y compañía
        $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);

        // Establecer los valores en el modelo
        $modeloRegistrar->fecha_registro_dispositivo = $req->fechaRegistroDispositivo;
        $modeloRegistrar->fecha_registro = $this->traitGetDateTimeNow();
        $modeloRegistrar->fk_id_tipo_calzada = $req->tipoCalzada;
        $modeloRegistrar->fk_id_ruta_nacional = $req->rutaNacional;
        $modeloRegistrar->observacion = $req->observacion;
        $modeloRegistrar->ubicacion_hallazgo = $req->ubicacionHallazgo;
        $modeloRegistrar->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
        $modeloRegistrar->foto_uno = $req->fotoUno;
        $modeloRegistrar->foto_dos = $req->fotoDos;
        $modeloRegistrar->foto_tres = $req->fotoTres;
        $modeloRegistrar->foto_cuatro = $req->fotoCuatro;
        $modeloRegistrar->foto_cinco = $req->fotoCinco;
        $modeloRegistrar->foto_seis = $req->fotoSeis;
        $modeloRegistrar->fk_estado = 12;
        $modeloRegistrar->latitud = $req->latitud;
        $modeloRegistrar->longitud = $req->longitud;
        $modeloRegistrar->hash = $req->hash;

        // Variable para controlar si se guardó el registro correctamente
        $guardado = false;

        // Iniciar una transacción en la base de datos
        DB::beginTransaction();
        try {
            // Bloquear la tabla y obtener el último registro de wbInformeCampo relacionado con el proyecto actual
            $last = wbInformeCampo::select('id_proyecto')
                ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->lockForUpdate()
                ->orderBy('id_informe_campo', 'desc')
                ->first();

            // Establecer el id_proyecto en el modeloRegistrar
            if ($last == null) {
                $modeloRegistrar->id_proyecto = 1;
            } else {
                $modeloRegistrar->id_proyecto = $last->id_proyecto + 1;
            }

            // Guardar el modeloRegistrar en la base de datos
            $guardado = $modeloRegistrar->save();

            // Recargar el modeloRegistrar desde la base de datos
            $modeloRegistrar->fresh();

            // Verificar si hay hallazgos en formato de arreglo
            foreach ($hallazgos as $data) {
                // Crear un nuevo modelo wbHallazgo
                $hallazgoRegistrar = new wbInformeCampoHazHallazgo;
                $hallazgo = wbHallazgo::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->find($data->hallazgo);

                // Establecer los valores en el modelo
                $hallazgoRegistrar->fk_id_informe_campo = $modeloRegistrar->id_informe_campo;
                $hallazgoRegistrar->fk_id_hallazgo = $data->hallazgo;
                $hallazgoRegistrar->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
                $hallazgoRegistrar->fecha_registro = $modeloRegistrar->fecha_registro;
                // Establecer la descripción para el hallazgo si es necesario
                if ($hallazgo->necesita_descripcion != 0) {
                    $hallazgoRegistrar->descripcion_otros = $data->descripcionOtros;
                }
                $hallazgoRegistrar = $this->traitSetProyectoYCompania($req, $hallazgoRegistrar);
                // Guardar el modelo en la base de datos
                $hallazgoRegistrar->save();
            }
            // Confirmar la transacción
            DB::commit();
        } catch (\Exception $e) {
            // Deshacer la transacción en caso de excepción
            DB::rollback();
            // Manejar la excepción y devolver una alerta
            return $this->handleAlert(__('messages.ocurrio_un_error_al_registrar_el_informe_de_campo_intente_de_nuevo_si_el_error_persiste_consulte_con_el_administrador'));
        }
        // Verificar si el registro se guardó correctamente
        if ($guardado) {
            $consultar = wbInformeCampo::select(
                'id_informe_campo',
                'id_proyecto'
            )
                ->where('id_proyecto', $modeloRegistrar->id_proyecto)
                ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->first();
            // Devolver una respuesta exitosa con un mensaje de éxito
            return $this->handleResponse($req, $this->informeCampoToModel($consultar), __('messages.informe_de_campo_registrado'));
        } else {
            // Devolver una alerta en caso de que no se haya guardado correctamente
            return $this->handleAlert(__('messages.ocurrio_un_error_al_registrar_el_informe_de_campo_intente_de_nuevo_si_el_error_persiste_consulte_con_el_administrador'));
        }
    }


    public function obtenerNombreUsuario(Request $req)
    {
        // Obtener el usuario basado en los parámetros proporcionados en la solicitud
        $usuario = usuarios_M::where('id_usuarios', $this->traitGetIdUsuarioToken($req))
            ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
            ->first();

        // Verificar si el usuario fue encontrado
        if ($usuario) {
            // Asignar el nombre del usuario a la variable $Nombre_usuario
            $Nombre_usuario = $usuario->Nombre; // Asegúrate de que 'nombre' es el nombre de la columna que contiene el nombre del usuario
        } else {
            // Manejar el caso cuando no se encuentra el usuario
            $Nombre_usuario = null; // o cualquier otro valor que desees retornar en caso de no encontrar al usuario
        }

        // Retornar el nombre del usuario
        return $Nombre_usuario;
    }


    /**
     * Funcion para Cerrar Hallazgos
     * Funcionamiento una vez seleccionado el hallazgo a cerrar
     * se valida los campos
     *  - id_informe_campo
     *  - observaciones_cierre
     *  - foto_cierre1
     *  - foto_cierre2
     *  Una vez validados y que cumplan procede actualizar
     * el registro en la base de datos.
     */
    public function CerrarHallazgos(Request $req)
    {
        // Validar los campos de la solicitud para el cierre de los hallazgos
        // foto cierre uno es requerido.
        // id_informe__campo que exista.
        // observaciones_cierre que minimo contenga 3 caracteres.
        // foto cierre 1 es requerido
        $validator = Validator::make($req->all(), [
            'id_informe_campo' => 'required|integer|exists:Wb_informe_campo,id_informe_campo',
            'observaciones_cierre' => 'nullable|string|min:3|max:250',
            'foto_cierre1' => 'required||regex:/^[a-zA-Z0-9\/\+=]+$/',
            'foto_cierre2' => 'nullable|regex:/^[a-zA-Z0-9\/\+=]+$/',
            'fecha_cierre' => 'required|date|date_format:Y-m-d',
        ]);
        //validamos que el formulario no contenga errores
        // validamos respecto a validator
        // en caso fallen se muestran las siguientes alertas con handleAlert

        if ($validator->fails()) {
            if ($validator->errors()->has('id_informe_campo')) {
                return $this->handleAlert(__('messages.id_campo_no_existe'));
                //return $this->handleAlert(json_encode($req->all()));
            }
            if ($validator->errors()->has('observaciones_cierre')) {
                return $this->handleAlert(__('messages.observaciones_min_3'));
            }
            if ($validator->errors()->has('foto_cierre1')) {
                return $this->handleAlert(__('messages.foto_cierre1'));
            }
            if ($validator->errors()->has('foto_cierre2')) {
                return $this->handleAlert(__('messages.fotocierre2'));
            }
            if ($validator->errors()->has('fecha_cierre')) {
                return $this->handleAlert('El formato de la fecha es incorrecto');
            }
            return $this->handleAlert($validator->errors());
        }

        //recogemos datos
        $datos = $req->all();

        //buscamos el codigo por proyectos y recogemos el primero
        // para extraer el id_informe_campo y realizar la consulta de la fecha_Registro
        $Hallazgos = wbInformeCampo::where('id_informe_campo', $datos['id_informe_campo'])
            ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
            ->first();
        //Obtenemos el id_informe Campo y fecha de registro
        if ($Hallazgos) {
            $Fecha_Registro = $Hallazgos->fecha_registro_dispositivo;
        }
        // Si la fecha de cierre es mayor que la fecha de registro
        // Actualizamos los datos
        // de lo contrario mostramos el error mediante un Handle Alert
        if ($datos['fecha_cierre'] >= $Fecha_Registro) {
            $Hallazgos->fecha_cierre = $datos['fecha_cierre'];
            $Hallazgos->observaciones_cierre = $datos['observaciones_cierre'];
            $Hallazgos->foto_cierre1 = $datos['foto_cierre1'];
            $Hallazgos->foto_cierre2 = $datos['foto_cierre2'];
            // Obtenemos el Token
            $Hallazgos->fk_user_update = $this->traitGetIdUsuarioToken($req);
            // establecemos el estado en 36 que es Reparado
            $Hallazgos->fk_estado = 36;
            /**
             * Estructura del mensaje y atributos necesarios, donde :
             * 'numero' => numero telefonico de a quien se quiere notificar
             * 'mensaje' => 'mensaje de texto'
             * 'nota'=> Respuesta exitosa Modulo + Respuesta exitosa
             */


            $Hallazgos->save();
        } else {
            return $this->handleAlert(__('messages.fecha_cierre'));
        }


        // Devolvemos el modelo que se actualizo correctamente
        if ($Hallazgos) {
            $consultar = wbInformeCampo::select(
                'id_informe_campo',
                'id_proyecto',
                'fecha_cierre',
                'observaciones_cierre',
                'foto_cierre1',
                'foto_cierre2',
                'fk_user_update',
                'fk_estado',
                'fk_id_project_Company',
                'updated_at',
                'fecha_registro_dispositivo'
            )
                ->where('id_proyecto', $Hallazgos->id_proyecto)
                ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                ->first();
            // Devolver una respuesta exitosa con un mensaje de éxito
            return $this->handleResponse($req, $this->informeCampoUpdateToModel($consultar), __('messages.informe_de_campo_actualizado'));
        } else {
            // Devolver una alerta en caso de que no se haya guardado correctamente
            return $this->handleAlert(__('messages.ocurrio_un_error_al_registrar_el_informe_de_campo_intente_de_nuevo_si_el_error_persiste_consulte_con_el_administrador'));
        }
    }




    public function postMasivo(Request $req)
    {
        $datosRegistrados = array();

        try {
            $informesDeCampo = json_decode($req->datos);
            // Log::error('informes de campo'. $req);
            if (is_array($informesDeCampo)) {
                foreach ($informesDeCampo as $informeDeCampo) {
                    $informeDeCampo = json_decode($informeDeCampo);
                    $hallazgos = json_decode($informeDeCampo->hallazgos);
                    // Verificar si el tipo de calzada existe
                    if (WbTipoCalzada::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->find($informeDeCampo->tipoCalzada) == null) {
                        array_push($datosRegistrados, ['hash' => $informeDeCampo->hash, 'estado' => 0]);
                        // Log::error($informeDeCampo->hash.' error en calzada. Dato: '.$informeDeCampo->tipoCalzada);
                        continue;
                    }

                    // Verificar si la ruta nacional existe
                    if (wbRutaNacional::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->find($informeDeCampo->rutaNacional) == null) {
                        array_push($datosRegistrados, ['hash' => $informeDeCampo->hash, 'estado' => 0]);
                        // Log::error($informeDeCampo->hash.' error en ruta nacional. Dato: '.$informeDeCampo->rutaNacional);
                        continue;
                    }
                    // Verificar si hay hallazgos en formato de arreglo
                    if (is_array($hallazgos)) {
                        foreach ($hallazgos as $data) {
                            $data = json_decode($data);
                            // Verificar si cada hallazgo existe
                            $hallazgo = wbHallazgo::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->find($data->hallazgo);
                            if ($hallazgo == null) {
                                array_push($datosRegistrados, ['hash' => $informeDeCampo->hash, 'estado' => 0]);
                                // Log::error($informeDeCampo->hash.' error en hallazgo. Dato: '.$data->hallazgo);
                                break;
                            }
                            // Verificar si se requiere una descripción para el hallazgo y si se proporciona
                            if ($hallazgo->necesita_descripcion != 0 && strlen($data->descripcionOtros) == 0) {
                                array_push($datosRegistrados, ['hash' => $informeDeCampo->hash, 'estado' => 0]);
                                // Log::error($informeDeCampo->hash.' error en hallazgo sin descripcion. Dato: '.$data->hallazgo);
                                break;
                            }
                            // Verificar si hay hallazgos duplicados
                            $count = 0;
                            foreach ($hallazgos as $dataComparar) {
                                $dataComparar = json_decode($dataComparar);
                                if ($data->hallazgo == $dataComparar->hallazgo) {
                                    $count++;
                                }
                            }
                            if ($count > 1) {
                                array_push($datosRegistrados, ['hash' => $informeDeCampo->hash, 'estado' => 0]);
                                // Log::error($informeDeCampo->hash.' hallazgos duplicados');
                                break;
                            }
                        }
                    }

                    // Crear una nueva instancia del modelo wbInformeCampo
                    $modeloRegistrar = new wbInformeCampo;

                    // Establecer el proyecto y compañía
                    $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);

                    // Establecer los valores en el modelo
                    $modeloRegistrar->fecha_registro_dispositivo = $informeDeCampo->fechaRegistroDispositivo;
                    $modeloRegistrar->fecha_registro = $this->traitGetDateTimeNow();
                    $modeloRegistrar->fk_id_tipo_calzada = $informeDeCampo->tipoCalzada;
                    $modeloRegistrar->fk_id_ruta_nacional = $informeDeCampo->rutaNacional;
                    $modeloRegistrar->observacion = $informeDeCampo->observacion;
                    $modeloRegistrar->ubicacion_hallazgo = $informeDeCampo->ubicacionHallazgo;
                    $modeloRegistrar->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
                    $modeloRegistrar->foto_uno = $informeDeCampo->fotoUno;
                    $modeloRegistrar->foto_dos = $informeDeCampo->fotoDos;
                    $modeloRegistrar->foto_tres = $informeDeCampo->fotoTres;
                    $modeloRegistrar->foto_cuatro = $informeDeCampo->fotoCuatro;
                    $modeloRegistrar->foto_cinco = $informeDeCampo->fotoCinco;
                    $modeloRegistrar->foto_seis = $informeDeCampo->fotoSeis;
                    $modeloRegistrar->fk_estado = 12;
                    $modeloRegistrar->hash = $informeDeCampo->hash;
                    $modeloRegistrar->latitud = $informeDeCampo->latitud;
                    $modeloRegistrar->longitud = $informeDeCampo->longitud;

                    // Variable para controlar si se guardó el registro correctamente
                    $guardado = false;

                    // Iniciar una transacción en la base de datos
                    DB::beginTransaction();
                    try {
                        // Bloquear la tabla y obtener el último registro de wbInformeCampo relacionado con el proyecto actual
                        $last = wbInformeCampo::select('id_proyecto')
                            ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
                            ->lockForUpdate()
                            ->orderBy('id_informe_campo', 'desc')
                            ->first();

                        // Establecer el id_proyecto en el modeloRegistrar
                        if ($last == null) {
                            $modeloRegistrar->id_proyecto = 1;
                        } else {
                            $modeloRegistrar->id_proyecto = $last->id_proyecto + 1;
                        }

                        // Guardar el modeloRegistrar en la base de datos
                        $guardado = $modeloRegistrar->save();

                        // Recargar el modeloRegistrar desde la base de datos
                        $modeloRegistrar->fresh();

                        // Verificar si hay hallazgos en formato de arreglo
                        foreach ($hallazgos as $data) {
                            $data = json_decode($data);
                            // Crear un nuevo modelo wbHallazgo
                            $hallazgoRegistrar = new wbInformeCampoHazHallazgo;
                            $hallazgo = wbHallazgo::where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))->find($data->hallazgo);

                            // Establecer los valores en el modelo
                            $hallazgoRegistrar->fk_id_informe_campo = $modeloRegistrar->id_informe_campo;
                            $hallazgoRegistrar->fk_id_hallazgo = $data->hallazgo;
                            $hallazgoRegistrar->fk_id_usuarios = $this->traitGetIdUsuarioToken($req);
                            $hallazgoRegistrar->fecha_registro = $modeloRegistrar->fecha_registro;
                            // Establecer la descripción para el hallazgo si es necesario
                            if ($hallazgo->necesita_descripcion != 0) {
                                $hallazgoRegistrar->descripcion_otros = $data->descripcionOtros;
                            }
                            $hallazgoRegistrar = $this->traitSetProyectoYCompania($req, $hallazgoRegistrar);
                            // Guardar el modelo en la base de datos
                            $hallazgoRegistrar->save();
                        }
                        // Confirmar la transacción
                        DB::commit();
                    } catch (\Exception $e) {
                        // Deshacer la transacción en caso de excepción
                        DB::rollback();
                        // Manejar la excepción y devolver una alerta
                        array_push($datosRegistrados, ['hash' => $informeDeCampo->hash, 'estado' => 0]);
                        // Log::error($informeDeCampo->hash.' error al guardar la informacion en la base de datos '.$e);
                        break;
                    }
                    // Verificar si el registro se guardó correctamente
                    if ($guardado) {
                        array_push($datosRegistrados, ['hash' => $informeDeCampo->hash, 'estado' => 1, 'identificador' => $modeloRegistrar->id_informe_campo, 'idProyecto' => $modeloRegistrar->id_proyecto]);
                    } else {
                        // Devolver una alerta en caso de que no se haya guardado correctamente
                        array_push($datosRegistrados, ['hash' => $informeDeCampo->hash, 'estado' => 0]);
                        // Log::error($informeDeCampo->hash.' registro no guardado');
                    }
                }
            } else {
                return $this->handleAlert(__('messages.no_hay_hallazgos_que_sincronizar'));
            }
        } catch (\Exception $exc) {
            var_dump($exc);
            return $this->handleAlert($exc);
        }
        return $this->handleResponse($req, $datosRegistrados, 'registrado');
    }

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * Cambia el estado de un informe de campo.
     *
     * @param  \Illuminate\Http\Request  $req
     * @param  int  $id
     * @param  int  $estado
     * @return \Illuminate\Http\JsonResponse
     */
    public function cambiarEstado(Request $req, $id, $estado)
    {
        // Verificar si el ID del informe de campo es válido
        if (!is_numeric($id)) {
            return $this->handleAlert(__('messages.informe_de_campo_no_valido'));
        }

        // Buscar el informe de campo en la base de datos por su ID de proyecto y proyecto
        $informeCampo = wbInformeCampo::where('id_proyecto', $id)
            ->where('fk_id_project_Company', $this->traitGetProyectoCabecera($req))
            ->first();

        // Verificar si el informe de campo no fue encontrado
        if ($informeCampo == null) {
            return $this->handleAlert(__('messages.informe_de_campo_no_encontrado'));
        }

        // Verificar si el informe de campo no fue encontrado
        if (!estado::find($req->estado)) {
            return $this->handleAlert(__('messages.estado_no_encontrado'));
        }

        // Actualizar el estado del informe de campo
        $informeCampo->fk_estado = $estado;
        $informeCampo->actualizado_por = $this->traitGetIdUsuarioToken($req);
        $informeCampo->fecha_actualizacion = $this->traitGetDateTimeNow();

        // Guardar los cambios en la base de datos
        if ($informeCampo->save()) {
            return $this->handleResponse($req, [], __('messages.se_cambio_estado_de_informe_de_campo'));
        } else {
            return $this->handleAlert(__('messages.el_estado_de_informe_de_campo_no_se_pudo_cambiar_intete_de_nuevo_si_el_error_persite_consulte_con_el_administrador'));
        }
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }


    /**
     * @param Request $request
     * Función get
     */
    public function get(Request $request)
    {
        $estado = $request->input('estado');
        $rutaNacional = $request->input('rutaNacional');
        $pkInicial = $request->input('pk_inicial');
        $pkFinal = $request->input('pk_final');
        $fecha_registro = $request->input('fecha_registro');
        $fecha_cierre = $request->input('fecha_cierre');
        $is_excel = $request->input('is_excel');

        if (!is_numeric($request->page) || !is_numeric($request->limit)) {
            return $this->handleAlert('Datos invalidos');
        }
        $consulta = wbInformeCampo::with([
            'tipoRuta' => function ($query) {
                $query->select('id_ruta_nacional', 'codigo', 'pk_inicial as pkInicialRuta', 'pk_final', 'nombre');
            }, 'tipoUsuario', 'tipoHallazgo', 'tipoEstado', 'tipoCalzada', 'HallazgosHasHallazgos'
        ]);
        if ($request->conImagen == '1') {
            $consulta = $consulta->select('Wb_informe_campo.*');
        } else {
            $consulta = $consulta->select(
                'Wb_informe_campo.id_informe_campo',
                'Wb_informe_campo.id_proyecto',
                'Wb_informe_campo.fecha_registro_dispositivo',
                'Wb_informe_campo.fecha_registro',
                'Wb_informe_campo.fk_id_tipo_calzada',
                'Wb_informe_campo.fk_id_ruta_nacional',
                'Wb_informe_campo.observacion',
                'Wb_informe_campo.fk_id_usuarios',
                'Wb_informe_campo.fk_id_project_Company',
                'Wb_informe_campo.fk_compañia',
                'Wb_informe_campo.fk_estado',
                'Wb_informe_campo.ubicacion_hallazgo',
                'Wb_informe_campo.hash',
                'Wb_informe_campo.latitud',
                'Wb_informe_campo.longitud',
                'Wb_informe_campo.fecha_cierre',
                'Wb_informe_campo.observaciones_cierre',
                'Wb_informe_campo.fk_user_update',
                'Wb_informe_campo.foto_cierre1',
                'Wb_informe_campo.foto_cierre2'
            );
        }
        if ($estado !== null && $estado !== '') {
            $consulta = $consulta->where('Wb_informe_campo.fk_estado', $estado);
        }

        if ($rutaNacional !== null && $rutaNacional !== '') {
            $consulta = $consulta->where('Wb_informe_campo.fk_id_ruta_nacional', $rutaNacional);
        }

        if ($pkInicial !== null && $pkInicial != '' && $pkFinal !== null && $pkFinal != '') {
            $consulta = $consulta->whereHas('tipoRuta', function ($query) use ($pkInicial, $pkFinal) {
                $query->where('pk_inicial', '>=', $pkInicial)
                    ->where('pk_final', '<=', $pkFinal);
            });
        }

        if ($fecha_registro !== 'null' && $fecha_cierre !== 'null') {
            $consulta = $consulta->where(function ($query) use ($fecha_registro, $fecha_cierre) {
                $query->whereBetween('Wb_informe_campo.fecha_registro_dispositivo', [$fecha_registro, $fecha_cierre])
                    ->orWhereBetween('Wb_informe_campo.fecha_cierre', [$fecha_registro, $fecha_cierre]);
            });
        }


        // Se define el método "get()" para obtener información de informes de campo.
        $consulta = $consulta->orderBy('id_informe_campo', 'desc');
        if ($is_excel && $is_excel == 1) {
            return Excel::download(new InformeHallazgoExport($consulta->get()), 'informe_campo.xlsx');
        }
        // Se aplica un filtro a la consulta utilizando el método "filtrar".
        // El método "filtrar" recibe el request, la consulta y el nombre de la tabla.
        $consulta = $this->filtrar($request, $consulta, 'Wb_informe_campo');
        $contador = clone $consulta;
        $contador = $contador->get();
        $consulta = $consulta->forPage($request->page, $request->limit)->get();
        $limitePaginas = ceil($contador->count() / $request->limit);

        // Se devuelve una respuesta exitosa con los datos obtenidos de la consulta.
        // El método "handleResponse" se encarga de formatear la respuesta y enviarla al cliente.
        // También se incluye un mensaje de éxito.
        return $this->handleResponse($request, $this->informeCampoToArray($consulta), __('messages.consultado'), $limitePaginas);
    }


    /**
     * @param Request $request
     */
    public function getById(Request $request, $id)
    {
        $consulta = wbInformeCampo::with([
            'tipoRuta' => function ($query) {
                $query->select('id_ruta_nacional', 'codigo', 'pk_inicial as pkInicialRuta', 'pk_final', 'nombre');
            }, 'tipoUsuario', 'tipoHallazgo', 'tipoEstado', 'tipoCalzada'
        ])->select(
            'Wb_informe_campo.*',
        )->where('id_informe_campo', $id)
            ->limit(1)
            ->orderBy('id_informe_campo', 'desc');
        // Se aplica un filtro a la consulta utilizando el método "filtrar".
        // El método "filtrar" recibe el request, la consulta y el nombre de la tabla.
        $consulta = $this->filtrar($request, $consulta, 'Wb_informe_campo')->first();
        // Se devuelve una respuesta exitosa con los datos obtenidos de la consulta.
        // El método "handleResponse" se encarga de formatear la respuesta y enviarla al cliente.
        // También se incluye un mensaje de éxito.
        return $this->handleResponse($request, $this->informeCampoToModel($consulta), __('messages.consultado'));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
