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
            //$data = $req->data;

            //$data = str_replace(" ", "+", $data);

            //$decript = encryptUrl::decryptMsg($data);

            //$info = explode(";", $decript);

            //return response()->json(["info" => $decript]);

            //$proy = isset($info[18]) ? $info[18] : (isset($info[17]) ? $info[17] : "" );

            //$transporte = WbTransporteRegistro::where('ticket', $ticket)->orderBy('tipo', 'DESC')->get();

            $transporte = WbTransporteRegistro::where('ticket', $ticket)
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
                ->orderBy('tipo', 'DESC')->get();

            if ($transporte->isEmpty()) {
                return view('transporteTicket3');
            }

            $viaje = collect();

            /* $item = WbSolicitudMateriales::where('id_solicitud_Materiales', $transporte[0]->fk_id_solicitud)
                ->where('fk_id_project_Company', $transporte[0]->fk_id_project_Company)->first(); */

            $item = $transporte->first();

            if ($item->solicitud) {

                // Array con los datos para la vista
                $mapping = [
                    'fechaProgramacion' => $item->solicitud ? $item->solicitud->fechaProgramacion : null,
                    'solicitante' => $item->solicitud && $item->solicitud->usuario ?
                        $item->solicitud->usuario->Nombre . ' ' . $item->solicitud->usuario->Apellido : null,
                ];

                $viaje->push($mapping);
            }

            foreach ($transporte as $key) {
                $voucher = $key->ticket;
                $solicitud = $key->fk_id_solicitud;
                $tipo = $key->tipo;
                $plantaOrigen = $key->origenPlanta ? $key->origenPlanta : null;
                $tramoOrigen = $key->origenTramoId ? $key->origenTramoId : ($key->origenTramo ? $key->origenTramo : null);
                $hitoOrigen = $key->origenHitoId ? $key->origenHitoId : ($key->origenHito ? $key->origenHito : null);
                $abscisaOrigen = $key->abscisa_origen;
                $plantaDestino = $key->destinoPlanta ? $key->destinoPlanta : null;
                $tramoDestino = $key->destinoTramoId ? $key->destinoTramoId : ($key->destinoTramo ? $key->destinoTramo : null);
                $hitoDestino = $key->destinoHitoId ? $key->destinoHitoId : ($key->destinoHito ? $key->destinoHito : null);
                $abscisaDestino = $key->abscisa_destino;
                $costCenter = $key->fk_id_cost_center;
                $material = $key->material ? $key->material : null;
                $formula = $key->formula ? $key->formula : null;
                $cantidad = $key->cantidad;
                $observacion = $key->observacion;
                $fechaRegistro = $key->fecha_registro;
                $creadoPor = $key->usuario_created ? $key->usuario_created : null;
                $equipo = $key->equipo ? $key->equipo : null;
                $chofer = $key->chofer;

                // Array con los datos para la vista
                $mapping = [
                    'voucher' => $voucher,
                    'solicitud' => $solicitud,
                    'tipo' => $tipo, //== "1" ? "llegada" : "salida",
                    'plantaOrigen' => $plantaOrigen ? $plantaOrigen->NombrePlanta : null,
                    'tramoOrigen' => $tramoOrigen ? $tramoOrigen->Id_Tramo . " - " . $tramoOrigen->Descripcion : null,
                    'hitoOrigen' => $hitoOrigen ? $hitoOrigen->Id_Hitos . " - " . $hitoOrigen->Descripcion : null,
                    'abscisaOrigen' => $abscisaOrigen ? 'K' . substr($abscisaOrigen, 0, 2) . '+' . substr($abscisaOrigen, 2, 3) : null,
                    'plantaDestino' => $plantaDestino ? $plantaDestino->NombrePlanta : null,
                    'tramoDestino' => $tramoDestino ? $tramoDestino->Id_Tramo . " - " . $tramoDestino->Descripcion : null,
                    'hitoDestino' => $hitoDestino ? $hitoDestino->Id_Hitos . " - " . $hitoDestino->Descripcion : null,
                    'abscisaDestino' => $abscisaDestino ? 'K' . substr($abscisaDestino, 0, 2) . '+' . substr($abscisaDestino, 2, 3) : null,
                    'costCenter' => $costCenter,
                    'material' => $material ? $material->Nombre . " (" . $material->id_material_lista . ")" : null,
                    'formula' => $formula ? $formula->Nombre . " (" . $formula->id_formula_lista . ")" : null,
                    'cantidad' => $cantidad && $material ? $cantidad . " " . $material->unidadMedida : ($cantidad && $formula ? $cantidad . " " . $formula->unidadMedida : null),
                    'observacion' => $observacion,
                    'fechaRegistro' => $fechaRegistro,
                    'creadoPor' => $creadoPor ? $creadoPor->Nombre . " " . $creadoPor->Apellido : null,
                    'equipo' => $equipo && $equipo->equiment_id ? $equipo->equiment_id : null,
                    'placa' => $equipo && $equipo->placa ? $equipo->placa : null,
                    'cubicaje' => $equipo && $equipo->cubicaje ? $equipo->cubicaje : null,
                    'contratista' => $equipo && $equipo->compania ? $equipo->compania->nombreCompañia : null,
                    'chofer' => $chofer,
                ];

                $viaje->push($mapping);
            }

            return view('transporteTicket3', ["transport" => $viaje]); //, ['equipos' => $equipos]);
        } catch (\Exception $e) {
            return $this->handleError("Error al procesar ticket", $e->getMessage());
        }
    }
}
