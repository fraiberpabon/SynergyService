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

            $transporte = WbTransporteRegistro::where('ticket', $ticket)->orderBy('tipo', 'DESC')->get();

            if ($transporte->isEmpty()) {
                return view('transporteTicket2');
            }

            $item = null;
            $solicitante = null;
            $viaje = collect();

            $item = WbSolicitudMateriales::where('id_solicitud_Materiales', $transporte[0]->fk_id_solicitud)
                ->where('fk_id_project_Company', $transporte[0]->fk_id_project_Company)->first();

            if ($item != null && $item->fk_id_usuarios != null) {
                $solicitante = usuarios_M::where('id_usuarios', $item->fk_id_usuarios)->where('fk_id_project_Company', $item->fk_id_project_Company)->first();

                // Array con los datos para la vista
                $mapping = [
                    'fechaProgramacion' => $item ? $item->fechaProgramacion : null,
                    'solicitante' => $solicitante ? $solicitante->Nombre . ' ' . $solicitante->Apellido : null,
                ];

                $viaje->push($mapping);
            }

            foreach ($transporte as $key) {
                $voucher = $key->ticket;
                $solicitud = $key->fk_id_solicitud;
                $tipo = $key->tipo;
                $plantaOrigen = UsuPlanta::where('id_plata', $key->fk_id_planta_origen)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $tramoOrigen = WbTramos::where('Id_Tramo', $key->fk_id_tramo_origen)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $hitoOrigen = WbHitos::where('Id_Hitos', $key->fk_id_hito_origen)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $abscisaOrigen = $key->abscisa_origen;
                $plantaDestino = UsuPlanta::where('id_plata', $key->fk_id_planta_destino)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $tramoDestino = WbTramos::where('Id_Tramo', $key->fk_id_tramo_destino)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $hitoDestino = WbHitos::where('Id_Hitos', $key->fk_id_hito_destino)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $abscisaDestino = $key->abscisa_destino;
                $costCenter = $key->fk_id_cost_center;
                $material = WbMaterialLista::where('id_material_lista', $key->fk_id_material)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $formula = WbFormulaLista::where('id_formula_lista', $key->fk_id_formula)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $cantidad = $key->cantidad;
                $observacion = $key->observacion;
                $fechaRegistro = $key->fecha_registro;
                $creadoPor = usuarios_M::where('id_usuarios', $key->user_created)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
                $equipo = WbEquipo::with("compania")->where('id', $key->fk_id_equipo)->where('fk_id_project_Company', $key->fk_id_project_Company)->first();
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

            return view('transporteTicket2', ["transport" => $viaje]); //, ['equipos' => $equipos]);
        } catch (\Exception $e) {
            return $this->handleError("Error al procesar ticket", $e->getMessage());
        }
    }
}
