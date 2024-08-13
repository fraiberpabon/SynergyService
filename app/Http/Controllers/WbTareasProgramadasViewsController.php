<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Laboratorio\WbEnsayosController;
use App\Http\Controllers\Laboratorio\WbTipoControlController;

class WbTareasProgramadasViewsController extends BaseController
{
    private function laboratorioSolicitudMuestraAutomatico(Request $req)
    {
        $materiales = new WbMaterialListaController();
        $programacionTarea = new WbProgramadorTareasController();
        $ensayos = new WbEnsayosController();
        $tipoControl = new WbTipoControlController();


        return view('tarea-programada/lab_solicitud_muestras', [
            'materiales' => $materiales->getPorProyecto($req, $this->traitGetProyectoCabecera($req)),
            'programacionTarea' => $programacionTarea->getPorProyecto($req, $this->traitGetProyectoCabecera($req)),
            'ensayos' => $ensayos->getOrdenadoASCPorProyecto($req, $this->traitGetProyectoCabecera($req)),
            'tipoControl' => $tipoControl->getPorProyecto($req, $this->traitGetProyectoCabecera($req))
        ])->render();
    }


    public function obtenerVista(Request $req)
    {
        $modulo = $req->modulo;
        $tarea = $req->tarea;
        if ($modulo === null && $tarea === null) {
            return $this->handleAlert(__('messages.faltan_parametros'), false);
        }

        $vista = null;
        if ($modulo == 1 && $tarea == 1) { //Se llama a solicitud de muestra en automatico
            $vista = $this->laboratorioSolicitudMuestraAutomatico($req);
        }

        return $this->handleAlert($vista, true);
    }
}
