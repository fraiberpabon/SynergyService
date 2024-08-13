<?php

namespace App\Http\Controllers;

use App\Models\WbEquipo;
use App\Models\WbConfiguraciones;
use Illuminate\Http\Request;


class QrCodeController extends BaseController
{


    /**
     * Funcion que me lista el qr
     * primero validamos el codigo que recoge la informacion del equipo
     * luego recorremos codigos
     * luego consultamos equipos en la cual me voy a traer los parametros necesarios para
     * mostrar el qr por proyectos
     */
    public function index($codigos, $proy = null, Request $req)
    {
        try {
            if ($proy == null || !is_numeric($proy)) {
                return $this->handleError('Parametros incorrecto');
            }
            $codigo = json_decode($codigos);
            foreach ($codigo as $value) {
                $valor[] = $value->codigo;
            }
            $equipos = WbEquipo::select(
                'equiment_id',
                'placa',
                'compañia.nombreCompañia AS nombreCompania',
                'Wb_equipos.fk_id_project_Company'
            )
                ->join('compañia', 'compañia.id_compañia', '=', 'Wb_equipos.fk_compania')
                ->whereIn('Wb_equipos.equiment_id', $valor)
                ->where('Wb_equipos.fk_id_project_Company', $proy)
                ->get();
            return view('qrcode', ['equipos' => $equipos]);
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener el proyecto', $e->getMessage());
        }
    }

    /**
     * Funcion que imprime el laboratorio de solicitud de muestra por qr
     * @param $cantidad
     */
    public function laboratorio_solicitud_muestras($cantidad)
    {

        $longitud = 8;

        if (!is_numeric($cantidad)) {
            echo 'no es numero';
            return;
        }

        $ultimo = WbConfiguraciones::first();
        if ($ultimo == null) {
            echo 'no encontrado';
            return;
        }

        $consecutivo = $ultimo->consecutivo_etiqueta_muestra_laboratorio + 1;

        for ($i = 0; $i < $cantidad; $i++) {
            $valor[] = $consecutivo;
            $consecutivo += 1;
        }
        /* foreach ($codigo as $value) {
            $valor[] = $value->codigo;
        }

        $equipo = ts_Equipement::leftjoin('SubcontractorTrans', 'Equipments.ContractID', '=', 'SubcontractorTrans.ContractID')
            ->leftjoin('Subcontractor', 'SubcontractorTrans.SubcontractorID', '=', 'Subcontractor.SubcontractorID')
            ->whereIn('Equipments.EquipmentID', $valor)
            ->get(); */

        if ($valor) {
            foreach ($valor as $value) {
                $numero = $this->rellenarConCerosIzquierda($value, $longitud);
                $data[] = $numero;
            }
        }

        $ultimo->consecutivo_etiqueta_muestra_laboratorio = $consecutivo - 1;
        $ultimo->save();
        //print_r($valor);
        return view('qrcode_lab', [
            'equipos' => $data,
        ]);
    }
    /**
     * Funcion que rellena cero a la izquierda
     */
    function rellenarConCerosIzquierda($numero, $longitud)
    {

        $numero_con_cero = str_pad($numero, $longitud, '0', STR_PAD_LEFT);

        return $numero_con_cero;
    }
}
