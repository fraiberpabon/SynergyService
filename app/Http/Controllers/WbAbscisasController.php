<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbHitosAbcisas;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WbAbscisasController extends BaseController implements Vervos
{

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {
        // TODO: Implement post() method
    }

    /**
     * @param Request $req
     * @param $id
     * @return void
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $id
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }


    /**
     * Esta funcion recibe $hito -> hito, $proyecto -> id_project_company, $abscisa1 -> abscisa de comparacion 1, $abscisa2 -> abscisa de comparacion 2 (opcional)
     * Busca las coordenadas en base de datos de dichas abscisas saca un promedio de las coordenadas de las mismas
     * al ginal devuelve null si no encontro coordenadas o la ubicacion promedio de las abscisas
     * 
     * @param $hito
     * @param $proyecto
     * @param $abscisa1
     * @param $abscisa2
     * 
     * @return string
     */
    public function getPromedioEntreAbscisas($hito, $proyecto, $abscisa1, $abscisa2 = null)
    {
        try {
            $consulta = WbHitosAbcisas::select('fk_id_Hitos', 'Inicio', 'Fin', 'coordenada_inicial', 'coordenada_final')
                ->where('fk_id_Hitos', $hito)
                ->where('fk_id_project_Company', $proyecto);
            if ($abscisa2 != null) {
                $consulta = $consulta->where(function ($consulta) use ($abscisa1, $abscisa2) {
                    $consulta->orWhere(function ($consulta) use ($abscisa1) {
                        $consulta->where('Inicio', '<=', $abscisa1)
                            ->where('Fin', '>=', $abscisa1);
                    })->orWhere(function ($consulta) use ($abscisa2) {
                        $consulta->where('Inicio', '<=', $abscisa2)
                            ->where('Fin', '>=', $abscisa2);
                    });
                });
            } else {
                $consulta = $consulta->where('Inicio', '<=', $abscisa1)
                    ->where('Fin', '>=', $abscisa1);
            }

            $consulta = $consulta->where('coordenada_inicial', '<>', null)
                ->where('coordenada_final', '<>', null)
                ->where('estado', 'A')
                ->get();



            if ($consulta->count() === 0) {
                return null;
            }

            /* Sumar todas las latitudes  */
            $coordenadas = collect();
            foreach ($consulta as $key) {
                $item = collect();
                $separar = explode(';', $key->coordenada_inicial);
                list($latitud, $longitud) = $separar;
                $item->put('latitud', $latitud);
                $item->put('longitud', $longitud);
                $coordenadas->push($item);

                $separar = explode(';', $key->coordenada_final);
                list($latitud, $longitud) = $separar;
                $item->put('latitud', $latitud);
                $item->put('longitud', $longitud);
                $coordenadas->push($item);
            }

            //return $average = collect([3.4, 39, 9.73860778689557, -74.8697540709521, 4])->avg();

            $totalLatitud = $coordenadas->avg('latitud');
            $totalLongitud = $coordenadas->avg('longitud');


            return $totalLatitud . ';' . $totalLongitud;
        } catch (\Throwable $th) {
            return null;
        }
    }
}