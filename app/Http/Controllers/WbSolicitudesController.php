<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbFormulaCentroProduccion;
use App\Models\WbSolicitudMateriales;
use App\Models\WbSolitudAsfalto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WbSolicitudesController extends BaseController implements Vervos
{
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
        if ($tipo == 'M') {
            $repuesta = $this->getSolicitudMaterial($idSolicitud);
        } else {
            $repuesta = $this->getSolicitudAsfalto($idSolicitud);
        }
        return $repuesta;
    }

    private function getSolicitudMaterial($idSolicitud)
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
                    'usuario',
                    'plantas',
                    'formula_asf',
                    'cost_code',
                    'transporte' => function ($sub) {
                        $sub->where('estado', 1)->where('tipo_solicitud', 'A');
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

    public function getListForIds(Request $req)
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
}
