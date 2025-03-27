<?php
namespace App\Http\Controllers\Transporte;
use App\Http\Controllers\BaseController;
use App\Models\Transporte\WbTransporteRegistro;
use App\Models\WbSolicitudMateriales;
class TransporteTicketController extends BaseController
{


    public function index($ticket)
    {
        try {
            // Primera consulta: basada en el ticket
            $transporteInicial = WbTransporteRegistro::where('ticket', $ticket)
                ->with([
                    'solicitudes' => function ($sub) {
                        $sub->morphWith([
                            '*' => function ($query) {
                                $query->with('usuario');

                                if ($query->getModel() instanceof WbSolicitudMateriales) {
                                    $query->with('usuarioAprobador');
                                }
                            }
                        ]);
                    },
                    'origenPlanta',
                    'origenTramo',
                    'origenTramoId',
                    'origenHito',
                    'origenHitoId',
                    'destinoPlanta',
                    'destinoTramo',
                    'destinoTramoId',
                    'destinoHito',
                    'destinoHitoId',
                    'cdc',
                    'material',
                    'formulas',
                    'usuario_created',
                    'equipo'
                ])
                ->orderBy('tipo', 'DESC')
                ->get();

            // Segunda consulta: basada en codigo_viaje y fk_id_equipo
            $transporteFiltrado = collect(); // Inicializar vacío
            if ($transporteInicial->isNotEmpty()) {
                $primerRegistro = $transporteInicial->first();
                $codigoViaje = $primerRegistro->codigo_viaje;
                $fkIdEquipo = $primerRegistro->fk_id_equipo;

                $transporteFiltrado = WbTransporteRegistro::where('codigo_viaje', $codigoViaje)
                    ->where('fk_id_equipo', $fkIdEquipo)
                    ->with(relations: [
                        'solicitudes' => function ($sub) {
                            $sub->morphWith([
                                '*' => function ($query) {
                                    $query->with('usuario');

                                    if ($query->getModel() instanceof WbSolicitudMateriales) {
                                        $query->with('usuarioAprobador');
                                    }
                                }
                            ]);
                        },
                        'origenPlanta',
                        'origenTramo',
                        'origenTramoId',
                        'origenHito',
                        'origenHitoId',
                        'destinoPlanta',
                        'destinoTramo',
                        'destinoTramoId',
                        'destinoHito',
                        'destinoHitoId',
                        'cdc',
                        'material',
                        'formulas',
                        'usuario_created',
                        'equipo'
                    ])
                    ->orderBy('tipo', 'DESC')
                    ->get();
            }

            // Comparar las dos consultas y usar la que tenga más registros
            if (empty($codigoViaje) || $transporteInicial->count() >= $transporteFiltrado->count()) {
                return $this->processTransportData($transporteInicial);
            } else {
                return $this->processTransportData($transporteFiltrado);
            }

        } catch (\Exception $e) {
            return $this->handleError("Error al procesar ticket", $e->getMessage());

        }
    }

