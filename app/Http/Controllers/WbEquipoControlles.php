<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Compania;
use App\Models\SyncRelacionVehiculoPesos;
use App\Models\Equipos\WbEquipo;
use App\Models\Usuarios\usuarios_M;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Aqui se encuentra la clase WbEquipoControlles que contiene toda la
 * logica del controlador de equipos
 */
class WbEquipoControlles extends BaseController implements Vervos
{

    /*
     * Función que crea un equipo tanto en TimeScan como en Webu
     * Esta función contiene la validación del formulario
     * Ademas de la funciones se valida si esta en proyecto 1 para actualizar la
     * tabla de TimeScan.
     */
    public function post(Request $req) {}

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
     * Funcion de get no tocar por la interface de vervos
     */
    public function get(Request $request) {}

    /**
     * Aqui procedemos a cambiar el estado del equipo de activo a inactivo
     * teniendo en cuenta sus estado inciaial primero verificamos a que proyecto pertenece
     * ese equipo
     * luego verificamos el estado inicial si es A lo cambiamos a I y si es I lo cambiamos a A
     * con sus respectivos mensajes y luego guardamos el usuario que lo edito y guardamos el estado
     * y en el caso de que sea 1 el proyecto guardamos en timescan
     */
    public function CambiarEstado(Request $request, $equipo) {}

    /**
     * Aqui se encuentra la funcion de equipo viajes para bascula la cual
     *se recibe un valor de filtro en la variable equipos,
     * en caso de verdadero se filtra la consulta por compañia, placa o id_equipo,luego se valida si se recibe
     * valores de filtro de id o estado para filtrar la consulta, luego se ordena de forma,
     * Finalmente se incrusta la informacion de la compañia y el peso de cada equipo en el resultado
     *
     */
    public function equiposParaViajeBascula(Request $request, $equipo = 0)
    {
        $baseDatos = Db::connection('sqlsrv2')->getDatabaseName() . '.dbo.';
        $consulta = WbEquipo::select('Wb_equipos.*')
            ->leftJoin('compañia as sc', 'sc.id_compañia', 'Wb_equipos.fk_compania')
            ->selectRaw("(SELECT top 1 PESO FROM {$baseDatos}sync_relacion_VehiculosPesos WHERE vehiculo = Wb_equipos.equiment_id collate SQL_Latin1_General_CP1_CI_AS) as peso");
        // ->selectRaw("(SELECT COUNT(equipo) FROM sync_registros WHERE equipo=Wb_equipos.equiment_id collate SQL_Latin1_General_CP1_CI_AS) as CANTIDAD");
        if ($equipo != 0 && strlen($equipo) > 0) {
            $equipo = strtolower($equipo);
            $consulta = $consulta->where(function ($query) use ($equipo) {
                $query->where(DB::raw('LOWER(sc.nombreCompañia)'), 'like', DB::raw("'%$equipo%'"))
                    ->orWhere(DB::raw('LOWER(Wb_equipos.placa)'), 'like', DB::raw("'%$equipo%'"))
                    ->orWhere(DB::raw('LOWER(Wb_equipos.equiment_id)'), 'like', DB::raw("'%$equipo%'"));
            });
        }
        if ($request->has('id') && strlen($request->id) > 0) {
            $consulta = $consulta->where('equiment_id', $request->id);
        }
        if ($request->has('estado') && strlen($request->estado) > 0) {
            $consulta = $consulta->where('estado', 'A');
        }
        $consulta = $consulta->orderBy('Wb_equipos.equiment_id', 'asc');
        $limitePaginas = 1;
        if ($request->query('page') && is_numeric($request->page) && $request->query('limit') && is_numeric($request->limit)) {
            $consulta = $this->filtrar3($request, $consulta, 'Wb_equipos');
            $contador = clone $consulta;
            $contador = $contador->select('Wb_equipos.equiment_id')->get();
            $consulta = $consulta->forPage($request->page, $request->limit)->get();
            $limitePaginas = ($contador->count() / $request->limit) + 1;
        } else {
            $consulta = $this->filtrar3($request, $consulta, 'Wb_equipos')->get();
        }
        $companias = Compania::select('id_compañia', 'nombreCompañia')->get();
        $pesos = SyncRelacionVehiculoPesos::select('vehiculo', 'peso')->get();
        foreach ($consulta as $item) {
            $this->setCompaniaById($item, $companias);
            $this->setPesoById($item, $pesos);
        }

        return $this->handleResponse($request, $this->equiposToArray($consulta), __('messages.consultado'), $limitePaginas);
    }


    /**
     * En esta funcion validamos si el equipo existe y si esta activo
     * en caso de que no lo sea mostramos que el equipo no fue encontrado
     */
    public function validarEquimentId(Request $request, $id)
    {
        $consulta = WbEquipo::where('equiment_id', $id)->where('estado', 'A')->first();
        if ($consulta != null) {
            return $this->handleResponse($request, ['placa' => $consulta->placa, 'descripcion' => $consulta->descripcion], __('messages.consultado'), $consulta != null);
        } else {
            return $this->handleAlert('Equipo no encontrado');
        }
    }


