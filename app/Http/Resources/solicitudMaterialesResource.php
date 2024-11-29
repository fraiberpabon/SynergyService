<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;
class solicitudMaterialesResource extends ResourceCollection
{

    public function toArray($request)
    {
        return
        $this->collection->map(function($modelo) {
            $destino = ($modelo->plantas_destino) ? $modelo->plantas_destino->NombrePlanta : $modelo->tramo->Id_Tramo . '-' . $modelo->hitos->Id_Hitos;
            if ($modelo->plantas_destino) {
                $abscisa = '';
            } else {
                $abscisaInicial = $modelo->abscisaInicialReferencia;
                $abscisaFinal = $modelo->abscisaFinalReferencia;
                $abscisa = 'K' . substr($abscisaInicial, 0, 2) . '+' . substr($abscisaInicial, 2) .
                    '-K' . substr($abscisaFinal, 0, 2) . '+' . substr($abscisaFinal, 2);
            }
            $material = ($modelo->materialLista) ? $modelo->materialLista->Nombre : $modelo->formulaLista->Nombre;
            if ($modelo->materialLista) {
                $tipo = 'Material';
            } else {
                $tipo = 'Formula';
            }
            $unidadMedida = ($modelo->materialLista) ? $modelo->materialLista->unidadMedida : $modelo->formulaLista->unidadMedida;
            $fecha = Carbon::parse($modelo->fechaProgramacion);
            $fechaProgramacionFormateada = $fecha->format('Y/m/d');
            $fechasol = Carbon::parse($modelo->dateCreation);
            $fechasolFormateada = $fechasol->format('Y/m/d h:i:s');
            $nombre = ($modelo->usuario) ? $modelo->usuario->Nombre : '';
            $apellido = ($modelo->usuario) ? $modelo->usuario->Apellido : '';
            $usuario = $nombre . ' ' . $apellido;
            $nombreapro = ($modelo->usuarioaprobador) ? $modelo->usuarioaprobador->Nombre : '';
            $apellidoapro = ($modelo->usuarioaprobador) ? $modelo->usuarioaprobador->Apellido : '';
            $usuarioapro = $nombreapro . ' ' . $apellidoapro;
            $estado = ($modelo->estado) ? $modelo->estado->descripcion_estado : 'Sin Estado';
            $estado_descripcion = ($modelo->estado) ? $modelo->estado->descripcion_estado : 'Sin Estado';
            if ($modelo->estado->id_estados >= 7 && $modelo->estado->id_estados <= 11) {
                $estado = 'POR APROBAR';
            } elseif ($modelo->estado->id_estados == 13) {
                $estado = 'RECHAZADO';
            } elseif ($modelo->estado->id_estados == 14) {
                $estado = 'ANULADO';
            } elseif ($modelo->estado->id_estados == 15) {
                $estado = 'DESPACHADO';
            } else {
                $estado = 'APROBADO';
            }
    
            $viajes=$modelo->transporte;
    
            return [
                'No' => $modelo->id_solicitud_Materiales,
                'Capa' => ($modelo->tipoCapa) ? $modelo->tipoCapa->Descripcion : '',
                'Tramo' => ($modelo->tramo) ? $modelo->tramo->Id_Tramo : '',
                'Hito' => ($modelo->hitos) ? $modelo->hitos->Id_Hitos : '',
                'Destino' => $destino,
                'Abscisa' => $abscisa,
                'Carril' => ($modelo->tipoCarril) ? $modelo->tipoCarril->Carril : '',
                'Calzada' => ($modelo->tipoCalzada) ? $modelo->tipoCalzada->Calzada : '',
                'CarrilDescripcion' => ($modelo->tipoCarril) ? $modelo->tipoCarril->Descripcion : '',
                'CalzadaDescripcion' => ($modelo->tipoCalzada) ? $modelo->tipoCalzada->Descripcion : '',
                'numeroCapa' => $modelo->numeroCapa,
                'Material' => $material,
                'Cantidad' => $modelo->Cantidad,
                'UnidadMedida' => $unidadMedida,
                'Programacion' => $fechaProgramacionFormateada,
                'Planta' => ($modelo->plantas) ? $modelo->plantas->NombrePlanta : '',
                'Solicitante' => $usuario,
                'Aprobador' => $usuarioapro,
                'Nota_solicitante' => $modelo->notaUsuario,
                'Estado_solicitud' => $estado,
                'Tipo' => $tipo,
                'Estado' => $modelo->estado,
                'Nota_aprobador' => $modelo->notaSU,
                'Numero_Capa' => $modelo->numeroCapa,
                'Estado_descripcion' => $estado_descripcion,
                'fecha' => $fechasolFormateada,
                'EquipoId'=>($modelo->transporte) ? $modelo->transporte->equiment_id : '',
                'total'=>($viajes)?$viajes->filter(fn($tr) => $tr->equipo && $tr->equipo->cubicaje != null)->sum(fn($tr) => $tr->equipo->cubicaje ?? 0):0
            ];
        });
    }
}