    private function processTransportData($transporte)
    {
        try {
            $lang = request()->getPreferredLanguage(['es', 'en', 'it']);
            app()->setLocale($lang);
            if ($transporte->isEmpty()) {
                return view('transporteTicket3');
            }
            $viaje = collect();
            $item = $transporte->first();
            if ($item->solicitudes) {
                $mapping = [
                    'fechaProgramacion' => $item->solicitudes ? ($item->tipo_solicitud == 'M' ? $item->solicitudes->fechaProgramacion :
                        ($item->tipo_solicitud == 'A' ? $item->solicitudes->FechaHoraProgramacion :
                            $item->solicitudes->fechaProgramacion
                        )
                    ) : null,
                    'nota_usuario' => $item->solicitudes ? $item->solicitudes->notaUsuario : null,
                    'cantidad' => $item->solicitudes ? $item->solicitudes->Cantidad : null,
                    'solicitante' => $item->solicitudes && $item->solicitudes->usuario ?
                        $item->solicitudes->usuario->Nombre . ' ' . $item->solicitudes->usuario->Apellido : null,
                    'solicitud' => $item->fk_id_solicitud,
                    'nota_su' => $item->solicitudes ? $item->solicitudes->notaSU : null,
                    'super_aprobador' => $item->solicitudes && $item->solicitudes->usuarioAprobador ?
                        $item->solicitudes->usuarioAprobador->Nombre . ' ' . $item->solicitudes->usuarioAprobador->Apellido : null,
                ];
                $viaje->push($mapping);
            } else {
                $map2 = [
                    'solicitud2' => __('messages.solicitud_no') . $item->fk_id_solicitud . __('messages.solicitud_no_encontrada'),
                ];
                $viaje->push($map2);
            }

            $item2 = $transporte->first();
            $mapping2 = null;

            if ($item2) {
                $mapping2 = [
                    'voucher' => $item2->ticket,
                    'ubicacion_entrada' => $item2->ubicacion_gps,
                    'ubicacion_salida' => $item2->ubicacion_gps,
                    'solicitud' => $item2->fk_id_solicitud,
                    'solicitante' => $item2->solicitudes && $item2->solicitudes->usuario ? $item2->solicitudes->usuario->Nombre . " " . $item2->solicitudes->usuario->Apellido : null,
                    'tipo' => $item2->tipo,
                    'plantaOrigen' => $item2->origenPlanta ? $item2->origenPlanta->NombrePlanta : null,
                    'tramoOrigen' => $item2->origenTramoId ? $item2->origenTramoId->Id_Tramo . " - " . $item2->origenTramoId->Descripcion : ($item2->origenTramo ? $item2->origenTramo->Id_Tramo . " - " . $item2->origenTramo->Descripcion : null),
                    'hitoOrigen' => $item2->origenHitoId ? $item2->origenHitoId->Id_Hitos . " - " . $item2->origenHitoId->Descripcion : ($item2->origenHito ? $item2->origenHito->Id_Hitos . " - " . $item2->origenHito->Descripcion : null),
                    'abscisaOrigen' => $item2->abscisa_origen ? 'K' . substr($item2->abscisa_origen, 0, 2) . '+' . substr($item2->abscisa_origen, 2, 3) : null,
                    'plantaDestino' => $item2->destinoPlanta ? $item2->destinoPlanta->NombrePlanta : null,
                    'tramoDestino' => $item2->destinoTramoId ? $item2->destinoTramoId->Id_Tramo . " - " . $item2->destinoTramoId->Descripcion : ($item2->destinoTramo ? $item2->destinoTramo->Id_Tramo . " - " . $item2->destinoTramo->Descripcion : null),
                    'hitoDestino' => $item2->destinoHitoId ? $item2->destinoHitoId->Id_Hitos . " - " . $item2->destinoHitoId->Descripcion : ($item2->destinoHito ? $item2->destinoHito->Id_Hitos . " - " . $item2->destinoHito->Descripcion : null),
                    'tramoDestino2' => $item2->destinoTramoId ? $item2->destinoTramoId->Id_Tramo : '',
                    'hitoDestino2' => $item2->destinoHitoId ? $item2->destinoHitoId->Id_Hitos : '',
                    'abscisaDestino' => $item2->abscisa_destino ? 'K' . substr($item2->abscisa_destino, 0, 2) . '+' . substr($item2->abscisa_destino, 2, 3) : null,
                    'costCenter' => $item2->fk_id_cost_center,
                    'material' => $item2->material ? $item2->material->Nombre . " (" . $item2->material->id_material_lista . ")" : null,
                    'formula' => $item2->formulas ? ($item2->tipo_solicitud == 'M' ? $item2->formulas->Nombre . " (" . $item2->formulas->id_formula_lista . ")" :
                        ($item2->tipo_solicitud == 'A' ? $item2->formulas->asfalt_formula . " (" . $item2->formulas->id_asfal_formula . ")" :
                            $item2->formulas->formula . " (" . $item2->formulas->resistencia . "-" . $item2->formulas->dmx . "-" . $item2->formulas->relacion . ")"
                        )
                    ) : null,
                    'cantidad' => $item2->cantidad && $item2->material ? $item2->cantidad . " " . $item2->material->unidadMedida :
                        ($item2->cantidad && $item2->formulas ? $item2->cantidad . " " . ($item2->tipo_solicitud == 'M' ? $item2->formulas->unidadMedida :
                            ($item2->tipo_solicitud == 'A' ? 'Toneladas' : 'Metros cubicos')
                        ) : null),
                    'observacion' => $item2->observacion,
                    'fechaRegistro' => $item2->fecha_registro,
                    'creadoPor' => $item2->usuario_created ? $item2->usuario_created->Nombre . " " . $item2->usuario_created->Apellido : null,
                    'equipo' => $item2->equipo && $item2->equipo->equiment_id ? $item2->equipo->equiment_id : null,
                    'placa' => $item2->equipo && $item2->equipo->placa ? $item2->equipo->placa : null,
                    'cubicaje' => $item2->equipo && $item2->equipo->cubicaje ? $item2->equipo->cubicaje : null,
                    'contratista' => $item2->equipo && $item2->equipo->compania ? $item2->equipo->compania->nombreCompañia : null,
                    'chofer' => $item2->chofer,
                    'nota_su' => $item->solicitudes ? $item->solicitudes->notaSU : null,
                    'icon' => $item2->tipo == 1 ? 'inbox-arrow-down' : 'truck',
                ];
            }

            // Procesa los datos del transporte
            foreach ($transporte as $key) {
                $mapping = [
                    'voucher' => $key->ticket,
                    'solicitud' => $key->fk_id_solicitud,
                    'solicitante' => $key->solicitudes && $key->solicitudes->usuario ? $key->solicitudes->usuario->Nombre . " " . $key->solicitudes->usuario->Apellido : null,
                    'tipo' => $key->tipo,
                    'plantaOrigen' => $key->origenPlanta ? $key->origenPlanta->NombrePlanta : null,
                    'tramoOrigen' => $key->origenTramoId ? $key->origenTramoId->Id_Tramo . " - " . $key->origenTramoId->Descripcion : ($key->origenTramo ? $key->origenTramo->Id_Tramo . " - " . $key->origenTramo->Descripcion : null),
                    'hitoOrigen' => $key->origenHitoId ? $key->origenHitoId->Id_Hitos . " - " . $key->origenHitoId->Descripcion : ($key->origenHito ? $key->origenHito->Id_Hitos . " - " . $key->origenHito->Descripcion : null),
                    'abscisaOrigen' => $key->abscisa_origen ? 'K' . substr($key->abscisa_origen, 0, 2) . '+' . substr($key->abscisa_origen, 2, 3) : null,
                    'plantaDestino' => $key->destinoPlanta ? $key->destinoPlanta->NombrePlanta : null,
                    'tramoDestino' => $key->destinoTramoId ? $key->destinoTramoId->Id_Tramo . " - " . $key->destinoTramoId->Descripcion : ($key->destinoTramo ? $key->destinoTramo->Id_Tramo . " - " . $key->destinoTramo->Descripcion : null),
                    'hitoDestino' => $key->destinoHitoId ? $key->destinoHitoId->Id_Hitos . " - " . $key->destinoHitoId->Descripcion : ($key->destinoHito ? $key->destinoHito->Id_Hitos . " - " . $key->destinoHito->Descripcion : null),
                    'tramoDestino2' => $item2->destinoTramoId ? $item2->destinoTramoId->Id_Tramo : '',
                    'hitoDestino2' => $item2->destinoHitoId ? $item2->destinoHitoId->Id_Hitos : '',
                    'abscisaDestino' => $key->abscisa_destino ? 'K' . substr($key->abscisa_destino, 0, 2) . '+' . substr($key->abscisa_destino, 2, 3) : null,
                    'costCenter' => $key->fk_id_cost_center,
                    'material' => $key->material ? $key->material->Nombre . " (" . $key->material->id_material_lista . ")" : null,
                    'formula' => $key->formulas ? ($key->tipo_solicitud == 'M' ? $key->formulas->Nombre . " (" . $key->formulas->id_formula_lista . ")" :
                        ($key->tipo_solicitud == 'A' ? $key->formulas->asfalt_formula . " (" . $key->formulas->id_asfal_formula . ")" :
                            $key->formulas->formula . " (" . $key->formulas->resistencia . "-" . $key->formulas->dmx . "-" . $key->formulas->relacion . ")"
                        )
                    ) : null,
                    'cantidad' => $key->cantidad && $key->material ? $key->cantidad . " " . $key->material->unidadMedida :
                        ($key->cantidad && $key->formulas ? $key->cantidad . " " . ($key->tipo_solicitud == 'M' ? $key->formulas->unidadMedida :
                            ($key->tipo_solicitud == 'A' ? 'Toneladas' : 'Metros cubicos')
                        ) : null),
                    'observacion' => $key->observacion,
                    'fechaRegistro' => $key->fecha_registro,
                    'creadoPor' => $key->usuario_created ? $key->usuario_created->Nombre . " " . $key->usuario_created->Apellido : null,
                    'equipo' => $key->equipo && $key->equipo->equiment_id ? $key->equipo->equiment_id : null,
                    'placa' => $key->equipo && $key->equipo->placa ? $key->equipo->placa : null,
                    'cubicaje' => $key->equipo && $key->equipo->cubicaje ? $key->equipo->cubicaje : null,
                    'contratista' => $key->equipo && $key->equipo->compania ? $key->equipo->compania->nombreCompañia : null,
                    'chofer' => $key->chofer,
                    'nota_su' => $item->solicitudes ? $item->solicitudes->notaSU : null,
                    'icon' => $key->tipo == 1 ? 'inbox-arrow-down' : 'truck'
                ];
                $viaje->push($mapping);
            }

            $tipo1Count = $transporte->where('tipo', 1)->count();
            $tipo2Count = $transporte->where('tipo', 2)->count();
            $conteoTipos = [
                'tipo1' => $tipo1Count,
                'tipo2' => $tipo2Count,
            ];
            if (($transporte)->get(0)) {
                $map = [
                    'tipo' => $transporte->get(0)->tipo == 1 ? 2 : 1,
                    'observacion' => __('messages.no_sincronizado'),
                    'icon' => $transporte->get(0)->tipo == 1 ? 'truck' : 'inbox-arrow-down',
                    'plantaOrigen' => $key->origenPlanta ? $key->origenPlanta->NombrePlanta : null
                ];
                if ($transporte->get(0)->tipo == 1 && $conteoTipos['tipo1'] == 1 && $conteoTipos['tipo2'] == 0) {
                    $viaje->splice(1, 0, [$map]);
                } else if ($transporte->get(0)->tipo == 2 && $conteoTipos['tipo2'] == 1 && $conteoTipos['tipo1'] == 0) {
                    $viaje->splice($viaje->count(), 0, [$map]);
                }
            }

            $ubicacion_entrada = $transporte->last()->ubicacion_gps ?? null;
            $ubicacion_salida = $transporte->first()->ubicacion_gps ?? null;

            return view('transporteTicket3', [
                "transport" => $viaje,
                "conteoTipos" => $conteoTipos,
                "card" => $mapping2,
                "ubicacion_entrada" => $ubicacion_entrada,
                "ubicacion_salida" => $ubicacion_salida,
            ]);
        } catch (\Exception $e) {
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }
}
