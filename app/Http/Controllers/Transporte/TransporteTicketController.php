<?php
namespace App\Http\Controllers\Transporte;
use App\Http\Controllers\BaseController;
use App\Models\Transporte\WbTransporteRegistro;
class TransporteTicketController extends BaseController
{
   

    public function index($ticket)
    {
        try {
            // Primera consulta: basada en el ticket
            $transporteInicial = WbTransporteRegistro::where('ticket', $ticket)
                ->with([
                    'solicitud' => function ($sub) {
                        $sub->with('usuario');
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
                    'formula',
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
                    ->with([
                        'solicitud' => function ($sub) {
                            $sub->with('usuario');
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
                        'formula',
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

        $lang = request()->getPreferredLanguage(['es', 'en', 'it']);
        app()->setLocale($lang);
        if ($transporte->isEmpty()) {
            return view('transporteTicket3');
        }
        $viaje = collect();
        $item = $transporte->first();
        if ($item->solicitud) {
            $mapping = [
                'solicitud' => $item->fk_id_solicitud ,
                'fechaProgramacion' => $item->solicitud ? $item->solicitud->fechaProgramacion : null,
                'nota_usuario' => $item->solicitud ? $item->solicitud->notaUsuario : null,
                'cantidad' => $item->solicitud ? $item->solicitud->Cantidad . ' '. $item->material->unidadMedida : null,
                'solicitante' => $item->solicitud && $item->solicitud->usuario ?
                    $item->solicitud->usuario->Nombre . ' ' . $item->solicitud->usuario->Apellido : null,

            ];
            $viaje->push($mapping);
        }else{
                $map2 = [
                    'solicitud2' => __('messages.solicitud_no').$item->fk_id_solicitud . __('messages.solicitud_no_encontrada'),
                ];
                $viaje->push($map2);
        }

        $item2 = $transporte->first();
        $mapping2 = null;

        if ($item2) {
            $mapping2 = [
                'voucher' => $item2->ticket,
                'solicitud' => $item2->fk_id_solicitud? $item2->fk_id_solicitud :null,
                'solicitante' => $item2->solicitud && $item2->solicitud->usuario ? $item2->solicitud->usuario->Nombre . " " . $item2->solicitud->usuario->Apellido : null,
                'tipo' => $item2->tipo,
                'plantaOrigen' => $item2->origenPlanta ? $item2->origenPlanta->NombrePlanta : null,
                'tramoOrigen' => $item2->origenTramoId ? $item2->origenTramoId->Id_Tramo . " - " . $item2->origenTramoId->Descripcion : ($item2->origenTramo ? $item2->origenTramo->Id_Tramo . " - " . $item2->origenTramo->Descripcion : null),
                'hitoOrigen' => $item2->origenHitoId ? $item2->origenHitoId->Id_Hitos . " - " . $item2->origenHitoId->Descripcion : ($item2->origenHito ? $item2->origenHito->Id_Hitos . " - " . $item2->origenHito->Descripcion : null),
                'abscisaOrigen' => $item2->abscisa_origen ? 'K' . substr($item2->abscisa_origen, 0, 2) . '+' . substr($item2->abscisa_origen, 2, 3) : null,
                'plantaDestino' => $item2->destinoPlanta ? $item2->destinoPlanta->NombrePlanta : null,
                'tramoDestino' => $item2->destinoTramoId ? $item2->destinoTramoId->Id_Tramo . " - " . $item2->destinoTramoId->Descripcion : ($item2->destinoTramo ? $item2->destinoTramo->Id_Tramo . " - " . $item2->destinoTramo->Descripcion : null),
                'hitoDestino' => $item2->destinoHitoId ? $item2->destinoHitoId->Id_Hitos . " - " . $item2->destinoHitoId->Descripcion : ($item2->destinoHito ? $item2->destinoHito->Id_Hitos . " - " . $item2->destinoHito->Descripcion : null),
                'abscisaDestino' => $item2->abscisa_destino ? 'K' . substr($item2->abscisa_destino, 0, 2) . '+' . substr($item2->abscisa_destino, 2, 3) : null,
                'costCenter' => $item2->fk_id_cost_center,
                'material' => $item2->material ? $item2->material->Nombre . " (" . $item2->material->id_material_lista . ")" : null,
                'formula' => $item2->formula ? $item2->formula->Nombre . " (" . $item2->formula->id_formula_lista . ")" : null,
                'cantidad' => $item2->cantidad && $item2->material ? $item2->cantidad . " " . $item2->material->unidadMedida : ($item2->cantidad && $item2->formula ? $item2->cantidad . " " . $item2->formula->unidadMedida : null),
                'observacion' => $item2->observacion,
                'fechaRegistro' => $item2->fecha_registro,
                'creadoPor' => $item2->usuario_created ? $item2->usuario_created->Nombre . " " . $item2->usuario_created->Apellido : null,
                'equipo' => $item2->equipo && $item2->equipo->equiment_id ? $item2->equipo->equiment_id : null,
                'placa' => $item2->equipo && $item2->equipo->placa ? $item2->equipo->placa : null,
                'cubicaje' => $item2->equipo && $item2->equipo->cubicaje ? $item2->equipo->cubicaje : null,
                'contratista' => $item2->equipo && $item2->equipo->compania ? $item2->equipo->compania->nombreCompañia : null,
                'chofer' => $item2->chofer,
                'icon' => $item2->tipo == 1 ? 'inbox-arrow-down' : 'truck'
            ];
          
        }    
        // Procesa los datos del transporte
        foreach ($transporte as $key) {
            $mapping = [
                'voucher' => $key->ticket,
                 'solicitud' => $key->fk_id_solicitud ,
                'solicitante' => $key->solicitud && $key->solicitud->usuario ? $key->solicitud->usuario->Nombre . " " . $key->solicitud->usuario->Apellido : null,
                'tipo' => $key->tipo,
                'plantaOrigen' => $key->origenPlanta ? $key->origenPlanta->NombrePlanta : null,
                'tramoOrigen' => $key->origenTramoId ? $key->origenTramoId->Id_Tramo . " - " . $key->origenTramoId->Descripcion : ($key->origenTramo ? $key->origenTramo->Id_Tramo . " - " . $key->origenTramo->Descripcion : null),
                'hitoOrigen' => $key->origenHitoId ? $key->origenHitoId->Id_Hitos . " - " . $key->origenHitoId->Descripcion : ($key->origenHito ? $key->origenHito->Id_Hitos . " - " . $key->origenHito->Descripcion : null),
                'abscisaOrigen' => $key->abscisa_origen ? 'K' . substr($key->abscisa_origen, 0, 2) . '+' . substr($key->abscisa_origen, 2, 3) : null,
                'plantaDestino' => $key->destinoPlanta ? $key->destinoPlanta->NombrePlanta : null,
                'tramoDestino' => $key->destinoTramoId ? $key->destinoTramoId->Id_Tramo . " - " . $key->destinoTramoId->Descripcion : ($key->destinoTramo ? $key->destinoTramo->Id_Tramo . " - " . $key->destinoTramo->Descripcion : null),
                'hitoDestino' => $key->destinoHitoId ? $key->destinoHitoId->Id_Hitos . " - " . $key->destinoHitoId->Descripcion : ($key->destinoHito ? $key->destinoHito->Id_Hitos . " - " . $key->destinoHito->Descripcion : null),
                'abscisaDestino' => $key->abscisa_destino ? 'K' . substr($key->abscisa_destino, 0, 2) . '+' . substr($key->abscisa_destino, 2, 3) : null,
                'costCenter' => $key->fk_id_cost_center,
                'material' => $key->material ? $key->material->Nombre . " (" . $key->material->id_material_lista . ")" : null,
                'formula' => $key->formula ? $key->formula->Nombre . " (" . $key->formula->id_formula_lista . ")" : null,
                'cantidad' => $key->cantidad && $key->material ? $key->cantidad . " " . $key->material->unidadMedida : ($key->cantidad && $key->formula ? $key->cantidad . " " . $key->formula->unidadMedida : null),
                'observacion' => $key->observacion,
                'fechaRegistro' => $key->fecha_registro,
                'creadoPor' => $key->usuario_created ? $key->usuario_created->Nombre . " " . $key->usuario_created->Apellido : null,
                'equipo' => $key->equipo && $key->equipo->equiment_id ? $key->equipo->equiment_id : null,
                'placa' => $key->equipo && $key->equipo->placa ? $key->equipo->placa : null,
                'cubicaje' => $key->equipo && $key->equipo->cubicaje ? $key->equipo->cubicaje : null,
                'contratista' => $key->equipo && $key->equipo->compania ? $key->equipo->compania->nombreCompañia : null,
                'chofer' => $key->chofer,
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
                'tipo' =>$transporte->get(0)->tipo==1 ?  2 : 1,
               'observacion' => __('messages.no_sincronizado'),
                'icon' =>  $transporte->get(0)->tipo==1 ? 'truck' :'inbox-arrow-down' ,
            ];
            if ($transporte->get(0)->tipo == 1 && $conteoTipos['tipo1']==1  && $conteoTipos['tipo2']==0 ) {
                $viaje->splice(1, 0, [$map]);
            } else if ($transporte->get(0)->tipo == 2 && $conteoTipos['tipo2']==1 && $conteoTipos['tipo1']==0) {
                $viaje->splice($viaje->count(), 0, [$map]);
            }
        } 
    
        return view('transporteTicket3', [
            "transport" => $viaje,
            "conteoTipos" => $conteoTipos,
            "card" => $mapping2,
        ]);
    }
}
