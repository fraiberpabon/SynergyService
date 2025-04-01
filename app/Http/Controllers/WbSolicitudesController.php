<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\solicitudConcreto;
use App\Models\Transporte\WbTransporteRegistro;
use App\Models\WbFormulaCentroProduccion;
use App\Models\WbSolicitudMateriales;
use App\Models\WbSolitudAsfalto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WbSolicitudesController extends BaseController implements Vervos
{
    private $estados_soli_material = [
        'pendiente' => 12, //pendientes
        'despachado' => 15, //despachados
        'cerrado' => 30  //cerrado o finalizado
    ];

    private $estados_soli_asfalto = [
        'pendiente' => 'PENDIENTE',
        'despachado' => 'ENVIADO'
    ];

    private $estados_soli_concreto = [
        'pendiente' => 'PENDIENTE',
        'despachado' => 'ENVIADO',
        'rechazado' => 'RECHAZADO',
        'anulado' => 'ANULADO',
    ];

    public function update(Request $req, $id)
    {

    }

    public function reAsignar(Request $request, $id)
    {

    }

    public function aprovar(Request $request, $id)
    {

    }

    public function rechazar(Request $request, $id)
    {

    }

    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getApp(Request $req)
    {
        $query = WbSolicitudMateriales::with([
            'usuario' => function ($sub) {
                $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
            },
            'usuarioAprobador' => function ($sub) {
                $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
            },
            'materialLista' => function ($sub) {
                $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
            },
            'tipoCapa' => function ($sub) {
                $sub->select('id_tipo_capa', 'Descripcion');
            },
            'tipoCalzada' => function ($sub) {
                $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
            },
            'tipoCarril' => function ($sub) {
                $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
            },
            'formulaLista' => function ($sub) {
                $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
            },
            'plantas' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'plantaReasig' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'plantas_destino' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'transporte' => function ($sub) {
                $sub->with('equipo')->where('estado', 1);
            }
        ])
            ->whereDate('fechaProgramacion', '>=', Carbon::now()->subDays(3)->toDateString())
            ->where(function ($q) {
                $q->where('fk_id_estados', 12)
                    ->orWhereNotNull('user_despacho');
            })
            ->select(
                'id_solicitud_Materiales as identificador',
                'id_solicitud_Materiales',
                DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                'fk_id_usuarios',
                'fk_id_usuarios_update',
                'fk_id_tipo_capa',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_tipo_carril',
                'fk_id_tipo_calzada',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'fk_id_plantaReasig',
                'Cantidad',
                'cantidad_real',
                'numeroCapa',
                'notaUsuario',
                'notaSU',
                'fk_id_planta_destino',
                DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                'fk_id_project_Company',
            );

        $query = $this->filtrar($req, $query)->orderBy('fechaProgramacion', 'DESC')->get();

        $query = $query->map(function ($item) {
            $info = WbFormulaCentroProduccion::select('codigoFormulaCdp')
                ->where('fk_id_formula_lista', $item->fk_id_formula)
                ->where('fk_id_planta', $item->fk_id_planta)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $item->fk_id_project_Company)
                ->orderBy('dateCreate', 'DESC')
                ->first();

            $item->fk_formula_cdp = $info->codigoFormulaCdp ?? null;

            if ($item->transporte) {
                //$cubicaje = 0;
                //$viajes = 0;

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($item->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    }
                }

                $item->cant_despachada = max($cLlegada, $cSalida);
                $item->cant_viajes = max($vLlegada, $vSalida);
            } else {
                $item->cant_despachada = 0;
                $item->cant_viajes = 0;
            }

            return $this->solicitudesAppToModel($item);
        });

        return $this->handleResponse($req, $query, __('messages.consultado'));
        //return $this->handleResponse($req, $this->solicitudesAppToArray($query->get()), __('messages.consultado'));
    }

    public function findForId($idSolicitud, $tipoTransporte = null)
    {
        try {
            $query = WbSolicitudMateriales::with([
                'usuario' => function ($sub) {
                    $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                },
                'usuarioAprobador' => function ($sub) {
                    $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                },
                'materialLista' => function ($sub) {
                    $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
                },
                'tipoCapa' => function ($sub) {
                    $sub->select('id_tipo_capa', 'Descripcion');
                },
                'tipoCalzada' => function ($sub) {
                    $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
                },
                'tipoCarril' => function ($sub) {
                    $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
                },
                'formulaLista' => function ($sub) {
                    $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
                },
                'plantas' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'plantaReasig' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'plantas_destino' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'transporte' => function ($sub) {
                    $sub->with('equipo')->where('estado', 1);
                }
            ])
                ->where('id_solicitud_Materiales', $idSolicitud)
                ->select(
                    'id_solicitud_Materiales as identificador',
                    'id_solicitud_Materiales',
                    DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                    'fk_id_usuarios',
                    'fk_id_usuarios_update',
                    'fk_id_tipo_capa',
                    'fk_id_tramo',
                    'fk_id_hito',
                    'abscisaInicialReferencia',
                    'abscisaFinalReferencia',
                    'fk_id_tipo_carril',
                    'fk_id_tipo_calzada',
                    'fk_id_material',
                    'fk_id_formula',
                    'fk_id_planta',
                    'fk_id_plantaReasig',
                    'Cantidad',
                    'cantidad_real',
                    'numeroCapa',
                    'notaUsuario',
                    'notaSU',
                    'fk_id_planta_destino',
                    DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                    DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                    'fk_id_project_Company',
                )->first();

            if ($query == null) {
                return null;
            }


            $info = WbFormulaCentroProduccion::select('codigoFormulaCdp')
                ->where('fk_id_formula_lista', $query->fk_id_formula)
                ->where('fk_id_planta', $query->fk_id_planta)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $query->fk_id_project_Company)
                ->orderBy('dateCreate', 'DESC')
                ->first();

            $query->fk_formula_cdp = $info->codigoFormulaCdp ?? null;

            if ($query->transporte) {

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($query->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    }
                }

                if ($tipoTransporte == null) {
                    $query->cant_despachada = max($cLlegada, $cSalida);
                    $query->cant_viajes = max($vLlegada, $vSalida);
                } else {
                    if ($tipoTransporte == '1') {
                        $query->cant_despachada = $cLlegada;
                        $query->cant_viajes = $vLlegada;
                    } else {
                        $query->cant_despachada = $cSalida;
                        $query->cant_viajes = $vSalida;
                    }
                }

            } else {
                $query->cant_despachada = 0;
                $query->cant_viajes = 0;
            }

            return $this->solicitudesAppToModel($query);
            //return $this->handleResponse($req, $this->solicitudesAppToArray($query->get()), __('messages.consultado'));
        } catch (\Throwable $th) {
            //throw $th;
            \Log::error('findForId: ' . $th->getMessage());
            return null;
        }
    }

    public function getAppV2(Request $req)
    {
        $query = WbSolicitudMateriales::with([
            'usuario' => function ($sub) {
                $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
            },
            'usuarioAprobador' => function ($sub) {
                $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
            },
            'materialLista' => function ($sub) {
                $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
            },
            'tipoCapa' => function ($sub) {
                $sub->select('id_tipo_capa', 'Descripcion');
            },
            'tipoCalzada' => function ($sub) {
                $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
            },
            'tipoCarril' => function ($sub) {
                $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
            },
            'formulaLista' => function ($sub) {
                $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
            },
            'plantas' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'plantaReasig' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'plantas_destino' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'transporte' => function ($sub) {
                $sub->with('equipo')->where('estado', 1);
            }
        ])
            ->whereDate('fechaProgramacion', '>=', Carbon::now()->subDays(3)->toDateString())
            ->where(function ($q) {
                $q->where('fk_id_estados', 12)
                    ->orWhereNotNull('user_despacho');
            })
            ->select(
                'id_solicitud_Materiales as identificador',
                'id_solicitud_Materiales',
                DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                'fk_id_usuarios',
                'fk_id_usuarios_update',
                'fk_id_tipo_capa',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_tipo_carril',
                'fk_id_tipo_calzada',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'fk_id_plantaReasig',
                'Cantidad',
                'cantidad_real',
                'numeroCapa',
                'notaUsuario',
                'notaSU',
                'fk_id_planta_destino',
                DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                'fk_id_project_Company',
                'fk_id_tramo_origen',
                'fk_id_hito_origen',
            );

        $query = $this->filtrar($req, $query)->orderBy('fechaProgramacion', 'DESC')->get();

        $query = $query->map(function ($item) {
            $info = WbFormulaCentroProduccion::select('codigoFormulaCdp')
                ->where('fk_id_formula_lista', $item->fk_id_formula)
                ->where('fk_id_planta', $item->fk_id_planta)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $item->fk_id_project_Company)
                ->orderBy('dateCreate', 'DESC')
                ->first();

            $item->fk_formula_cdp = $info->codigoFormulaCdp ?? null;

            if ($item->transporte) {
                //$cubicaje = 0;
                //$viajes = 0;

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($item->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    }
                }

                $item->total_despachada = max($cLlegada, $cSalida);
                $item->cant_recibida = $cLlegada;
                $item->cant_viajes_llegada = $vLlegada;
                $item->cant_despachada = $cSalida;
                $item->cant_viajes_salida = $vSalida;
            } else {
                $item->total_despachada = 0;
                $item->cant_recibida = 0;
                $item->cant_viajes_llegada = 0;
                $item->cant_despachada = 0;
                $item->cant_viajes_salida = 0;
            }

            return $this->solicitudesAppV2ToModel($item);
        });

        return $this->handleResponse($req, $query, __('messages.consultado'));
        //return $this->handleResponse($req, $this->solicitudesAppToArray($query->get()), __('messages.consultado'));
    }

    public function findForIdV2($idSolicitud)
    {
        try {
            $query = WbSolicitudMateriales::with([
                'usuario' => function ($sub) {
                    $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                },
                'usuarioAprobador' => function ($sub) {
                    $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                },
                'materialLista' => function ($sub) {
                    $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
                },
                'tipoCapa' => function ($sub) {
                    $sub->select('id_tipo_capa', 'Descripcion');
                },
                'tipoCalzada' => function ($sub) {
                    $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
                },
                'tipoCarril' => function ($sub) {
                    $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
                },
                'formulaLista' => function ($sub) {
                    $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
                },
                'plantas' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'plantaReasig' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'plantas_destino' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'transporte' => function ($sub) {
                    $sub->with('equipo')->where('estado', 1);
                }
            ])
                ->where('id_solicitud_Materiales', $idSolicitud)
                ->select(
                    'id_solicitud_Materiales as identificador',
                    'id_solicitud_Materiales',
                    DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                    'fk_id_usuarios',
                    'fk_id_usuarios_update',
                    'fk_id_tipo_capa',
                    'fk_id_tramo',
                    'fk_id_hito',
                    'abscisaInicialReferencia',
                    'abscisaFinalReferencia',
                    'fk_id_tipo_carril',
                    'fk_id_tipo_calzada',
                    'fk_id_material',
                    'fk_id_formula',
                    'fk_id_planta',
                    'fk_id_plantaReasig',
                    'Cantidad',
                    'cantidad_real',
                    'numeroCapa',
                    'notaUsuario',
                    'notaSU',
                    'fk_id_planta_destino',
                    DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                    DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                    'fk_id_project_Company',
                    'fk_id_tramo_origen',
                    'fk_id_hito_origen',
                )->first();

            if ($query == null) {
                return null;
            }


            $info = WbFormulaCentroProduccion::select('codigoFormulaCdp')
                ->where('fk_id_formula_lista', $query->fk_id_formula)
                ->where('fk_id_planta', $query->fk_id_planta)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $query->fk_id_project_Company)
                ->orderBy('dateCreate', 'DESC')
                ->first();

            $query->fk_formula_cdp = $info->codigoFormulaCdp ?? null;

            if ($query->transporte) {

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($query->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    }
                }

                $query->total_despachada = max($cLlegada, $cSalida);
                $query->cant_recibida = $cLlegada;
                $query->cant_viajes_llegada = $vLlegada;
                $query->cant_despachada = $cSalida;
                $query->cant_viajes_salida = $vSalida;

            } else {
                $query->total_despachada = 0;
                $query->cant_recibida = 0;
                $query->cant_viajes_llegada = 0;
                $query->cant_despachada = 0;
                $query->cant_viajes_salida = 0;
            }

            return $this->solicitudesAppV2ToModel($query);
            //return $this->handleResponse($req, $this->solicitudesAppToArray($query->get()), __('messages.consultado'));
        } catch (\Throwable $th) {
            //throw $th;
            \Log::error('findForIdV2: ' . $th->getMessage());
            return null;
        }
    }

    public function findForIdV3($idSolicitud, $tipo)
    {
        switch ($tipo) {
            case 'M':
                $repuesta = $this->getSolicitudMaterial($idSolicitud);
                break;
            case 'A':
                $repuesta = $this->getSolicitudAsfalto($idSolicitud);
                break;
            case 'C':
                $repuesta = $this->getSolicitudConcreto($idSolicitud);
                break;
            default:
                break;
        }

        return $repuesta;
    }

    private function getSolicitudMaterial($idSolicitud)
    {
        try {
            $query = WbSolicitudMateriales::with([
                'transporte' => function ($sub) {
                    $sub->where('estado', 1)->where('tipo_solicitud', 'M')->where('user_created', '!=', 0);
                }
            ])
                ->where('id_solicitud_Materiales', $idSolicitud)
                ->first();

            if ($query == null) {
                return null;
            }

            if ($query->transporte) {

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($query->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->cubicaje ? $tr->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->cubicaje ? $tr->cubicaje : 0;
                    }
                }

                $query->total_despachada = max($cLlegada, $cSalida);
                $query->cant_recibida = $cLlegada;
                $query->cant_viajes_llegada = $vLlegada;
                $query->cant_despachada = $cSalida;
                $query->cant_viajes_salida = $vSalida;

            } else {
                $query->total_despachada = 0;
                $query->cant_recibida = 0;
                $query->cant_viajes_llegada = 0;
                $query->cant_despachada = 0;
                $query->cant_viajes_salida = 0;
            }

            return $this->solicitudesAppV2ToModel($query);
            //return $this->handleResponse($req, $this->solicitudesAppToArray($query->get()), __('messages.consultado'));
        } catch (\Throwable $th) {
            //throw $th;
            \Log::error('getSolicitudMaterial: ' . $th->getMessage());
            return null;
        }
    }

    private function getSolicitudAsfalto($idSolicitud)
    {
        try {
            $query = WbSolitudAsfalto::where('id_solicitudAsf', $idSolicitud)
                ->with([
                    'transporte' => function ($sub) {
                        $sub->where('estado', 1)->where('tipo_solicitud', 'A')->where('user_created', '!=', 0);
                    }
                ])
                ->select(
                    'id_solicitudAsf as identificador',
                    'id_solicitudAsf',
                    DB::raw("'A' as tipo"), // Ponemos el tipo de la solicitud, en el caso solicitud de asfalto
                    'fk_id_usuario',
                    'nombreCompañia',
                    'fechaSolicitud',
                    DB::raw("CAST(fechaSolicitud as DATE) AS dateCreation"),
                    'formula',
                    'abscisas',
                    'hito',
                    'tramo',
                    'calzada',
                    'cantidadToneladas',
                    'tipoMezcla',
                    'FechaHoraProgramacion',
                    DB::raw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) AS DATE) AS fechaProgramacion"),
                    'observaciones',
                    'CompañiaDestino',
                    'fechaAceptacion',
                    'toneFaltante',
                    'CostCode',
                    'toneladaReal',
                    'notaCierre',
                    'fk_id_project_Company',
                    'estado',
                )->first();

            if ($query == null) {
                return null;
            }

            // Extraemos los valores usando una expresión regular
            preg_match('/Inicial K(\d+)\+(\d+) - Final K(\d+)\+(\d+)/', $query->abscisas, $matches);

            if ($matches) {
                // Combinamos los grupos para obtener los valores completos
                $inicial = str_pad($matches[1] . $matches[2], 5, '0', STR_PAD_LEFT);
                $final = str_pad($matches[3] . $matches[4], 5, '0', STR_PAD_LEFT);

                $query->abscisaInicialReferencia = $inicial;
                $query->abscisaFinalReferencia = $final;
            }

            if ($query->transporte) {
                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($query->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->cantidad ? $tr->cantidad / 1000 : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->cantidad ? $tr->cantidad / 1000 : 0;
                    }
                }

                $query->total_despachada = max($cLlegada, $cSalida);
                $query->cant_recibida = $cLlegada;
                $query->cant_viajes_llegada = $vLlegada;
                $query->cant_despachada = $cSalida;
                $query->cant_viajes_salida = $vSalida;
            } else {
                $query->total_despachada = 0;
                $query->cant_recibida = 0;
                $query->cant_viajes_llegada = 0;
                $query->cant_despachada = 0;
                $query->cant_viajes_salida = 0;
            }

            return $this->solicitudesAsfaltoAppToModel($query);
        } catch (\Throwable $th) {
            \Log::error('getSolicitudAsfalto: ' . $th->getMessage());
            return null;
        }

    }


    public function getAppByFecha(Request $request)
    {

    }

    public function get(Request $request)
    {
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getListForIds(Request $req) // deprecated
    {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $listAsk = json_decode($req->datos, true);

        if (!is_array($listAsk) || sizeof($listAsk) == 0) {
            return $this->handleAlert('empty');
        }

        $query = WbSolicitudMateriales::with([
            'usuario' => function ($sub) {
                $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
            },
            'usuarioAprobador' => function ($sub) {
                $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
            },
            'materialLista' => function ($sub) {
                $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
            },
            'tipoCapa' => function ($sub) {
                $sub->select('id_tipo_capa', 'Descripcion');
            },
            'tipoCalzada' => function ($sub) {
                $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
            },
            'tipoCarril' => function ($sub) {
                $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
            },
            'formulaLista' => function ($sub) {
                $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
            },
            'plantas' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'plantaReasig' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'plantas_destino' => function ($sub) {
                $sub->select('id_plata', 'NombrePlanta', 'descripcion');
            },
            'transporte' => function ($sub) {
                $sub->with('equipo')->where('estado', 1)->where('tipo_solicitud', 'M')->where('user_created', '!=', 0);
            }
        ])
            ->whereDate('fechaProgramacion', '>=', Carbon::now()->subDays(3)->toDateString())
            ->where(function ($q) {
                $q->where('fk_id_estados', 12)
                    ->orWhereNotNull('user_despacho');
            })
            ->whereIn('id_solicitud_Materiales', $listAsk)
            ->select(
                'id_solicitud_Materiales as identificador',
                'id_solicitud_Materiales',
                DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                'fk_id_usuarios',
                'fk_id_usuarios_update',
                'fk_id_tipo_capa',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_tipo_carril',
                'fk_id_tipo_calzada',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'fk_id_plantaReasig',
                'Cantidad',
                'cantidad_real',
                'numeroCapa',
                'notaUsuario',
                'notaSU',
                'fk_id_planta_destino',
                DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                'fk_id_project_Company',
                'fk_id_tramo_origen',
                'fk_id_hito_origen',
                'fk_id_estados',
            );

        $query = $this->filtrarPorProyecto($req, $query)->orderBy('fechaProgramacion', 'DESC')->get();

        $query = $query->map(function ($item) {
            $info = WbFormulaCentroProduccion::select('codigoFormulaCdp')
                ->where('fk_id_formula_lista', $item->fk_id_formula)
                ->where('fk_id_planta', $item->fk_id_planta)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $item->fk_id_project_Company)
                ->orderBy('dateCreate', 'DESC')
                ->first();

            $item->fk_formula_cdp = $info->codigoFormulaCdp ?? null;

            if ($item->transporte) {

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($item->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    }
                }

                $item->total_despachada = max($cLlegada, $cSalida);
                $item->cant_recibida = $cLlegada;
                $item->cant_viajes_llegada = $vLlegada;
                $item->cant_despachada = $cSalida;
                $item->cant_viajes_salida = $vSalida;
            } else {
                $item->total_despachada = 0;
                $item->cant_recibida = 0;
                $item->cant_viajes_llegada = 0;
                $item->cant_despachada = 0;
                $item->cant_viajes_salida = 0;
            }

            return $this->solicitudesAppV2ToModel($item);
        });

        return $this->handleResponse($req, $query, __('messages.consultado'));
    }

    /* public function getAppV3(Request $req)
    {
        $material = $this->getSolicitudesMateriales($req);
        $asfalto = $this->getSolicitudesAsfalto($req);

        $respuesta = [];

        // Verificamos si $material no está vacío y lo añadimos al resultado
        if (!empty($material)) {
            $array = array($material);
            $respuesta = array_merge($respuesta, $array);
        }

        // Verificamos si $asfalto no está vacío y lo añadimos al resultado
        if (!empty($asfalto)) {
            $array = array($asfalto);
            $respuesta = array_merge($respuesta, $array);
        }

        return $this->handleResponse($req, $respuesta, __('messages.consultado'));
    } */

    public function getAppV3(Request $req)
    {
        $material = $this->getSolicitudesMateriales($req);
        $asfalto = $this->getSolicitudesAsfalto($req);

        $respuesta = $material->concat($asfalto);

        return $this->handleResponse($req, $respuesta, __('messages.consultado'));
    }

    public function getSolicitudesMateriales(Request $req)
    {
        $query = WbSolicitudMateriales::whereDate('fechaProgramacion', '>=', Carbon::now()->subDays(3)->toDateString())
            ->where(function ($q) {
                $q->where('fk_id_estados', 12)
                    ->orWhereNotNull('user_despacho');
            })
            ->with([
                'usuario' => function ($sub) {
                    $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                },
                'usuarioAprobador' => function ($sub) {
                    $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                },
                'materialLista' => function ($sub) {
                    $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
                },
                'tipoCapa' => function ($sub) {
                    $sub->select('id_tipo_capa', 'Descripcion');
                },
                'tipoCalzada' => function ($sub) {
                    $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
                },
                'tipoCarril' => function ($sub) {
                    $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
                },
                'formulaLista' => function ($sub) {
                    $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
                },
                'plantas' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'plantaReasig' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'plantas_destino' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'transporte' => function ($sub) {
                    $sub->with('equipo')->where('estado', 1)->where('tipo_solicitud', 'M')->where('user_created', '!=', 0);
                }
            ])->select(
                'id_solicitud_Materiales as identificador',
                'id_solicitud_Materiales',
                DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                'fk_id_usuarios',
                'fk_id_usuarios_update',
                'fk_id_tipo_capa',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_tipo_carril',
                'fk_id_tipo_calzada',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'fk_id_plantaReasig',
                'Cantidad',
                'cantidad_real',
                'numeroCapa',
                'notaUsuario',
                'notaSU',
                'fk_id_planta_destino',
                DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                'fk_id_project_Company',
                'fk_id_tramo_origen',
                'fk_id_hito_origen',
                'fk_id_estados',
            );

        $query = $this->filtrar($req, $query)->orderBy('fechaProgramacion', 'DESC')->get();

        $query = $query->map(function ($item) {
            $info = WbFormulaCentroProduccion::select('codigoFormulaCdp')
                ->where('fk_id_formula_lista', $item->fk_id_formula)
                ->where('fk_id_planta', $item->fk_id_planta)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $item->fk_id_project_Company)
                ->orderBy('dateCreate', 'DESC')
                ->first();

            $item->fk_formula_cdp = $info->codigoFormulaCdp ?? null;

            if ($item->transporte) {
                //$cubicaje = 0;
                //$viajes = 0;

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($item->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    }
                }

                $item->total_despachada = max($cLlegada, $cSalida);
                $item->cant_recibida = $cLlegada;
                $item->cant_viajes_llegada = $vLlegada;
                $item->cant_despachada = $cSalida;
                $item->cant_viajes_salida = $vSalida;
            } else {
                $item->total_despachada = 0;
                $item->cant_recibida = 0;
                $item->cant_viajes_llegada = 0;
                $item->cant_despachada = 0;
                $item->cant_viajes_salida = 0;
            }

            return $this->solicitudesAppV2ToModel($item);
        });

        return $query;
    }

    public function getSolicitudesAsfalto(Request $req)
    {
        $fecha = Carbon::now()->subDays(3)->toDateString();
        $query = WbSolitudAsfalto::where(function ($q) {
            $q->where('estado', 'PENDIENTE')
                ->orWhereNull('toneladaReal');
        })
            //where('estado', 'PENDIENTE')
            ->whereRaw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) as date) >= ?", [$fecha])
            ->with([
                'usuario',
                'plantas',
                'formula_asf',
                'cost_code',
                'transporte' => function ($sub) {
                    $sub->where('estado', 1)->where('tipo_solicitud', 'A')->where('user_created', '!=', 0);
                }
            ])
            ->select(
                'id_solicitudAsf as identificador',
                'id_solicitudAsf',
                DB::raw("'A' as tipo"), // Ponemos el tipo de la solicitud, en el caso solicitud de asfalto
                'fk_id_usuario',
                'nombreCompañia',
                'fechaSolicitud',
                DB::raw("CAST(fechaSolicitud as DATE) AS dateCreation"),
                'formula',
                'abscisas',
                'hito',
                'tramo',
                'calzada',
                'cantidadToneladas',
                'tipoMezcla',
                'FechaHoraProgramacion',
                DB::raw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) AS DATE) AS fechaProgramacion"),
                'observaciones',
                'CompañiaDestino',
                'fechaAceptacion',
                'toneFaltante',
                'CostCode',
                'toneladaReal',
                'notaCierre',
                'fk_id_project_Company',
                'estado',
            );

        $query = $this->filtrar($req, $query)
            ->orderByRaw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) as date) DESC")
            ->get();

        $query = $query->map(function ($item) {
            // Extraemos los valores entre "Inicial K"

            if (preg_match('/Inicial K(\d+)\+(\d+).*Final K(\d+)\+(\d+)/', $item->abscisas, $matches)) {
                // Combinamos los grupos para obtener los valores completos
                $inicial = str_pad($matches[1] . $matches[2], 5, '0', STR_PAD_LEFT);
                $final = str_pad($matches[3] . $matches[4], 5, '0', STR_PAD_LEFT);

                $item->abscisaInicialReferencia = $inicial;
                $item->abscisaFinalReferencia = $final;
            }

            if ($item->transporte) {
                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($item->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->cantidad ? $tr->cantidad / 1000 : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->cantidad ? $tr->cantidad / 1000 : 0;
                    }
                }

                $item->total_despachada = max($cLlegada, $cSalida);
                $item->cant_recibida = $cLlegada;
                $item->cant_viajes_llegada = $vLlegada;
                $item->cant_despachada = $cSalida;
                $item->cant_viajes_salida = $vSalida;
            } else {
                $item->total_despachada = 0;
                $item->cant_recibida = 0;
                $item->cant_viajes_llegada = 0;
                $item->cant_despachada = 0;
                $item->cant_viajes_salida = 0;
            }

            return $this->solicitudesAsfaltoAppToModel($item);
        });

        return $query;
    }



    /* Sincronizacion de solicitudes en segundo plano */
    public function getListForIdsV1(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $listAsk = json_decode($req->datos, true);

        if (!is_array($listAsk) || sizeof($listAsk) == 0) {
            return $this->handleAlert('empty');
        }

        $proy = $this->traitGetProyectoCabecera($req);

        $listMaterial = $this->getListForIdsMaterial($listAsk, $proy);

        $listAsfalto = $this->getListForIdsAsfalto($listAsk, $proy);

        $respuesta = $listMaterial->concat($listAsfalto);

        return $this->handleResponse($req, $respuesta, __('messages.consultado'));
    }

    public function getListForIdsV2(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $listAsk = json_decode($req->datos, true);

        if (!is_array($listAsk) || sizeof($listAsk) == 0) {
            return $this->handleAlert('empty');
        }

        // Separar en dos listas según el tipo de solicitud
        $listMaterial = [];
        $listAsfalto = [];

        foreach ($listAsk as $item) {
            if (!isset($item['tipo']) || !isset($item['tipo'])) {
                continue;
            }

            if ($item['tipo'] == 'M') {
                $listMaterial[] = $item['identificador'];
            } elseif ($item['tipo'] == 'A') {
                $listAsfalto[] = $item['identificador'];
            }
        }

        $proy = $this->traitGetProyectoCabecera($req);

        $respMaterial = $this->getListForIdsMaterial($listMaterial, $proy);

        $respAsfalto = $this->getListForIdsAsfalto($listAsfalto, $proy);

        $respuesta = $respMaterial->concat($respAsfalto);

        return $this->handleResponse($req, $respuesta, __('messages.consultado'));
    }

    private function getListForIdsMaterial($listAsk, $proy)
    {
        // Si la lista está vacía, retornar colección vacía para evitar errores
        if (empty($listAsk)) {
            return collect();
        }

        $query = WbSolicitudMateriales::whereDate('fechaProgramacion', '>=', Carbon::now()->subDays(3)->toDateString())
            ->where(function ($q) {
                $q->where('fk_id_estados', 12)
                    ->orWhereNotNull('user_despacho');
            })
            ->whereIn('id_solicitud_Materiales', $listAsk)
            ->where('fk_id_project_Company', $proy)->with([
                    'usuario' => function ($sub) {
                        $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                    },
                    'usuarioAprobador' => function ($sub) {
                        $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                    },
                    'materialLista' => function ($sub) {
                        $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
                    },
                    'tipoCapa' => function ($sub) {
                        $sub->select('id_tipo_capa', 'Descripcion');
                    },
                    'tipoCalzada' => function ($sub) {
                        $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
                    },
                    'tipoCarril' => function ($sub) {
                        $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
                    },
                    'formulaLista' => function ($sub) {
                        $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
                    },
                    'plantas' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'plantaReasig' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'plantas_destino' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'transporte' => function ($sub) {
                        $sub->with('equipo')->where('estado', 1)->where('tipo_solicitud', 'M')->where('user_created', '!=', 0);
                    }
                ])
            ->select(
                'id_solicitud_Materiales as identificador',
                'id_solicitud_Materiales',
                DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                'fk_id_usuarios',
                'fk_id_usuarios_update',
                'fk_id_tipo_capa',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_tipo_carril',
                'fk_id_tipo_calzada',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'fk_id_plantaReasig',
                'Cantidad',
                'cantidad_real',
                'numeroCapa',
                'notaUsuario',
                'notaSU',
                'fk_id_planta_destino',
                DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                'fk_id_project_Company',
                'fk_id_tramo_origen',
                'fk_id_hito_origen',
                'fk_id_estados',
            )->orderBy('fechaProgramacion', 'DESC')->get();

        $query = $query->map(function ($item) {
            $info = WbFormulaCentroProduccion::select('codigoFormulaCdp')
                ->where('fk_id_formula_lista', $item->fk_id_formula)
                ->where('fk_id_planta', $item->fk_id_planta)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $item->fk_id_project_Company)
                ->orderBy('dateCreate', 'DESC')
                ->first();

            $item->fk_formula_cdp = $info->codigoFormulaCdp ?? null;

            if ($item->transporte) {

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($item->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    }
                }

                $item->total_despachada = max($cLlegada, $cSalida);
                $item->cant_recibida = $cLlegada;
                $item->cant_viajes_llegada = $vLlegada;
                $item->cant_despachada = $cSalida;
                $item->cant_viajes_salida = $vSalida;
            } else {
                $item->total_despachada = 0;
                $item->cant_recibida = 0;
                $item->cant_viajes_llegada = 0;
                $item->cant_despachada = 0;
                $item->cant_viajes_salida = 0;
            }

            return $this->solicitudesAppV2ToModel($item);
        });

        return $query;
    }

    private function getListForIdsAsfalto($listAsk, $proy)
    {
        // Si la lista está vacía, retornar colección vacía para evitar errores
        if (empty($listAsk)) {
            return collect();
        }

        $fecha = Carbon::now()->subDays(3)->toDateString();
        $query = WbSolitudAsfalto::where(function ($q) {
            $q->where('estado', 'PENDIENTE')
                ->orWhereNull('toneladaReal');
        })
            //where('estado', 'PENDIENTE')
            ->whereRaw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) as date) >= ?", [$fecha])
            ->whereIn('id_solicitudAsf', $listAsk)
            ->where('fk_id_project_Company', $proy)
            ->with([
                'usuario',
                'plantas',
                'formula_asf',
                'cost_code',
                'transporte' => function ($sub) {
                    $sub->where('estado', 1)->where('tipo_solicitud', 'A')->where('user_created', '!=', 0);
                }
            ])
            ->select(
                'id_solicitudAsf as identificador',
                'id_solicitudAsf',
                DB::raw("'A' as tipo"), // Ponemos el tipo de la solicitud, en el caso solicitud de asfalto
                'fk_id_usuario',
                'nombreCompañia',
                'fechaSolicitud',
                DB::raw("CAST(fechaSolicitud as DATE) AS dateCreation"),
                'formula',
                'abscisas',
                'hito',
                'tramo',
                'calzada',
                'cantidadToneladas',
                'tipoMezcla',
                'FechaHoraProgramacion',
                DB::raw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) AS DATE) AS fechaProgramacion"),
                'observaciones',
                'CompañiaDestino',
                'fechaAceptacion',
                'toneFaltante',
                'CostCode',
                'toneladaReal',
                'notaCierre',
                'fk_id_project_Company',
                'estado',
            )
            ->orderByRaw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) as date) DESC")
            ->get();

        $query = $query->map(function ($item) {
            // Extraemos los valores entre "Inicial K"

            if (preg_match('/Inicial K(\d+)\+(\d+).*Final K(\d+)\+(\d+)/', $item->abscisas, $matches)) {
                // Combinamos los grupos para obtener los valores completos
                $inicial = str_pad($matches[1] . $matches[2], 5, '0', STR_PAD_LEFT);
                $final = str_pad($matches[3] . $matches[4], 5, '0', STR_PAD_LEFT);

                $item->abscisaInicialReferencia = $inicial;
                $item->abscisaFinalReferencia = $final;
            }

            if ($item->transporte) {
                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($item->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->cantidad ? $tr->cantidad / 1000 : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->cantidad ? $tr->cantidad / 1000 : 0;
                    }
                }

                $item->total_despachada = max($cLlegada, $cSalida);
                $item->cant_recibida = $cLlegada;
                $item->cant_viajes_llegada = $vLlegada;
                $item->cant_despachada = $cSalida;
                $item->cant_viajes_salida = $vSalida;
            } else {
                $item->total_despachada = 0;
                $item->cant_recibida = 0;
                $item->cant_viajes_llegada = 0;
                $item->cant_despachada = 0;
                $item->cant_viajes_salida = 0;
            }

            return $this->solicitudesAsfaltoAppToModel($item);
        });

        return $query;
    }

    public function getAppV4(Request $req)
    {
        $material = $this->getSolicitudesMaterialesV2($req);
        $asfalto = $this->getSolicitudesAsfaltoV2($req);

        $respuesta = $material->concat($asfalto);

        return $this->handleResponse($req, $respuesta, __('messages.consultado'));
    }

    public function getSolicitudesMaterialesV2(Request $req)
    {
        $estados = array_values($this->estados_soli_material);
        $query = WbSolicitudMateriales::whereDate('fechaProgramacion', '>=', Carbon::now()->subDays(3)->toDateString())

            ->whereIn('fk_id_estados', $estados)
            ->with([
                'usuario' => function ($sub) {
                    $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                },
                'usuarioAprobador' => function ($sub) {
                    $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                },
                'materialLista' => function ($sub) {
                    $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
                },
                'tipoCapa' => function ($sub) {
                    $sub->select('id_tipo_capa', 'Descripcion');
                },
                'tipoCalzada' => function ($sub) {
                    $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
                },
                'tipoCarril' => function ($sub) {
                    $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
                },
                'formulaLista' => function ($sub) {
                    $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
                },
                'plantas' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'plantaReasig' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'plantas_destino' => function ($sub) {
                    $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                },
                'transporte' => function ($sub) {
                    $sub->with('equipo')->where('estado', 1)->where('tipo_solicitud', 'M')->where('user_created', '!=', 0);
                },
                'formula_cdp'
            ])->select(
                'id_solicitud_Materiales as identificador',
                'id_solicitud_Materiales',
                DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                'fk_id_usuarios',
                'fk_id_usuarios_update',
                'fk_id_tipo_capa',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_tipo_carril',
                'fk_id_tipo_calzada',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'fk_id_plantaReasig',
                'Cantidad',
                'cantidad_real',
                'numeroCapa',
                'notaUsuario',
                'notaSU',
                'fk_id_planta_destino',
                DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                'fk_id_project_Company',
                'fk_id_tramo_origen',
                'fk_id_hito_origen',
                'fk_id_estados',
            );

        $query = $this->filtrarPorProyecto($req, $query)->orderBy('fechaProgramacion', 'DESC')->get();

        $query = $query->map(function ($item) {

            $item->fk_formula_cdp = $item->formula_cdp ? $item->formula_cdp->id_formula_centroProduccion : null;

            $item = $this->formatInfoItemTransport($item, $item->transporte);

            return $this->solicitudesAppV2ToModel($item);
        });

        return $query;
    }

    public function getSolicitudesAsfaltoV2(Request $req)
    {
        $estados = array_values($this->estados_soli_asfalto);
        $fecha = Carbon::now()->subDays(3)->toDateString();
        $query = WbSolitudAsfalto::where(function ($q) use ($estados) {
            $q->whereIn('estado', $estados)
                ->orWhereNull('toneladaReal');
        })
            ->whereRaw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) as date) >= ?", [$fecha])
            ->with([
                'usuario',
                'plantas',
                'formula_asf',
                'cost_code',
                'transporte' => function ($sub) {
                    $sub->where('estado', 1)->where('tipo_solicitud', 'A')->where('user_created', '!=', 0);
                }
            ])
            ->select(
                'id_solicitudAsf as identificador',
                'id_solicitudAsf',
                DB::raw("'A' as tipo"), // Ponemos el tipo de la solicitud, en el caso solicitud de asfalto
                'fk_id_usuario',
                'nombreCompañia',
                'fechaSolicitud',
                DB::raw("CAST(fechaSolicitud as DATE) AS dateCreation"),
                'formula',
                'abscisas',
                'hito',
                'tramo',
                'calzada',
                'cantidadToneladas',
                'tipoMezcla',
                'FechaHoraProgramacion',
                DB::raw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) AS DATE) AS fechaProgramacion"),
                'observaciones',
                'CompañiaDestino',
                'fechaAceptacion',
                'toneFaltante',
                'CostCode',
                'toneladaReal',
                'notaCierre',
                'fk_id_project_Company',
                'estado',
            );

        $query = $this->filtrarPorProyecto($req, $query)
            ->orderByRaw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) as date) DESC")
            ->get();

        $query = $query->map(function ($item) {
            // Extraemos los valores entre "Inicial K"

            if (preg_match('/Inicial K(\d+)\+(\d+).*Final K(\d+)\+(\d+)/', $item->abscisas, $matches)) {
                // Combinamos los grupos para obtener los valores completos
                $inicial = str_pad($matches[1] . $matches[2], 5, '0', STR_PAD_LEFT);
                $final = str_pad($matches[3] . $matches[4], 5, '0', STR_PAD_LEFT);

                $item->abscisaInicialReferencia = $inicial;
                $item->abscisaFinalReferencia = $final;
            }

            $item = $this->formatInfoItemTransport($item, $item->transporte);

            return $this->solicitudesAsfaltoAppToModel($item);
        });

        return $query;
    }

    public function getAppV5(Request $req)
    {
        try {
            $material = $this->getSolicitudesMaterialesV2($req);
            $asfalto = $this->getSolicitudesAsfaltoV2($req);
            $concreto = $this->getSolicitudesConcreto($req);

            $concat = $material->concat($asfalto);
            $respuesta = $concat->concat($concreto);

            return $this->handleResponse($req, $respuesta, __('messages.consultado'));
        } catch (\Throwable $e) {
            \Log::error('' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }

    }

    public function getSolicitudesConcreto(Request $req)
    {
        $estados = array_values($this->estados_soli_concreto);
        $fecha = Carbon::now()->subDays(3)->format('j-n-Y'); //->toDateString();
        $query = solicitudConcreto::whereIn('estado', $estados)
            ->whereNotNull('fechaProgramacion')
            ->where('fechaProgramacion', '<>', 'N/A')
            ->whereRaw("CONVERT(DATE, fechaProgramacion, 103) >=  CONVERT(DATE, ?, 103)", [$fecha])
            ->with([
                'usuario',
                'plantas',
                'formula_concreto',
                'cost_code',
                'transporte' => function ($sub) {
                    $sub->where('estado', 1)->where('tipo_solicitud', 'C')->where('user_created', '!=', 0);
                }
            ])
            ->select(
                'id_solicitud as identificador',
                'id_solicitud',
                DB::raw("'C' as tipo"), // Ponemos el tipo de la solicitud, en el caso solicitud de asfalto
                'fk_usuario',
                'elementoVaciar',
                'nomenclatura',
                'tipoMezcla',
                'fechaHoraSolicitud',
                DB::raw("CONVERT(DATE, [fechaHoraSolicitud], 103) AS dateCreation"),
                'fk_id_formula',
                'abscisas',
                'hito',
                'tramo',
                'volumen',
                'volumenReal',
                'resistencia',
                'asentamiento',
                'nota',
                'fechaProgramacion',
                DB::raw("CONVERT(DATE, [fechaProgramacion], 103) AS fechaDeProgramacion"),
                'nota',
                'PlantaDestino',
                'fechaAceptacion',
                'CostCode',
                'toneFaltante',
                'notaCierre',
                'fk_id_project_Company',
                'estado',
            );

        $query = $this->filtrarPorProyecto($req, $query)
            ->orderByRaw("CONVERT(DATE,[fechaProgramacion], 103) DESC")
            ->get();

        $query = $query->map(function ($item) {

            if ($item->abscisas && $item->abscisas != '...') {
                // Extraemos los valores entre "Inicial K"
                if (preg_match('/Inicial K(\d+)\+(\d+).*Final K(\d+)\+(\d+)/', $item->abscisas, $matches)) {
                    // Combinamos los grupos para obtener los valores completos
                    $inicial = str_pad($matches[1] . $matches[2], 5, '0', STR_PAD_LEFT);
                    $final = str_pad($matches[3] . $matches[4], 5, '0', STR_PAD_LEFT);

                    $item->abscisaInicialReferencia = $inicial;
                    $item->abscisaFinalReferencia = $final;
                } else {
                    $cadena = str_replace(['K', '+'], '', $item->abscisas); // Elimina 'K' y '+'
                    $inicial = explode(',', $cadena)[0]; // Toma solo lo antes de la coma
                    $item->abscisaInicialReferencia = $inicial;
                }
            }

            $item = $this->formatInfoItemTransport($item, $item->transporte);

            return $this->solicitudesConcretoAppToModel($item);
        });

        return $query;
    }


    public function getListForIdsV3(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $listAsk = json_decode($req->datos, true);

        if (!is_array($listAsk) || sizeof($listAsk) == 0) {
            return $this->handleAlert('empty');
        }

        // Separar en dos listas según el tipo de solicitud
        $listMaterial = [];
        $listAsfalto = [];

        foreach ($listAsk as $item) {
            if (!isset($item['tipo']) || !isset($item['tipo'])) {
                continue;
            }

            if ($item['tipo'] == 'M') {
                $listMaterial[] = $item['identificador'];
            } elseif ($item['tipo'] == 'A') {
                $listAsfalto[] = $item['identificador'];
            }
        }

        $proy = $this->traitGetProyectoCabecera($req);

        $respMaterial = $this->getListForIdsMaterialV2($listMaterial, $proy);

        $respAsfalto = $this->getListForIdsAsfaltoV2($listAsfalto, $proy);

        $respuesta = $respMaterial->concat($respAsfalto);

        return $this->handleResponse($req, $respuesta, __('messages.consultado'));
    }

    private function getListForIdsMaterialV2($listAsk, $proy)
    {
        // Si la lista está vacía, retornar colección vacía para evitar errores
        if (empty($listAsk)) {
            return collect();
        }

        $estados = array_values($this->estados_soli_material);
        $query = WbSolicitudMateriales::whereDate('fechaProgramacion', '>=', Carbon::now()->subDays(3)->toDateString())
            ->whereIn('fk_id_estados', $estados)
            ->whereIn('id_solicitud_Materiales', $listAsk)
            ->where('fk_id_project_Company', $proy)->with([
                    'usuario' => function ($sub) {
                        $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                    },
                    'usuarioAprobador' => function ($sub) {
                        $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                    },
                    'materialLista' => function ($sub) {
                        $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
                    },
                    'tipoCapa' => function ($sub) {
                        $sub->select('id_tipo_capa', 'Descripcion');
                    },
                    'tipoCalzada' => function ($sub) {
                        $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
                    },
                    'tipoCarril' => function ($sub) {
                        $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
                    },
                    'formulaLista' => function ($sub) {
                        $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
                    },
                    'plantas' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'plantaReasig' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'plantas_destino' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'transporte' => function ($sub) {
                        $sub->with('equipo')->where('estado', 1)->where('tipo_solicitud', 'M')->where('user_created', '!=', 0);
                    }
                ])
            ->select(
                'id_solicitud_Materiales as identificador',
                'id_solicitud_Materiales',
                DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                'fk_id_usuarios',
                'fk_id_usuarios_update',
                'fk_id_tipo_capa',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_tipo_carril',
                'fk_id_tipo_calzada',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'fk_id_plantaReasig',
                'Cantidad',
                'cantidad_real',
                'numeroCapa',
                'notaUsuario',
                'notaSU',
                'fk_id_planta_destino',
                DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                'fk_id_project_Company',
                'fk_id_tramo_origen',
                'fk_id_hito_origen',
                'fk_id_estados',
            )->orderBy('fechaProgramacion', 'DESC')->get();

        $query = $query->map(function ($item) {
            $info = WbFormulaCentroProduccion::select('codigoFormulaCdp')
                ->where('fk_id_formula_lista', $item->fk_id_formula)
                ->where('fk_id_planta', $item->fk_id_planta)
                ->where('Estado', 'A')
                ->where('fk_id_project_Company', $item->fk_id_project_Company)
                ->orderBy('dateCreate', 'DESC')
                ->first();

            $item->fk_formula_cdp = $info->codigoFormulaCdp ?? null;

            if ($item->transporte) {

                $vLlegada = $vSalida = $cLlegada = $cSalida = 0;

                foreach ($item->transporte as $tr) {
                    if ($tr->tipo == 1) {
                        $vLlegada++;
                        $cLlegada += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    } else if ($tr->tipo == 2) {
                        $vSalida++;
                        $cSalida += $tr->equipo && $tr->equipo->cubicaje ? $tr->equipo->cubicaje : 0;
                    }
                }

                $item->total_despachada = max($cLlegada, $cSalida);
                $item->cant_recibida = $cLlegada;
                $item->cant_viajes_llegada = $vLlegada;
                $item->cant_despachada = $cSalida;
                $item->cant_viajes_salida = $vSalida;
            } else {
                $item->total_despachada = 0;
                $item->cant_recibida = 0;
                $item->cant_viajes_llegada = 0;
                $item->cant_despachada = 0;
                $item->cant_viajes_salida = 0;
            }

            return $this->solicitudesAppV2ToModel($item);
        });

        return $query;
    }

    private function getListForIdsMaterialV3($listAsk, $proy)
    {
        // Si la lista está vacía, retornar colección vacía para evitar errores
        if (empty($listAsk)) {
            return collect();
        }

        $estados = array_values($this->estados_soli_material);
        $query = WbSolicitudMateriales::whereDate('fechaProgramacion', '>=', Carbon::now()->subDays(3)->toDateString())
            ->whereIn('fk_id_estados', $estados)
            ->whereIn('id_solicitud_Materiales', $listAsk)
            ->where('fk_id_project_Company', $proy)->with([
                    'usuario' => function ($sub) {
                        $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                    },
                    'usuarioAprobador' => function ($sub) {
                        $sub->select('id_usuarios', 'usuario', 'Nombre', 'Apellido');
                    },
                    'materialLista' => function ($sub) {
                        $sub->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida');
                    },
                    'tipoCapa' => function ($sub) {
                        $sub->select('id_tipo_capa', 'Descripcion');
                    },
                    'tipoCalzada' => function ($sub) {
                        $sub->select('id_tipo_calzada', 'Calzada', 'Descripcion');
                    },
                    'tipoCarril' => function ($sub) {
                        $sub->select('id_tipo_carril', 'Carril', 'Descripcion');
                    },
                    'formulaLista' => function ($sub) {
                        $sub->select('id_formula_lista', 'Nombre', 'formulaDescripcion');
                    },
                    'plantas' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'plantaReasig' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'plantas_destino' => function ($sub) {
                        $sub->select('id_plata', 'NombrePlanta', 'descripcion');
                    },
                    'formula_cdp' => function ($sub) {
                        $sub->where('Estado', 'A');
                    },
                    'transporte' => function ($sub) {
                        $sub->with('equipo')->where('estado', 1)->where('tipo_solicitud', 'M')->where('user_created', '!=', 0);
                    }
                ])
            ->select(
                'id_solicitud_Materiales as identificador',
                'id_solicitud_Materiales',
                DB::raw("'M' as tipo"), // Ponemos el tipo de la solicitud, en este caso solicitud de material
                'fk_id_usuarios',
                'fk_id_usuarios_update',
                'fk_id_tipo_capa',
                'fk_id_tramo',
                'fk_id_hito',
                'abscisaInicialReferencia',
                'abscisaFinalReferencia',
                'fk_id_tipo_carril',
                'fk_id_tipo_calzada',
                'fk_id_material',
                'fk_id_formula',
                'fk_id_planta',
                'fk_id_plantaReasig',
                'Cantidad',
                'cantidad_real',
                'numeroCapa',
                'notaUsuario',
                'notaSU',
                'fk_id_planta_destino',
                DB::raw("CAST(fechaProgramacion as DATE) as fechaProgramacion"),
                DB::raw("CAST(dateCreation as DATE) as dateCreation"),
                'fk_id_project_Company',
                'fk_id_tramo_origen',
                'fk_id_hito_origen',
                'fk_id_estados',
            )->orderBy('fechaProgramacion', 'DESC')->get();

        $query = $query->map(function ($item) {

            $item->fk_formula_cdp = $item->formula_cdp ? $item->formula_cdp->id_formula_centroProduccion : null;

            $item = $this->formatInfoItemTransport($item, $item->transporte);

            return $this->solicitudesAppV2ToModel($item);
        });

        return $query;
    }

    private function formatInfoItemTransport($item, $transporte)
    {
        if ($transporte) {
            $tipo = $item->tipo;
            $vLlegada = $vSalida = $cLlegada = $cSalida = 0;
            $vLlegada = $transporte->where('tipo', 1)->count();
            $vSalida = $transporte->where('tipo', 2)->count();

            $colSuma = $tipo == 'M' ? 'cubicaje' : 'cantidad';

            $cLlegada = $transporte->where('tipo', 1)->sum($colSuma);
            $cSalida = $transporte->where('tipo', 2)->sum($colSuma);

            if ($tipo == 'A') {
                $cLlegada /= 1000;
                $cSalida /= 1000;
            }

            $item->total_despachada = max($cLlegada, $cSalida);
            $item->cant_recibida = $cLlegada;
            $item->cant_viajes_llegada = $vLlegada;
            $item->cant_despachada = $cSalida;
            $item->cant_viajes_salida = $vSalida;
        } else {
            $item->total_despachada = 0;
            $item->cant_recibida = 0;
            $item->cant_viajes_llegada = 0;
            $item->cant_despachada = 0;
            $item->cant_viajes_salida = 0;
        }

        return $item;
    }

    private function getListForIdsAsfaltoV2($listAsk, $proy)
    {
        // Si la lista está vacía, retornar colección vacía para evitar errores
        if (empty($listAsk)) {
            return collect();
        }

        $estados = array_values($this->estados_soli_asfalto);
        $fecha = Carbon::now()->subDays(3)->toDateString();
        $query = WbSolitudAsfalto::where(function ($q) use ($estados) {
            //$q->where('estado', 'PENDIENTE')
            $q->whereIn('estado', $estados)
                ->orWhereNull('toneladaReal');
        })
            ->whereRaw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) as date) >= ?", [$fecha])
            ->whereIn('id_solicitudAsf', $listAsk)
            ->where('fk_id_project_Company', $proy)
            ->with([
                'usuario',
                'plantas',
                'formula_asf',
                'cost_code',
                'transporte' => function ($sub) {
                    $sub->where('estado', 1)->where('tipo_solicitud', 'A')->where('user_created', '!=', 0);
                }
            ])
            ->select(
                'id_solicitudAsf as identificador',
                'id_solicitudAsf',
                DB::raw("'A' as tipo"), // Ponemos el tipo de la solicitud, en el caso solicitud de asfalto
                'fk_id_usuario',
                'nombreCompañia',
                'fechaSolicitud',
                DB::raw("CAST(fechaSolicitud as DATE) AS dateCreation"),
                'formula',
                'abscisas',
                'hito',
                'tramo',
                'calzada',
                'cantidadToneladas',
                'tipoMezcla',
                'FechaHoraProgramacion',
                DB::raw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) AS DATE) AS fechaProgramacion"),
                'observaciones',
                'CompañiaDestino',
                'fechaAceptacion',
                'toneFaltante',
                'CostCode',
                'toneladaReal',
                'notaCierre',
                'fk_id_project_Company',
                'estado',
            )
            ->orderByRaw("CAST(LEFT(FechaHoraProgramacion, CHARINDEX(' ', FechaHoraProgramacion + ' ') - 1) as date) DESC")
            ->get();

        $query = $query->map(function ($item) {
            // Extraemos los valores entre "Inicial K"

            if (preg_match('/Inicial K(\d+)\+(\d+).*Final K(\d+)\+(\d+)/', $item->abscisas, $matches)) {
                // Combinamos los grupos para obtener los valores completos
                $inicial = str_pad($matches[1] . $matches[2], 5, '0', STR_PAD_LEFT);
                $final = str_pad($matches[3] . $matches[4], 5, '0', STR_PAD_LEFT);

                $item->abscisaInicialReferencia = $inicial;
                $item->abscisaFinalReferencia = $final;
            }

            $item = $this->formatInfoItemTransport($item, $item->transporte);

            return $this->solicitudesAsfaltoAppToModel($item);
        });

        return $query;
    }

    /* public function getListForIdsV4(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $listAsk = json_decode($req->datos, true);

        if (!is_array($listAsk) || sizeof($listAsk) == 0) {
            return $this->handleAlert('empty');
        }

        // Separar en dos listas según el tipo de solicitud
        $listMaterial = [];
        $listAsfalto = [];
        $listConcreto = [];

        foreach ($listAsk as $item) {
            if (!isset($item['tipo']) || !isset($item['tipo'])) {
                continue;
            }

            if ($item['tipo'] == 'M') {
                $listMaterial[] = $item['identificador'];
            } elseif ($item['tipo'] == 'A') {
                $listAsfalto[] = $item['identificador'];
            } elseif ($item['tipo'] == 'C') {
                $listConcreto[] = $item['identificador'];
            }
        }

        $proy = $this->traitGetProyectoCabecera($req);

        $respMaterial = $this->getListForIdsMaterialV2($listMaterial, $proy);

        $respAsfalto = $this->getListForIdsAsfaltoV2($listAsfalto, $proy);

        $respConcreto = $this->getListForIdsConcreto($listConcreto, $proy);

        $concat = $respMaterial->concat($respAsfalto);

        $respuesta = $concat->concat($respConcreto);

        return $this->handleResponse($req, $respuesta, __('messages.consultado'));
    } */

    public function getListForIdsV4(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'datos' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->handleAlert($validate->errors());
        }

        $listAsk = json_decode($req->datos, true);

        if (!is_array($listAsk) || sizeof($listAsk) == 0) {
            return $this->handleAlert('empty');
        }

        $classifiedList = collect($listAsk)
            ->filter(fn($item) => isset($item['tipo'], $item['identificador']))
            ->reduce(function ($obj, $item) {
                $obj[$item['tipo'][] = $item['identificador']];
                return $obj;
            }, ['M' => [], 'A' => [], 'C' => []]);

        $proy = $this->traitGetProyectoCabecera($req);

        $respuesta = collect([])
            ->concat($this->getListForIdsMaterialV2($classifiedList['M'], $proy))
            ->concat($this->getListForIdsAsfaltoV2($classifiedList['A'], $proy))
            ->concat($this->getListForIdsConcreto($classifiedList['C'], $proy));

        return $this->handleResponse($req, $respuesta, __('messages.consultado'));
    }

    private function getListForIdsConcreto($listAsk, $proy)
    {
        // Si la lista está vacía, retornar colección vacía para evitar errores
        if (empty($listAsk)) {
            return collect();
        }

        $estados = array_values($this->estados_soli_asfalto);
        $fecha = Carbon::now()->subDays(3)->format('j-n-Y');
        $query = solicitudConcreto::whereIn('estado', $estados)
            ->whereNotNull('fechaProgramacion')
            ->where('fechaProgramacion', '<>', 'N/A')
            ->whereIn('id_solicitud', $listAsk)
            ->where('fk_id_project_Company', $proy)
            ->whereRaw("CONVERT(DATE, fechaProgramacion, 103) >=  CONVERT(DATE, ?, 103)", [$fecha])
            ->with([
                'usuario',
                'plantas',
                'formula_concreto',
                'cost_code',
                'transporte' => function ($sub) {
                    $sub->where('estado', 1)->where('tipo_solicitud', 'C')->where('user_created', '!=', 0);
                }
            ])
            ->select(
                'id_solicitud as identificador',
                'id_solicitud',
                DB::raw("'C' as tipo"), // Ponemos el tipo de la solicitud, en el caso solicitud de asfalto
                'fk_usuario',
                'elementoVaciar',
                'nomenclatura',
                'tipoMezcla',
                'fechaHoraSolicitud',
                DB::raw("CONVERT(DATE, [fechaHoraSolicitud], 103) AS dateCreation"),
                'fk_id_formula',
                'abscisas',
                'hito',
                'tramo',
                'volumen',
                'volumenReal',
                'resistencia',
                'asentamiento',
                'nota',
                'fechaProgramacion',
                DB::raw("CONVERT(DATE, [fechaProgramacion], 103) AS fechaDeProgramacion"),
                'nota',
                'PlantaDestino',
                'fechaAceptacion',
                'CostCode',
                'toneFaltante',
                'notaCierre',
                'fk_id_project_Company',
                'estado',
            )
            ->orderByRaw("CONVERT(DATE, [fechaProgramacion], 103) DESC")
            ->get();

        $query = $query->map(function ($item) {
            if ($item->abscisas && $item->abscisas != '...') {
                // Extraemos los valores entre "Inicial K"
                if (preg_match('/Inicial K(\d+)\+(\d+).*Final K(\d+)\+(\d+)/', $item->abscisas, $matches)) {
                    // Combinamos los grupos para obtener los valores completos
                    $inicial = str_pad($matches[1] . $matches[2], 5, '0', STR_PAD_LEFT);
                    $final = str_pad($matches[3] . $matches[4], 5, '0', STR_PAD_LEFT);

                    $item->abscisaInicialReferencia = $inicial;
                    $item->abscisaFinalReferencia = $final;
                } else {
                    $cadena = str_replace(['K', '+'], '', $item->abscisas); // Elimina 'K' y '+'
                    $inicial = explode(',', $cadena)[0]; // Toma solo lo antes de la coma
                    $item->abscisaInicialReferencia = $inicial;
                }
            }

            $item = $this->formatInfoItemTransport($item, $item->transporte);

            return $this->solicitudesConcretoAppToModel($item);
        });

        return $query;
    }

    private function getSolicitudConcreto($idSolicitud)
    {
        try {
            $query = solicitudConcreto::where('id_solicitud', $idSolicitud)
                ->with([
                    'transporte' => function ($sub) {
                        $sub->where('estado', 1)->where('tipo_solicitud', 'C')->where('user_created', '!=', 0);
                    }
                ])
                ->select(
                    'id_solicitud as identificador',
                    'id_solicitud',
                    DB::raw("'C' as tipo"), // Ponemos el tipo de la solicitud, en el caso solicitud de asfalto
                    'fk_usuario',
                    'elementoVaciar',
                    'nomenclatura',
                    'tipoMezcla',
                    'fechaHoraSolicitud',
                    DB::raw("CONVERT(DATE, [fechaHoraSolicitud], 103) AS dateCreation"),
                    'fk_id_formula',
                    'abscisas',
                    'hito',
                    'tramo',
                    'volumen',
                    'volumenReal',
                    'resistencia',
                    'asentamiento',
                    'nota',
                    'fechaProgramacion',
                    DB::raw("CONVERT(DATE, [fechaProgramacion], 103) AS fechaDeProgramacion"),
                    'nota',
                    'PlantaDestino',
                    'fechaAceptacion',
                    'CostCode',
                    'toneFaltante',
                    'notaCierre',
                    'fk_id_project_Company',
                    'estado',
                )->first();

            if ($query == null) {
                return null;
            }

            if ($query->abscisas && $query->abscisas != '...') {
                // Extraemos los valores entre "Inicial K"
                if (preg_match('/Inicial K(\d+)\+(\d+).*Final K(\d+)\+(\d+)/', $query->abscisas, $matches)) {
                    // Combinamos los grupos para obtener los valores completos
                    $inicial = str_pad($matches[1] . $matches[2], 5, '0', STR_PAD_LEFT);
                    $final = str_pad($matches[3] . $matches[4], 5, '0', STR_PAD_LEFT);

                    $query->abscisaInicialReferencia = $inicial;
                    $query->abscisaFinalReferencia = $final;
                } else {
                    $cadena = str_replace(['K', '+'], '', $query->abscisas); // Elimina 'K' y '+'
                    $inicial = explode(',', $cadena)[0]; // Toma solo lo antes de la coma
                    $query->abscisaInicialReferencia = $inicial;
                }
            }

            $query = $this->formatInfoItemTransport($query, $query->transporte);

            return $this->solicitudesConcretoAppToModel($query);
        } catch (\Throwable $th) {
            \Log::error('getSolicitudConcreto: ' . $th->getMessage());
            return null;
        }

    }
}
