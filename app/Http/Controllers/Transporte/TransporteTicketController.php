<?php

namespace App\Http\Controllers\Transporte;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\encryptUrl;
use App\Models\CostCode;
use App\Models\Equipos\WbEquipo;
use App\Models\Materiales\WbMaterialLista;
use App\Models\Transporte\WbTransporteRegistro;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbFormulaLista;
use App\Models\WbHitos;
use App\Models\WbMaterialFormula;
use App\Models\WbSolicitudMateriales;
use App\Models\WbTramos;
use Illuminate\Http\Request;

class TransporteTicketController extends BaseController
{
    public function index($ticket, Request $req)
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
            if ($transporteInicial->count() >= $transporteFiltrado->count()) {
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
        if ($transporte->isEmpty()) {
            return view('transporteTicket3');
        }
        $viaje = collect();
        $item = $transporte->first();
        if ($item->solicitud) {
            $mapping = [
                'fechaProgramacion' => $item->solicitud ? $item->solicitud->fechaProgramacion : null,
                'nota_usuario' => $item->solicitud ? $item->solicitud->notaUsuario : null,
                'solicitante' => $item->solicitud && $item->solicitud->usuario ?
                    $item->solicitud->usuario->Nombre . ' ' . $item->solicitud->usuario->Apellido : null,
            ];
            $viaje->push($mapping);
        }    
        // Procesa los datos del transporte
        foreach ($transporte as $key) {
            $mapping = [
                'voucher' => $key->ticket,
                 'solicitud' => $key->fk_id_solicitud,
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
            ];
            $viaje->push($mapping);
        }
        return view('transporteTicket3', ["transport" => $viaje]);
    }
}