    /**
     * Aqui se encuentra la funcion de los equipos activos
     * se filtran los equipos diferentes de inactivos
     */
    public function equiposActivos(Request $request)
    {
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $consulta = WbEquipo::where('estado', '!=', 'I')
                ->with([
                    'tipo_equipo' => function ($query) {
                        $query->select('id_tipo_equipo', 'nombre', 'horometro', 'kilometraje', 'is_volco');
                    },
                    'vehiculos_pesos' => function ($query) {
                        $query->select('vehiculo', 'peso');
                    },
                    'compania' => function ($query) {
                        $query->select('id_compañia', 'nombreCompañia');
                    },
                    'horometros' => function ($query) {
                        $query->select('id_equipos_horometros_ubicaciones', 'fk_id_equipo', 'horometro', 'fecha_registro');
                    },
                    'parte_diario',
                    'parte_diario_kilometraje',
                    // 'parte_diario' => function ($query) {
                    //     $query->select('id_parte_diario','fecha_registro','fecha_creacion_registro','horometro_final','fk_equiment_id');
                    // },
                    'ubicacion' => function ($query) use ($proyecto) {
                        $query->select('id_equipos_horometros_ubicaciones', 'fk_id_equipo', 'fk_id_tramo', 'fk_id_hito', 'fecha_registro')
                            ->with([
                                'tramo' => function ($query) use ($proyecto) {
                                    $query->select('id', 'Id_Tramo', 'Descripcion', 'fk_id_project_Company')->where('fk_id_project_Company', $proyecto);
                                },
                                'hito' => function ($query) use ($proyecto) {
                                    $query->select('id', 'Id_Hitos', 'Descripcion', 'fk_id_project_Company')->where('fk_id_project_Company', $proyecto);
                                }
                            ]);
                    }
                ])
                ->select(
                    'id',
                    'equiment_id',
                    'descripcion',
                    'cubicaje',
                    'marca',
                    'modelo',
                    'placa',
                    'observacion',
                    'dueno',
                    'estado',
                    'tipocontrato',
                    'codigo_externo',
                    'horometro_inicial',
                    'fk_compania',
                    'fk_id_tipo_equipo',
                    'fk_id_project_Company',
                    'peso'
                );

            $consulta = $this->filtrarPorProyecto($request, $consulta)->orderBy('equiment_id', 'DESC')->get();
            //return $this->handleResponse($request, $consulta->orderBy('equiment_id', 'DESC')->get(), 'consultado');
            return $this->handleResponse($request, $this->equiposToArray($consulta), 'consultado');
        } catch (\Throwable $th) {
            \Log::info($th->getMessage());
            return $this->handleAlert($th->getMessage(), false);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function findForId($idEquipo, $proyecto)
    {
        try {
            $consulta = WbEquipo::where('id', $idEquipo)
                ->with([
                    'tipo_equipo' => function ($query) {
                        $query->select('id_tipo_equipo', 'nombre');
                    },
                    'vehiculos_pesos' => function ($query) {
                        $query->select('vehiculo', 'peso');
                    },
                    'compania' => function ($query) {
                        $query->select('id_compañia', 'nombreCompañia');
                    },
                    'horometros' => function ($query) {
                        $query->select('id_equipos_horometros_ubicaciones', 'fk_id_equipo', 'horometro', 'fecha_registro');
                    },
                    'ubicacion' => function ($query) use ($proyecto) {
                        $query->select('id_equipos_horometros_ubicaciones', 'fk_id_equipo', 'fk_id_tramo', 'fk_id_hito', 'fecha_registro')
                            ->with([
                                'tramo' => function ($query) use ($proyecto) {
                                    $query->select('id', 'Id_Tramo', 'Descripcion', 'fk_id_project_Company')->where('fk_id_project_Company', $proyecto);
                                },
                                'hito' => function ($query) use ($proyecto) {
                                    $query->select('id', 'Id_Hitos', 'Descripcion', 'fk_id_project_Company')->where('fk_id_project_Company', $proyecto);
                                }
                            ]);
                    }
                ])
                ->select(
                    'id',
                    'equiment_id',
                    'descripcion',
                    'cubicaje',
                    'marca',
                    'modelo',
                    'placa',
                    'observacion',
                    'dueno',
                    'estado',
                    'tipocontrato',
                    'codigo_externo',
                    'horometro_inicial',
                    'fk_compania',
                    'fk_id_tipo_equipo',
                    'fk_id_project_Company'
                )->first();

            if ($consulta == null) {
                return null;
            }

            return $this->equipoToModel($consulta);
        } catch (\Throwable $th) {
            //return $this->handleAlert('findForId equipo: ' . $th->getMessage());
            \Log::error('findForId equipo: ' . $th->getMessage());
            return null;
        }
    }

    public function getListForIds(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $listIds = json_decode($req->datos, true);

        if (!is_array($listIds) || sizeof($listIds) == 0) {
            return $this->handleAlert('empty');
        }

        $proyecto = $this->traitGetProyectoCabecera($req);

        $query = WbEquipo::whereIn('id', $listIds)
            ->where('estado', '!=', 'I')
            ->where('fk_id_project_Company', $proyecto)
            ->with([
                'tipo_equipo' => function ($query) {
                    $query->select('id_tipo_equipo', 'nombre');
                },
                'vehiculos_pesos' => function ($query) {
                    $query->select('vehiculo', 'peso');
                },
                'compania' => function ($query) {
                    $query->select('id_compañia', 'nombreCompañia');
                },
                'horometros' => function ($query) {
                    $query->select('id_equipos_horometros_ubicaciones', 'fk_id_equipo', 'horometro', 'fecha_registro');
                },
                'ubicacion' => function ($query) use ($proyecto) {
                    $query->select('id_equipos_horometros_ubicaciones', 'fk_id_equipo', 'fk_id_tramo', 'fk_id_hito', 'fecha_registro')
                        ->with([
                            'tramo' => function ($query) use ($proyecto) {
                                $query->select('id', 'Id_Tramo', 'Descripcion', 'fk_id_project_Company')->where('fk_id_project_Company', $proyecto);
                            },
                            'hito' => function ($query) use ($proyecto) {
                                $query->select('id', 'Id_Hitos', 'Descripcion', 'fk_id_project_Company')->where('fk_id_project_Company', $proyecto);
                            }
                        ]);
                }
            ])
            ->select(
                'id',
                'equiment_id',
                'descripcion',
                'cubicaje',
                'marca',
                'modelo',
                'placa',
                'observacion',
                'dueno',
                'estado',
                'tipocontrato',
                'codigo_externo',
                'horometro_inicial',
                'fk_compania',
                'fk_id_tipo_equipo',
                'fk_id_project_Company'
            )->get();

        return $this->handleResponse($req, $this->equiposToArray($query), __('messages.consultado'));
    }


    private function postActPesoAction($info)
    {
        $validacion = Validator::make($info, [
            'identificador' => 'required|numeric',
            'equipo' => 'required|string',
            'peso' => 'required|string',
            'usuario_id' => 'required|string',
            'proyecto' => 'required|numeric',
            'hash' => 'required|string',
        ]);

        if ($validacion->fails()) {
            return false;
        }

        $find = WbEquipo::where('id', $info['equipo'])
            ->where('fk_id_project_Company', $info['proyecto'])
            ->first();

        if ($find == null) {
            return false;
        }

        $find->peso = isset($info['peso']) ? $info['peso'] : null;
        $find->peso_user = isset($info['usuario_id']) ? $info['usuario_id'] : null;

        if (!$find->save()) {
            return false;
        }

        $sync = SyncRelacionVehiculoPesos::where('vehiculo', $find->equiment_id)->first();

        if ($sync == null) {
            $sync = new SyncRelacionVehiculoPesos();
            $sync->vehiculo = $find->equiment_id;
            $sync->peso = isset($info['peso']) ? $info['peso'] : null;
            if ($info['usuario_id']) {
                $usuario = usuarios_M::where('id_usuarios', $info['usuario_id'])->first();
                if ($usuario) {
                    $sync->userr = $usuario->usuario;
                }
            }
            $fecha = Carbon::now();
            $formateada = $fecha->format('d/m/Y g:i');
            $sync->fecha = $formateada;
        } else {
            $sync->peso = isset($info['peso']) ? $info['peso'] : null;
            if ($info['usuario_id']) {
                $usuario = usuarios_M::where('id_usuarios', $info['usuario_id'])->first();
                if ($usuario) {
                    $sync->userr = $usuario->usuario;
                }
            }
            $fecha = Carbon::now();
            $formateada = $fecha->format('d/m/Y g:i');
            $sync->fecha = $formateada;
        }

        if (!$sync->save()) {
            return false;
        }

        return true;
    }


    public function postPesoArray(Request $req)
    {
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
                foreach ($listaGuardar as $key => $info) {
                    $action = $this->postActPesoAction($info);
                    if ($action) {
                        $guardados++;
                        $itemRespuesta = collect();
                        $itemRespuesta->put('hash', $info['hash']);
                        $respuesta->push($itemRespuesta);
                    }
                }

                if ($guardados == 0) {
                    return $this->handleAlert("empty");
                }

                return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
            } else {
                return $this->handleAlert("empty");
            }
        } catch (\Throwable $th) {
            \Log::error('peso-equipo-array-insert ' . $th->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }
}
