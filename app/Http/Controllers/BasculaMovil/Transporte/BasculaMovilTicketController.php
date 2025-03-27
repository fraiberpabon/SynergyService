<?php
namespace App\Http\Controllers\BasculaMovil\Transporte;
use App\Http\Controllers\BaseController;
use App\Models\BasculaMovil\WbBasculaMovilTransporte;
class BasculaMovilTicketController extends BaseController
{


    public function index($ticket)
    {
        try {
            // Primera consulta: basada en el ticket
            $transporteInicial = WbBasculaMovilTransporte::where('boucher', $ticket)
                ->with([
                    'origenPlanta',
                    'origenTramo',
                    'origenHito',
                    'cdcOrigen',
                    'destinoPlanta',
                    'destinoTramo',
                    'destinoHito',
                    'cdcDestino',
                    'material',
                    'formula',
                    'formulaAsf',
                    'usuario_creador',
                    'equipo',
                    'conductores'
                ])
                ->orderBy('tipo', 'DESC')
                ->get();

            // Segunda consulta: basada en codigo_viaje y fk_id_equipo
            $transporteFiltrado = collect(); // Inicializar vacío
            if ($transporteInicial->isNotEmpty()) {
                $primerRegistro = $transporteInicial->first();
                $codigoViaje = $primerRegistro->codigo_transporte;
                $fkIdEquipo = $primerRegistro->fk_id_equipo;

                $transporteFiltrado = WbBasculaMovilTransporte::where('codigo_transporte', $codigoViaje)
                    ->where('fk_id_equipo', $fkIdEquipo)
                    ->with([
                        'origenPlanta',
                        'origenTramo',
                        'origenHito',
                        'cdcOrigen',
                        'destinoPlanta',
                        'destinoTramo',
                        'destinoHito',
                        'cdcDestino',
                        'material',
                        'formula',
                        'formulaAsf',
                        'usuario_creador',
                        'equipo',
                        'conductores'
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

        $mapping2 = null;

        if ($item) {
            $mapping2 = [
                'voucher' => $item->boucher,
                'ubicacion_entrada' =>  $item->ubicacion_gps,
                'ubicacion_salida' =>  $item->ubicacion_gps,
                'tipo' => $item->tipo,
                'externo' => $item->es_externo,

                'plantaOrigen' => $item->origenPlanta ? $item->origenPlanta->NombrePlanta : null,
                'tramoOrigen' => $item->origenTramo ? $item->origenTramo->Id_Tramo . " - " . $item->origenTramo->Descripcion : null,
                'hitoOrigen' => $item->origenHito ? $item->origenHito->Id_Hitos . " - " . $item->origenHito->Descripcion : null,
                'otroOrigen' => $item->otro_origen,
                'costCenterOrigen' => $item->fk_id_cost_center_origen,

                'plantaDestino' => $item->destinoPlanta ? $item->destinoPlanta->NombrePlanta : null,
                'tramoDestino' => $item->destinoTramo ? $item->destinoTramo->Id_Tramo . " - " . $item->destinoTramo->Descripcion : null,
                'hitoDestino' => $item->destinoHito ? $item->destinoHito->Id_Hitos . " - " . $item->destinoHito->Descripcion : null,
                'otroDestino' => $item->otro_destino,
                'costCenterDestino' => $item->fk_id_cost_center_destino,

                'material' => $item->material ? $item->material->Nombre . " (" . $item->material->id_material_lista . ")" : null,
                'formula' => $item->formula ? $item->formula->Nombre . " (" . $item->formula->id_formula_lista . ")" : null,
                'formulaAsf' => $item->formulaAsf ? $item->formulaAsf->asfalt_formula . " (" . $item->formulaAsf->id_asfal_formula . ")" : null,
                'formulaTipo' => $item->tipo_formula ? $item->tipo_formula : null,

                'peso1' => $item->peso1,
                'peso2' => $item->peso2,
                'pesoNeto' => $item->peso_neto,

                'observacion' => $item->observacion,

                'fechaRegistro' => $item->fecha_registro,
                'fechaRegistroPeso2' => $item->fecha_registro_peso2,

                'creadoPor' => $item->usuario_creador ? $item->usuario_creador->Nombre . " " . $item->usuario_creador->Apellido : null,
                'equipo' => $item->equipo && $item->equipo->equiment_id ? $item->equipo->equiment_id : null,
                'placa' => $item->equipo && $item->equipo->placa ? $item->equipo->placa : null,
                'cubicaje' => $item->equipo && $item->equipo->cubicaje ? $item->equipo->cubicaje : null,
                'contratista' => $item->equipo && $item->equipo->compania ? $item->equipo->compania->nombreCompañia : null,
                'conductor' => $item->conductores ? $item->conductores->dni . "\n" . $item->conductores->nombreCompleto : null,
                'icon' => $item->tipo == 1 ? 'inbox-arrow-down' : 'truck',
            ];

        }
        // Procesa los datos del transporte
        foreach ($transporte as $key) {
            $mapping = [
                'voucher' => $key->boucher,
                'tipo' => $key->tipo,
                'externo' => $key->es_externo,

                'plantaOrigen' => $key->origenPlanta ? $key->origenPlanta->NombrePlanta : null,
                'tramoOrigen' => $key->origenTramo ? $key->origenTramo->Id_Tramo . " - " . $key->origenTramo->Descripcion : null,
                'hitoOrigen' => $key->origenHito ? $key->origenHito->Id_Hitos . " - " . $key->origenHito->Descripcion : null,
                'otroOrigen' => $key->otro_origen ? $key->otro_origen : null,
                'costCenterOrigen' => $key->fk_id_cost_center_origen,

                'plantaDestino' => $key->destinoPlanta ? $key->destinoPlanta->NombrePlanta : null,
                'tramoDestino' => $key->destinoTramo ? $key->destinoTramo->Id_Tramo . " - " . $key->destinoTramo->Descripcion : null,
                'hitoDestino' => $key->destinoHito ? $key->destinoHito->Id_Hitos . " - " . $key->destinoHito->Descripcion : null,
                'otroDestino' => $key->otro_destino,
                'costCenterDestino' => $key->fk_id_cost_center_destino,

                'material' => $key->material ? $key->material->Nombre . " (" . $key->material->id_material_lista . ")" : null,
                'formula' => $key->formula ? $key->formula->Nombre . " (" . $key->formula->id_formula_lista . ")" : null,
                'formulaAsf' => $key->formulaAsf ? $key->formulaAsf->asfalt_formula . " (" . $key->formulaAsf->id_asfal_formula . ")" : null,
                'formulaTipo' => $key->tipo_formula ? $key->tipo_formula : null,

                'peso1' => $key->peso1,
                'peso2' => $key->peso2,
                'pesoNeto' => $key->peso_neto,

                'observacion' => $key->observacion,

                'fechaRegistro' => $key->fecha_registro,
                'fechaRegistroPeso2' => $key->fecha_registro_peso2,

                'creadoPor' => $key->usuario_creador ? $key->usuario_creador->Nombre . " " . $key->usuario_creador->Apellido : null,
                'equipo' => $key->equipo && $key->equipo->equiment_id ? $key->equipo->equiment_id : null,
                'placa' => $key->equipo && $key->equipo->placa ? $key->equipo->placa : null,
                'cubicaje' => $key->equipo && $key->equipo->cubicaje ? $key->equipo->cubicaje : null,
                'contratista' => $key->equipo && $key->equipo->compania ? $key->equipo->compania->nombreCompañia : null,
                'conductor' => $item->conductores ? $item->conductores->dni . "\n" . $item->conductores->nombreCompleto : null,
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

        $viaje = $viaje->sortByDesc('tipo');

        $ubicacion_entrada = $transporte->last()->ubicacion_gps ?? null;
        $ubicacion_salida = $transporte->first()->ubicacion_gps ?? null;

        return view('basculaMovilTicket', [
            "scale" => $viaje,
            "conteoTipos" => $conteoTipos,
            "card" => $mapping2,
            "ubicacion_entrada" => $ubicacion_entrada,
            "ubicacion_salida" => $ubicacion_salida,
        ]);
    }
}
