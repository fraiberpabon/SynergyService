<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbProgramadorTarea;
use App\Models\WbTareasProgramadas;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Laboratorio\WbSolicitudMuestraController;
use App\Http\Controllers\Laboratorio\WbTipoControlController;
use App\Http\Controllers\Laboratorio\WbEnsayosController;

class WbTareasProgramadasController extends BaseController implements Vervos
{


    /* aplicacion movil */
    /* public function getMovil(Request $request)
    {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $turnos = WbTareasProgramadas::where('fk_id_project_Company', $proyecto)->where('estado', 1)->get();

        if (count($turnos) == 0) {
            return $this->handleAlert(__('messages.no_tiene_turnos_registrados'), false);
        }
        return $this->handleResponse($request, $this->wbSeguridadSitioTurnoToArray($turnos), __('messages.consultado'));
    } */
    /* fin aplicacion movil */

    /**
     * Obtiene las opciones del módulo según el proyecto y responde con la lista de opciones.
     *
     * @param Request $req La solicitud HTTP.
     *
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con la lista de opciones.
     */
    public function getOpciones(Request $req)
    {
        // Obtiene la lista de opciones
        $array = $this->getListaOpciones();

        // Utiliza array_filter para filtrar el array según el proyecto
        $array = array_filter($array, function ($item) use ($req) {
            return $item['modulo']['proyecto'] == $this->traitGetProyectoCabecera($req);
        });

        // Responde con la lista de opciones en formato JSON
        return $this->handleResponse($req, $array, __('messages.consultado'));
    }

    /**
     * Obtiene la lista de opciones del módulo.
     *
     * @return array La estructura de datos que representa la lista de opciones.
     */
    private function getListaOpciones()
    {
        // Define y devuelve la lista de opciones del módulo
        return array(
            array(
                'modulo' => array(
                    'id' => 1,
                    'descripcion' => __('messages.mod_laboratorio'),
                    'tareas' => array(
                        array('id' => 1, 'descripcion' => __('messages.lab_solicitud_muestra')),
                        /* array('id' => 2, 'descripcion' => __('messages.otra_opcion')), */
                    ),
                    'proyecto' => 1,
                ),
            ),
        );
    }

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method
        $validator = Validator::make($req->all(), [
            'programador' => 'required|numeric',
            'modulo' => 'required|numeric',
            'tarea' => 'required|numeric',
            'parametros' => 'required',
            'descripcion' => 'required|string',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        $modulo = $req->modulo;
        $tarea = $req->tarea;
        if (!$this->validarModuloYTarea($modulo, $tarea, $req->parametros)) {
            return $this->handleAlert(__('messages.faltan_parametros'), false);
        }

        // Consultamos los permisos
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('TAREAS_PROGRAMADAS_CREAR', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        //creamos nueva instacia del modelo
        $modelo = new WbTareasProgramadas;

        //estabelcemos el proyecto y compañia
        $modelo = $this->traitSetProyecto($req, $modelo);

        $modelo->fk_id_programador_tareas = $req->programador;
        $modelo->modulo = $req->modulo;
        $modelo->metodo = $req->tarea;
        $modelo->parametros = json_encode($req->parametros);
        $modelo->descripcion = $req->descripcion;
        $modelo->estado = 1;
        $modelo->prox_ejecucion = DB::raw("CAST('{$this->calcularProximaEjecucion($req->programador, true)}' as DATE)");
        $modelo->fk_id_usuarios_creacion = $usuarioRol->fk_usuario;
        $modelo->fecha_creacion = DB::raw('SYSDATETIME()');

        if (!$modelo->save()) {
            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
        }
        return $this->handleAlert(__('messages.registro_exitoso'), true);
    }

    /**
     * Función para validar el módulo y la tarea con parámetros específicos.
     *
     * @param int $modulo      Identificador del módulo.
     * @param int $tarea       Identificador de la tarea.
     * @param array $parametros Parámetros a ser validados.
     *
     * @return bool            Devuelve true si la validación es exitosa, de lo contrario, devuelve false.
     */
    private function validarModuloYTarea($modulo, $tarea, $parametros)
    {
        /* Condición para validar solo las solicitudes de muestra del laboratorio (Módulo: 1, Tarea: 1) */
        if ($modulo == 1 && $tarea == 1) {
            $lab_solicitud_muesta = new WbSolicitudMuestraController();

            // Llama al método validarCampos del controlador WbLaboratorioSolicitudMuestraController
            // para validar los campos con los parámetros proporcionados.
            if (!$lab_solicitud_muesta->validarCampos($parametros)) {
                return false; // La validación de campos falló.
            }
        }
        return true; // La validación fue exitosa.
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $req)
    {
        // TODO: Implement get() method.
        try {
            // Selecciona los campos específicos de la tabla WbTareasProgramadas
            $query = WbTareasProgramadas::select(
                'id_tareas_programadas',
                'fk_id_programador_tareas',
                'modulo',
                'metodo',
                'parametros',
                'descripcion',
                'prox_ejecucion',
                'estado',
            );



            // Aplica filtros adicionales utilizando el método filtrar (no proporcionado aquí)
            $query = $this->filtrar($req, $query)->orderBy('id_tareas_programadas', 'DESC')->get();

            // Verifica si la consulta no devuelve registros
            if ($query->count() == 0) {
                return $this->handleAlert(__('messages.sin_registros_por_mostrar'), false);
            }

            // Prepara los datos de las columnas (no proporcionado aquí)
            $query = $this->preparandoDatosColumnas($query);

            // Convierte los resultados a un formato deseado (no proporcionado aquí)
            return $this->handleResponse($req, $this->wbTareasProgramadasToArray($query), __('messages.consultado'));
        } catch (\Throwable $e) {
            // Manejo de errores: devuelve un mensaje de error interno del servidor
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);

            // También podrías devolver información detallada del error para fines de depuración
            //return $this->handleAlert($e->getMessage(), false);
        }
    }

    /**
     * Prepara y transforma los datos de un array asociativo, remplazando identificadores con texto descriptivo.
     *
     * @param array $array El array de datos a transformar.
     * @return array El array transformado con datos preparados.
     */
    private function preparandoDatosColumnas($array)
    {
        try {
            // Crea una instancia del controlador de Programador de Tareas
            $programador = new WbProgramadorTareasController();

            // Itera sobre el array de datos y transforma cada elemento
            foreach ($array as &$elemento) {
                $copia = clone $elemento;
                // Convierte el identificador de módulo en texto descriptivo
                $elemento->moduloName = $this->getModuloEnTexto($copia->modulo);

                // Convierte el identificador de método en texto descriptivo
                $elemento->metodoName = $this->getMetodoEnTexto($copia->modulo, $copia->metodo); //__('messages.lab_solicitud_muestra');

                // Busca y reemplaza el identificador del programador de tareas con su descripción
                $programadorTareas = $programador->find($elemento->fk_id_programador_tareas);
                if ($programadorTareas) {
                    $elemento->fk_id_programador_tareas = $programadorTareas->descripcion;
                }

                $elemento->parametros = $this->getParametrosLabSolicitudMuestrasEnTexto($copia->parametros);
            }

            return $array;
        } catch (\Throwable $e) {
            // Registra el error en el log o maneja según tus necesidades
            //\Log::error($e->getMessage());
            return $array; // Devuelve el array original en caso de error
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function cambiarEstado(Request $req)
    {
        // Validar los parámetros de la solicitud
        $validator = Validator::make($req->all(), [
            'identificador' => 'required|numeric'
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Consultar los permisos del usuario
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        // Obtener permisos específicos para cambiar estados de tareas programadas
        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('TAREAS_PROGRAMADAS_CAMBIAR_ESTADOS', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        //Buscamos el registro por medio de su ID
        $modelo = WbTareasProgramadas::find($req->identificador);

        //En caso de no encontrarlo se devuelve el error
        if ($modelo == null) {
            return $this->handleAlert(__('messages.tarea_programada_no_encontrada'), false);
        }

        //En caso de encontrarlo se cambia su estado a inactivo
        if ($modelo->estado == 0) {
            $modelo->estado = 1;
        } else {
            $modelo->estado = 0;
        }
        $modelo->fk_id_usuario_edicion = $usuarioRol->fk_usuario;
        $modelo->fecha_edicion = DB::raw('SYSDATETIME()');

        //Guardamos los cambios, en caso de error devolvemos el error
        if (!$modelo->save()) {
            return $this->handleAlert(__('messages.error_al_intentar_hacer_cambios'), false);
        }

        // Devolver un mensaje de éxito
        return $this->handleAlert(__('messages.registro_cambio_de_estado'), true);
    }

    /**
     * Calcula la próxima ejecución de una tarea programada según la programación dada.
     *
     * @param int     $id_programador Identificador del programador de tareas.
     * @param bool    $esNuevo        Indica si es una nueva programación o no.
     *
     * @return \DateTime|string|null La fecha y hora de la próxima ejecución o null si no se encuentra una programación válida.
     */
    private function calcularProximaEjecucion($id_programador, $esNuevo = false)
    {
        // Crear una instancia del controlador WbProgramadorTareasController
        $programadorTareas = new WbProgramadorTareasController;

        // Obtener la programación de tareas según el ID del programador
        $programacion = $programadorTareas->find($id_programador);


        // Verificar si es una nueva programación
        if ($esNuevo) {
            // Calcular la próxima ejecución según el tipo de programación
            if ($programacion) {
                switch ($programacion->tipo) {
                    case 1:
                        // Tipos de programación diaria
                        return $this->calcularProximaEjecucionDiaria($programacion->dia_semana);
                    case 2:
                        // Tipos de programación semanal
                        return $this->calcularProximaEjecucionDiaria($programacion->dia_semana);
                    case 3:
                        // Tipo de programación mensual
                        return $this->calcularProximaEjecucionMensual($programacion->dia_mes, $programacion->semana_mes, $programacion->mes);
                }
            }
        } else {
            // Calcular la próxima ejecución basándose en la programación existente
            if ($programacion) {
                switch ($programacion->tipo) {
                    case 1:
                        // Tipo de programación diaria
                        return $this->calcularProximaEjecucionDiaria($programacion->dia_semana);
                    case 2:
                        // Tipo de programación semanal
                        return $this->calcularProximaEjecucionSemanal($programacion->dia_semana, $programacion->intervalo_semana);
                    case 3:
                        // Tipo de programación mensual
                        return $this->calcularProximaEjecucionMensual($programacion->dia_mes, $programacion->semana_mes, $programacion->mes);
                }
            }
        }
        // Si no se encuentra una programación válida, devolver null
        return null;
    }

    /**
     * Verifica si una cadena representa una fecha válida en el formato 'Y-m-d'.
     *
     * @param string $cadenaFecha La cadena que se intentará interpretar como una fecha.
     *
     * @return bool Retorna true si la cadena es una fecha válida, de lo contrario, retorna false.
     */
    function esFechaValida($cadenaFecha)
    {
        // Intenta analizar la cadena como una fecha utilizando el formato 'Y-m-d'
        $fecha = Carbon::createFromFormat('Y-m-d', $cadenaFecha);

        // Verifica si la fecha es una instancia de Carbon y si es válida
        return $fecha instanceof Carbon && $fecha->isValid();
    }



    /**
     * Obtiene el nombre del día de la semana en texto a partir de un número.
     *
     * @param int $dia Número del día de la semana (1 para lunes, 2 para martes, etc.).
     *
     * @return string|null Retorna el nombre del día de la semana en texto o null si el número no está en el rango válido.
     */
    private function getDiaSemanaEnTexto($dia)
    {
        // Verifica si el número del día está en el rango válido (1-7)
        if ($dia >= 1 && $dia <= 7) {
            // Obtiene la fecha actual
            $hoy = Carbon::now();

            // Copia la fecha actual, establece el inicio de la semana y agrega los días correspondientes
            return $hoy->copy()->startOfWeek()->addDays($dia - 1)->englishDayOfWeek;
        } else {
            // Retorna null si el número del día no está en el rango válido
            return null;
        }
    }

    /**
     * Calcula la próxima ejecución diaria basándose en el día de la semana y la fecha proporcionada.
     *
     * @param int    $dia_semana Número del día de la semana (1 para lunes, 2 para martes, etc.).
     * @param string $fecha      Fecha en formato 'Y-m-d'.
     *
     * @return string Retorna la fecha de la próxima ejecución diaria en formato 'Y-m-d'.
     */
    private function calcularDiaria($dia_semana, $fecha)
    {
        try {
            // Convierte la cadena de fecha a un objeto Carbon
            $fechaParse = Carbon::createFromFormat('Y-m-d', $fecha); // date('Y-m-d', $fecha);

            // Obtiene el nombre del día de la semana en texto
            $dia = $this->getDiaSemanaEnTexto($dia_semana);

            // Calcula la fecha de la próxima ejecución diaria
            $next = Carbon::createFromTimestamp(strtotime('next ' . $dia, $fechaParse->timestamp)); //$fechaParse->next($dia);

            // Verifica si la próxima ejecución cae en el próximo mes y ajusta la fecha en consecuencia
            if ($next->month !== $fechaParse->month) {
                //$next = $next->startOfMonth()->next($dia);
                $next = Carbon::createFromTimestamp(strtotime('first ' . $dia . ' of next month', $fechaParse->timestamp));
            }

            // Retorna la fecha de la próxima ejecución diaria en formato 'Y-m-d'
            return $next->format('Y-m-d');
            //return $fecha->startOfWeek()->next($dia)->format('Y-m-d');
        } catch (\Throwable $e) {
            // En caso de error, retorna un mensaje de error
            return "Error: " . $e->getMessage();
        }

    }

    /* private function getCarbonFechaFormat($fecha_ejecucion)
    {
        $fecha = null;
        if ($fecha_ejecucion === null) {
            $fecha = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
        } else {
            if ($this->esFechaValida($fecha_ejecucion)) {
                $fecha = Carbon::createFromFormat('Y-m-d', $fecha_ejecucion);
            }
        }
        return $fecha->format('Y-m-d');
    } */

    /**
     * Obtiene un objeto Carbon a partir de una cadena de fecha dada.
     *
     * @param string|null $fecha_ejecucion La cadena de fecha a convertir o nula si no se proporciona.
     *
     * @return string|null Retorna la fecha en formato 'Y-m-d' si es válida, de lo contrario, retorna null.
     */
    private function getCarbonFechaFormat($fecha_ejecucion)
    {
        $fecha = null;
        // Verifica si la cadena de fecha es nula
        if ($fecha_ejecucion === null) {
            // Si es nula, crea un objeto Carbon con la fecha actual
            $fecha = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
        } else {
            // Si la cadena de fecha no es nula, verifica si es válida utilizando la función esFechaValida
            if ($this->esFechaValida($fecha_ejecucion)) {
                // Si es válida, crea un objeto Carbon con la fecha proporcionada
                $fecha = Carbon::createFromFormat('Y-m-d', $fecha_ejecucion);
            }
        }

        // Retorna la fecha en formato 'Y-m-d' si es válida, de lo contrario, retorna null
        return $fecha ? $fecha->format('Y-m-d') : null;
    }

    /**
     * Calcula la próxima ejecución para una tarea programada diaria.
     *
     * @param int         $dia_semana       Número que representa el día de la semana (1-7).
     * @param string|null $fecha_ejecucion  La fecha de ejecución actual o nula si no se proporciona.
     *
     * @return string Retorna la próxima fecha de ejecución en formato 'Y-m-d'.
     */
    public function calcularProximaEjecucionDiaria($dia_semana, $fecha_ejecucion = null)
    {
        // Obtiene la fecha actual en formato 'Y-m-d' utilizando la función getCarbonFechaFormat
        $fecha = $this->getCarbonFechaFormat($fecha_ejecucion);

        // Obtiene una colección de días de la semana ordenados según el calendario
        $colecion = $this->getCollection($dia_semana);

        // Obtiene el número del día de la semana actual
        $diaDeLaSemana = $this->getDiaDeLaSemana($fecha);

        // Obtiene el próximo número del día de la semana en la colección
        $diaBuscar = $this->getNextDateNumber($colecion, $diaDeLaSemana);

        // Calcula la próxima fecha de ejecución diaria utilizando la función calcularDiaria
        return $this->calcularDiaria($diaBuscar, $fecha);
    }

    /**
     * Obtiene el próximo número de fecha en un arreglo dado.
     *
     * @param \Illuminate\Support\Collection $array      La colección de números de fecha.
     * @param int                           $parametro  El número de fecha actual.
     *
     * @return int Retorna el próximo número de fecha en el arreglo.
     */
    private function getNextDateNumber($array, $parametro)
    {
        // Busca la posición del próximo número de fecha en el arreglo usando la función de búsqueda
        $posicion = $array->search(function (int $item) use ($parametro) {
            return $item > $parametro;
        });

        // Si no encuentra una posición, establece la posición en el primer elemento del arreglo
        if (!$posicion) {
            $posicion = 0;
        }

        // Retorna el próximo número de fecha en el arreglo
        return $array->get($posicion);
    }

    /**
     * Obtiene una colección de números de fecha a partir de una cadena de entrada.
     *
     * @param string $string_array La cadena que contiene los números de fecha.
     *
     * @return \Illuminate\Support\Collection|null Retorna una colección de números de fecha o null si la entrada no es válida.
     */
    private function getCollection($string_array)
    {
        // Inicializa una nueva colección
        $colection = collect();

        // Verifica si la cadena contiene una coma (,), lo que indica múltiples números de fecha
        if (strpos($string_array, ',')) {
            // Divide la cadena en un arreglo usando la coma como delimitador
            $array = explode(',', $string_array);

            // Filtramos para asegurarnos de que solo contenga números válidos de día
            $array = array_filter($array, function ($dia) {
                return is_numeric($dia) && $dia >= 1 && $dia <= 31;
            });

            // Si el arreglo está vacío después del filtrado, retorna null
            if (empty($array)) {
                return null;
            }

            // Agrega cada número de fecha válido a la colección
            foreach ($array as $dia) {
                $colection->push($dia);
            }

        } else {
            // Si no hay comas en la cadena, asume que es un solo número de fecha y lo agrega a la colección
            $colection->push($string_array);
        }
        // Retorna la colección resultante
        return $colection;
    }

    /**
     * Obtiene el día de la semana (0-6) para una fecha dada.
     *
     * @param string $fecha_ejecucion La fecha para la cual se desea obtener el día de la semana.
     *
     * @return int El día de la semana representado como un número (0-6), donde 0 es domingo y 6 es sábado.
     */
    private function getDiaDeLaSemana($fecha_ejecucion)
    {
        // Crea una instancia de Carbon a partir de la fecha proporcionada
        $fecha = Carbon::createFromFormat('Y-m-d', $fecha_ejecucion);

        // Obtiene el día de la semana (0-6) de la instancia de Carbon
        return $fecha->dayOfWeek;
    }

    /**
     * Función de prueba que calcula la próxima ejecución semanal.
     *
     * @param string $dias           Los días de la semana en los que debe ocurrir la ejecución (pueden ser múltiples días separados por comas).
     * @param int    $intervalo      El intervalo de semanas entre ejecuciones.
     * @param string $fecha_ejecucion La fecha de ejecución inicial (opcional, por defecto es la fecha actual).
     *
     * @return string|null La próxima fecha de ejecución calculada o null si hay un error.
     */
    public function pruebaSemanal($dias, $intervalo, $fecha_ejecucion = null)
    {
        // Llama a la función principal para calcular la próxima ejecución semanal
        return $this->calcularProximaEjecucionSemanal($dias, $intervalo, $fecha_ejecucion);
    }

    /**
     * Calcula la próxima ejecución de una tarea programada de forma semanal.
     *
     * @param string $dia_semana      Los días de la semana en los que debe ocurrir la ejecución (pueden ser múltiples días separados por comas).
     * @param int    $intervalo        El intervalo de semanas entre ejecuciones.
     * @param string $fecha_ejecucion  La fecha de ejecución inicial (opcional, por defecto es la fecha actual).
     *
     * @return string|null La próxima fecha de ejecución calculada o null si hay un error.
     */
    private function calcularProximaEjecucionSemanal($dia_semana, $intervalo, $fecha_ejecucion = null)
    {
        // Obtiene la fecha en formato Carbon
        $fecha = $this->getCarbonFechaFormat($fecha_ejecucion);

        // Obtiene una colección de los días de la semana en los que debe ocurrir la ejecución
        $colecion = $this->getCollection($dia_semana);

        // Obtiene el número del día de la semana de la fecha de ejecución
        $diaDeLaSemana = $this->getDiaDeLaSemana($fecha);

        // Obtiene el próximo día de la semana en el que debe ocurrir la ejecución
        $diaBuscar = $this->getNextDateNumber($colecion, $diaDeLaSemana);

        // Calcula la próxima ejecución semanal
        return $this->calcularSemanal($colecion, $diaBuscar, $intervalo, $fecha);

    }

    /**
     * Calcula la próxima fecha de ejecución para una tarea programada de forma semanal.
     *
     * @param \Illuminate\Support\Collection $conllection   Colección de días de la semana en los que debe ocurrir la ejecución.
     * @param int                            $dia_semana    El día de la semana en el que debe ocurrir la ejecución.
     * @param int                            $intervalo     El intervalo de semanas entre ejecuciones.
     * @param string                         $fecha_ejecucion La fecha de ejecución inicial.
     *
     * @return string|null La próxima fecha de ejecución calculada o null si hay un error.
     */
    private function calcularSemanal($conllection, $dia_semana, $intervalo, $fecha_ejecucion)
    {
        try {
            // Obtiene el primer día de la colección
            $primerDia = $conllection->first();

            if ($primerDia == $dia_semana) {
                // Si el primer día es igual al día de la semana, se calcula la fecha en base a la fecha de ejecución
                $aux = Carbon::createFromFormat('Y-m-d', $fecha_ejecucion);
                $fecha_modificar = $aux->copy()->previous($this->getDiaSemanaEnTexto($dia_semana));

                if ($intervalo > 1) {
                    // Si el intervalo es mayor a 1, se agrega la cantidad de semanas especificada
                    $fecha = $fecha_modificar->addWeeks($intervalo)->format('Y-m-d');
                } else {
                    // Si el intervalo es 1 o menos, se calcula la próxima ejecución diariamente
                    $fecha = $this->calcularDiaria($dia_semana, $fecha_ejecucion);
                    /* $fecha = $fecha_modificar->addWeek()->format('Y-m-d'); */
                }
            } else {
                // Si el primer día no coincide con el día de la semana, se calcula la próxima ejecución diariamente
                $fecha = $this->calcularDiaria($dia_semana, $fecha_ejecucion);
            }
            return $fecha;
        } catch (\Throwable $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /* private function calcularSemanal($conllection, $dia_semana, $intervalo, $fecha_ejecucion)
    {
        try {
            $primerDia = $conllection->first();
            if ($primerDia == $dia_semana) {
                $aux = Carbon::createFromFormat('Y-m-d', $fecha_ejecucion);
                //$fecha_modificar = $aux->copy()->previous($this->getDiaSemanaEnTexto($dia_semana));
                \Log::error('Aux' . $aux);

                // Obtener el próximo día de la semana utilizando strtotime
                $fecha = Carbon::createFromTimestamp(strtotime('next ' . $this->getDiaSemanaEnTexto($dia_semana), $aux->timestamp));
                \Log::error('fecha' . $fecha);
                if ($intervalo > 1) {
                    $fecha = $fecha->addWeeks($intervalo - 1)->format('Y-m-d');
                } else {
                    $fecha = $this->calcularDiaria($dia_semana, $fecha_ejecucion);
                }
                //$fecha = $fecha->addWeeks($intervalo - 1)->format('Y-m-d');
                \Log::error('salida' . $fecha);
            } else {
                $fecha = $this->calcularDiaria($dia_semana, $fecha_ejecucion);
            }
            return $fecha;
        } catch (\Throwable $e) {
            return "Error: " . $e->getMessage();
        }
    } */

    public function calcuprueba(Request $req)
    {
        return $this->handleAlert($this->calcularProximaEjecucionMensual($req->dia, $req->sem, $req->mes));
    }

    /**
     * Calcula la próxima fecha de ejecución para una tarea programada de forma mensual.
     *
     * @param int         $dia_mes        Día del mes en el que debe ocurrir la ejecución.
     * @param int|string  $semana          Semana del mes en la que debe ocurrir la ejecución (opcional).
     * @param int         $mes            Mes en el que debe ocurrir la ejecución.
     * @param string|null $fecha_ejecucion La fecha de ejecución inicial.
     *
     * @return string|null La próxima fecha de ejecución calculada o null si hay un error.
     */
    private function calcularProximaEjecucionMensual($dia_mes, $semana, $mes, $fecha_ejecucion = null)
    {
        // Obtiene la fecha en formato Carbon
        $fecha = $this->getCarbonFechaFormat($fecha_ejecucion);

        // Comprueba si se especifica una semana
        if ($semana == null) {
            $respuesta = $this->calcularMensualDe30Dias($dia_mes, $mes, $fecha);
        } else {
            // Calcula la próxima ejecución mensual con días de la semana (Lun-Dom)
            $respuesta = $this->calcularMensualDeLunADom(trim($dia_mes), trim($semana), trim($mes));
        }
        return $respuesta;
    }

    /**
     * Calcula la próxima fecha de ejecución para una tarea programada de forma mensual con días específicos.
     *
     * @param int|string  $dias           Días del mes en los que debe ocurrir la ejecución.
     * @param int|string  $mes            Meses en los que debe ocurrir la ejecución.
     * @param string|null $fecha_ejecucion La fecha de ejecución inicial.
     *
     * @return string|null La próxima fecha de ejecución calculada o null si hay un error.
     */
    private function calcularMensualDe30Dias($dias, $mes, $fecha_ejecucion = null)
    {
        // Comprueba si la fecha de ejecución inicial es válida
        if ($fecha_ejecucion != null && !$this->esFechaValida($fecha_ejecucion)) {
            return null;
        }

        // Obtiene las colecciones de meses y días
        $colectMes = $this->getCollection($mes);
        $colectDias = $this->getCollection($dias);

        // Comprueba si hay meses y días especificados
        if ($colectMes->count() == 0 && $colectDias->count() == 0) {
            return null;
        }

        $fecha = null;
        // Establece la fecha inicial según la fecha de ejecución o la fecha actual
        if ($fecha_ejecucion === null) {
            $fecha = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
        } else {
            $fecha = Carbon::createFromFormat('Y-m-d', $fecha_ejecucion);
        }

        $nextDia = $colectDias->first();

        $exite = $colectMes->search($fecha->month);

        // Comprueba si el mes actual está en la lista de meses
        if ($exite !== false) {
            // Comprueba si el día actual es menor que el próximo día especificado
            if ($fecha->day < $nextDia) {
                return $fecha->day($nextDia)->toDateString(); //date('Y-m-' . $nextDia);
            }
        }

        $nextMes = $this->getNextDateNumber($colectMes, $fecha->month);

        if ($fecha_ejecucion === null) {
            // Comprueba si el mes actual es el próximo mes especificado
            $auxFecha = Carbon::createFromFormat('Y-m-d', date('Y-m-' . $nextDia));
            if ($fecha->month == $auxFecha->month) {
                // Comprueba si el día actual es menor que el próximo día especificado
                if ($fecha->day < $auxFecha->day) {
                    return $auxFecha->toDateString();
                }
            }
        }



        $mesTexto = $fecha->copy()->month($nextMes)->englishMonth;

        if ($nextMes == $colectMes->first()) {
            // Calcula la próxima fecha en el próximo año si el próximo mes es el primer mes en la lista
            return date('Y-m-d', strtotime($nextDia . ' ' . $mesTexto . ' next year'));
        }
        // Calcula la próxima fecha para el próximo mes especificado
        return date('Y-m-d', strtotime($nextDia . ' ' . $mesTexto));
    }

    /* private function calcularMensualDeLunADom($dias, $semana, $mes, $fecha_ejecucion = null)
    {
        try {
            if ($fecha_ejecucion != null && !$this->esFechaValida($fecha_ejecucion)) {
                return null;
            }

            if (!is_numeric($semana)) {
                return null;
            }

            $colectMes = $this->getCollection($mes);

            $colectDias = $this->getCollection($dias);

            if ($colectMes->count() == 0 && $colectDias->count() == 0) {
                return null;
            }

            $fecha = null;
            if ($fecha_ejecucion === null) {
                $fecha = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            } else {
                $fecha = Carbon::createFromFormat('Y-m-d', $fecha_ejecucion);
            }

            $exite = $colectMes->search($fecha->month);

            if ($exite !== false) {
                $mes1 = $exite;
            } else {
                $mes1 = $colectMes->search($this->getNextDateNumber($colectMes, $fecha->month));
            }


            $año = $fecha->year;

            for ($año; $año < $año + 2; $año++) {
                for ($j = $mes1; $j < $colectMes->count(); $j++) {
                    for ($i = 0; $i < $colectDias->count(); $i++) {
                        $prueba = Carbon::create($año, $colectMes->get($j), 1, 0, 0, 0)->nthOfMonth($semana, $colectDias->get($i));
                        if ($prueba->gt($fecha)) {
                            return $prueba;
                        }
                    }

                }
                $mes1 = 0;
            }
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    } */

    /**
     * Calcula la próxima fecha de ejecución para una tarea programada de forma mensual con semana específica y días específicos.
     *
     * @param int|string $dias Días del mes en los que debe ocurrir la ejecución.
     * @param int|string $semana Semana del mes en la que debe ocurrir la ejecución.
     * @param int|string $mes Meses en los que debe ocurrir la ejecución.
     *
     * @return string|null La próxima fecha de ejecución calculada o null si hay un error.
     */
    private function calcularMensualDeLunADom($dias, $semana, $mes)
    {
        /* if ($fecha_ejecucion !== null && !$this->esFechaValida($fecha_ejecucion)) {
            return null;
        } */

        // Comprueba si la semana es un número
        if (!is_numeric($semana)) {
            return null;
        }

        // Obtiene las colecciones de meses y días
        $colectMes = $this->getCollection($mes);
        $colectDias = $this->getCollection($dias);

        // Comprueba si hay meses y días especificados
        if ($colectMes->count() == 0 && $colectDias->count() == 0) {
            return null;
        }

        /* $fecha = null;
        if ($fecha_ejecucion === null) {
            $fecha = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
        } else {
            $fecha = Carbon::createFromFormat('Y-m-d', $fecha_ejecucion);
        } */

        // Obtiene la fecha actual
        $fecha = Carbon::now();

        // Busca el mes actual en la lista de meses
        $exite = $colectMes->search($fecha->month);

        // Si el mes actual no está en la lista, busca el siguiente mes
        if ($exite !== false) {
            $mes1 = $exite;
        } else {
            $mes1 = $colectMes->search($this->getNextDateNumber($colectMes, $fecha->month));
        }

        // Obtiene el año actual
        $año = $fecha->year;

        // Itera a través de los años actuales y siguientes
        for ($año; $año < $año + 2; $año++) {
            // Itera a través de los meses en la lista
            for ($j = $mes1; $j < $colectMes->count(); $j++) {
                // Itera a través de los días en la lista
                for ($i = 0; $i < $colectDias->count(); $i++) {
                    // Crea una instancia de Carbon para la fecha actual
                    $prueba = Carbon::create($año, $colectMes->get($j), 1, 0, 0, 0)->nthOfMonth($semana, $colectDias->get($i));

                    // Comprueba si la fecha calculada es posterior a la fecha actual
                    if ($prueba->gt($fecha)) {
                        return $prueba->format('Y-m-d');
                    }
                }

            }
            $mes1 = 0; // Reinicia el índice del mes para el siguiente año
        }
    }

    /**
     * Convierte el identificador de módulo en texto descriptivo.
     *
     * @param \stdClass $data El objeto de datos que contiene el identificador de módulo.
     * @return string|null El texto descriptivo del módulo o null si no se encuentra.
     */
    private function getModuloEnTexto($modulo)
    {
        try {
            // Obtiene la lista de opciones para módulos y tareas
            $opcionesArray = $this->getListaOpciones();

            // Itera sobre las opciones de módulos
            foreach ($opcionesArray as $opcionesKey) {
                // Compara el identificador de módulo en los datos con el identificador del módulo actual
                if ($modulo == $opcionesKey['modulo']['id']) {
                    // Retorna la descripción del módulo si hay coincidencia
                    return $opcionesKey['modulo']['descripcion'];
                }
            }

            // Retorna null si no se encuentra una coincidencia
            return null;
        } catch (\Throwable $e) {
            // Registra el error en el log o maneja según tus necesidades
            //\Log::error($e->getMessage());
            return null;
        }
    }

    /**
     * Convierte los identificadores de módulo y método en texto descriptivo.
     *
     * @param \stdClass $data El objeto de datos que contiene los identificadores de módulo y método.
     * @return string|null El texto descriptivo del método o null si no se encuentra.
     */
    private function getMetodoEnTexto($modulo, $metodo)
    {
        try {
            // Obtiene la lista de opciones para módulos y tareas
            $opcionesArray = $this->getListaOpciones();
            // Itera sobre las opciones de módulos
            foreach ($opcionesArray as $opcionesKey) {
                // Compara el identificador de módulo en los datos con el identificador del módulo actual
                if ($modulo == $opcionesKey['modulo']['id']) {
                    // Itera sobre las opciones de tareas dentro del módulo actual
                    foreach ($opcionesKey['modulo']['tareas'] as $tareaKey) {
                        // Compara el identificador de método en los datos con el identificador de la tarea actual
                        if ($metodo == $tareaKey['id']) {
                            // Retorna la descripción de la tarea si hay coincidencia
                            return $tareaKey['descripcion'];
                        }
                    }
                }
            }
            // Retorna null si no se encuentra una coincidencia
            return null;
        } catch (\Throwable $e) {
            // Registra el error en el log o maneja según tus necesidades
            //\Log::error($e->getMessage());
            return null;
        }
    }

    /**
     * Convierte los parámetros relacionados con las solicitudes de muestras de laboratorio a un formato de texto legible.
     *
     * @param string|null $parametros Parámetros de la solicitud de muestra en formato JSON.
     *
     * @return string|null Texto legible que describe los parámetros de la solicitud de muestra o null si hay un error.
     */
    private function getParametrosLabSolicitudMuestrasEnTexto($parametros)
    {
        try {
            // Comprueba si los parámetros son nulos
            if ($parametros == null) {
                return null;
            }

            // Decodifica los parámetros JSON
            $elemento = json_decode($parametros, true);

            // Comprueba si la decodificación fue exitosa
            if ($elemento == null) {
                return null;
            }

            // Obtiene información sobre el material y el tipo de control
            $material = (new WbMaterialListaController())->find($elemento['material']);
            $tipoControl = (new WbTipoControlController())->find($elemento['tipo_control']);

            // Inicializa la cadena de respuesta con información sobre el material y el tipo de control
            $respuesta = 'Material: ' . $material->Nombre . ';Tipo de control: ' . $tipoControl->descripcion . ';Ensayos: ';

            $ensayos = $elemento['ensayos']; //json_decode($elemento['ensayos'], true);

            // Comprueba si hay ensayos disponibles
            if (sizeof($ensayos) == 0) {
                return null;
            }

            // Itera a través de los ensayos
            for ($i = 0; $i < sizeof($ensayos); $i++) {
                // Comprueba si es el último ensayo en la lista
                if ($i != (sizeof($ensayos) - 1)) {
                    // Obtiene información sobre el ensayo
                    $item = (new WbEnsayosController())->find($ensayos[$i]);

                    // Comprueba si la información del ensayo es válida
                    if ($item == null) {
                        continue;
                    }

                    // Agrega información del ensayo a la cadena de respuesta
                    $respuesta .= $item->nombre . '(' . $item->descripcion . '), ';
                } else {
                    // Si es el último ensayo, agrega información sin la coma final
                    $item = (new WbEnsayosController())->find($ensayos[$i]);

                    // Comprueba si la información del ensayo es válida
                    if ($item == null) {
                        continue;
                    }

                    // Agrega información del ensayo a la cadena de respuesta
                    $respuesta .= $item->nombre . '(' . $item->descripcion . ')';
                }

            }

            // Retorna null si no se encuentra una coincidencia
            return $respuesta;
        } catch (\Throwable $e) {
            // Registra el error en el log o maneja según tus necesidades
            //\Log::error($e->getMessage());
            return $e->getMessage();
            //return null;
        }
    }


    /**
     * Ejecuta las tareas programadas para el día actual.
     */
    public function ejecutarTareasProgramadas()
    {
        try {
            // Obtiene la fecha actual en formato 'Y-m-d'
            $hoy = date('Y-m-d');

            // Obtiene las tareas programadas para el día actual y que están activas
            $tareasProgramadas = WbTareasProgramadas::where('prox_ejecucion', $hoy)
                ->where('estado', 1)
                ->get();

            // Si no hay tareas programadas, sale de la función
            if ($tareasProgramadas->isEmpty()) {
                return;
            }

            // Itera sobre las tareas programadas
            foreach ($tareasProgramadas as $tareaProgramada) {
                // Si la tarea está desactivada, continúa con la siguiente
                if ($tareaProgramada->estado == 0) {
                    continue;
                }

                //consultamos si la programacion a la cual pertenece esta tarea se encuentra activa
                $programacion = WbProgramadorTarea::find($tareaProgramada->fk_id_programador_tareas);

                //en el caso de no estar activa entonces omitimos la tarea
                if ($programacion != null && $programacion->estado == 0) {
                    continue;
                }

                // Busca y obtiene el usuario relacionado con la tarea programada
                $usuario = (new UsuarioController())->find($tareaProgramada->fk_id_usuarios_creacion);

                // Si no se encuentra el usuario, continúa con la siguiente tarea
                if ($usuario == null) {
                    continue;
                }

                // Verifica el módulo y método de la tarea programada para determinar la acción a realizar
                if ($tareaProgramada->modulo == 1 && $tareaProgramada->metodo == 1) {
                    // Ejecuta la tarea para el módulo 1 y método 1 (Laboratorio solicitud de muestras en automático)
                    (new WbSolicitudMuestraController())->crearSolicitudMuestrasAutomatico(
                        json_decode($tareaProgramada->parametros, true),
                        $usuario->fk_id_project_Company,
                        $usuario->fk_compañia
                    );
                }

                // Calcula la próxima ejecución de la tarea programada
                $tareaProgramada->prox_ejecucion = $this->calcularProximaEjecucion($tareaProgramada->fk_id_programador_tareas);
                $tareaProgramada->save();
            }
        } catch (\Throwable $e) {
            // Captura cualquier excepción y la registra en el log
            //\Log::error($e->getMessage());
        }
    }
}