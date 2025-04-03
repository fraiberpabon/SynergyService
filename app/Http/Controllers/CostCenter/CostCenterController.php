<?php

namespace App\Http\Controllers\CostCenter;

use App\Http\Controllers\Controller;
use App\Http\interfaces\Vervos;
use App\Models\CostCenter\CostCenter;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CostCenter as CostCenterResource;
use App\Http\Resources\CostCenterCollection;
use App\Http\Controllers\BaseController;
use App\Models\CnfCostCenter;
use Illuminate\Support\Facades\Validator;
use App\Http\trait\CompaniaTrait;

class CostCenterController extends BaseController implements Vervos
{
    /**
     * @param Request $req
     */
    public function post(Request $req) {}

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id) {}

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id) {}

    /**
     * @param Request $request
     */
    public function get(Request $request) {}

    public function getPorProyecto(Request $request, $proyecto) {}


    public function postCentroCosto(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'Codigo' => 'required',
                'Descripcion' => 'required|max:50',
                'Observacion' => 'nullable',
                'UnidadMedida' => 'required|min:3',
                'Grupo' => 'required|min:3',
                'Distribuible' => 'required|min:1',
            ]);
            // Si las validaciones fallan, se retorna un error
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            $proyecto = $this->traitGetProyectoCabecera($request);
            $compania = $this->traitIdEmpresaPorProyecto($request);
            $datos = $request->all();
            $findCentroCosto = CostCenter::where('Codigo', $datos['Codigo'])
                ->where('fk_id_project_Company', $proyecto)
                ->get();
            if ($findCentroCosto->count() > 0) {
                return $this->handleAlert(__('messages.centro_costo_existe'));
            }
            $crearCentroCostoWb = new CostCenter();
            $crearCentroCostoWb->Codigo = $datos['Codigo'];
            $crearCentroCostoWb->Descripcion = $datos['Descripcion'];
            $crearCentroCostoWb->Observacion = $datos['Observacion'];
            $crearCentroCostoWb->UM = $datos['UnidadMedida'];
            $crearCentroCostoWb->Grupo = $datos['Grupo'];
            $crearCentroCostoWb->Distribuible = $datos['Distribuible'];
            $crearCentroCostoWb->Estado = 1;
            $crearCentroCostoWb->created_at = $this->traitGetDateTimeNow();
            $crearCentroCostoWb->fk_user_creador = $this->traitGetIdUsuarioToken($request);
            $crearCentroCostoWb->updated_at = null;
            $crearCentroCostoWb->fk_user_update = null;
            $crearCentroCostoWb->fk_id_project_Company = $proyecto;
            $crearCentroCostoWb->fk_compania = $compania;
            $crearCentroCostoWb->save();
            if ($proyecto == 1) {
                $crearCentroCostoCnf = new CnfCostCenter();
                $crearCentroCostoCnf->COSYNCCODE = $datos['Codigo'];
                $crearCentroCostoCnf->COCEOBSERVATION = $datos['Observacion'];
                $crearCentroCostoCnf->COCENAME = $datos['Descripcion'];
                $crearCentroCostoCnf->COCECREATEDATE = $this->traitGetDateTimeNow();
                $crearCentroCostoCnf->COCEUSERCREATE = 2;
                $crearCentroCostoCnf->COCEENABLED = 1;
                $crearCentroCostoCnf->DISTRIBUTABLE = null;
                $crearCentroCostoCnf->COSYNCCODE2 = null;
                $crearCentroCostoCnf->COCEEQUIVALENT = null;
                $crearCentroCostoCnf->BUSINESSUNIT = null;
                $crearCentroCostoCnf->DISTRIBUTABLE = $datos['Distribuible'];
                $crearCentroCostoCnf->save();
            }
            return $this->handleAlert(__('messages.centro_costo_creado'), true);
        } catch (Exception $e) {
            Log::error('error al crear centro de costo ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }




    public function updateCentroCosto(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'Codigo' => 'required',
                'Descripcion' => 'required|max:50',
                'Observacion' => 'nullable',
                'UnidadMedida' => 'required|min:3',
                'Grupo' => 'required|min:3',
                'Distribuible' => 'required|min:1',
            ]);
            // Si las validaciones fallan, se retorna un error
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            $proyecto = $this->traitGetProyectoCabecera($request);
            $compania = $this->traitIdEmpresaPorProyecto($request);
            $datos = $request->all();
            $actualizarCentroCostoWb = CostCenter::where('Codigo', $datos['Codigo'])
                ->where('fk_id_project_Company', $proyecto)
                ->where('Estado', 1)
                ->get();
            if ($actualizarCentroCostoWb) {
                foreach ($actualizarCentroCostoWb as $actCentroCosto) {
                    $actCentroCosto->Descripcion = $datos['Descripcion'];
                    $actCentroCosto->Observacion = $datos['Observacion'];
                    $actCentroCosto->UM = $datos['UnidadMedida'];
                    $actCentroCosto->Grupo = $datos['Grupo'];
                    $actCentroCosto->Distribuible = $datos['Distribuible'];
                    $actCentroCosto->Estado = 1;
                    $actCentroCosto->fk_user_creador = $this->traitGetIdUsuarioToken($request);
                    $actCentroCosto->updated_at  = $this->traitGetDateTimeNow();
                    $actCentroCosto->fk_user_update = null;
                    $actCentroCosto->fk_id_project_Company = $proyecto;
                    $actCentroCosto->fk_compania = $compania;
                    $actCentroCosto->save();
                }
            }
            if ($proyecto == 1) {
                $actualizarCentroCostoCnf = CnfCostCenter::where('COSYNCCODE', $datos['Codigo'])
                    ->get();
                if ($actualizarCentroCostoCnf) {
                    foreach ($actualizarCentroCostoCnf as $actCentroCostoCnf) {
                        $actCentroCostoCnf->COCEOBSERVATION = $datos['Observacion'];
                        $actCentroCostoCnf->COCENAME = $datos['Descripcion'];
                        $actCentroCostoCnf->COCECREATEDATE = $this->traitGetDateTimeNow();
                        $actCentroCostoCnf->COCEUSERCREATE = 2;
                        $actCentroCostoCnf->COCEENABLED = 1;
                        $actCentroCostoCnf->DISTRIBUTABLE = null;
                        $actCentroCostoCnf->COSYNCCODE2 = null;
                        $actCentroCostoCnf->COCEEQUIVALENT = null;
                        $actCentroCostoCnf->BUSINESSUNIT = null;
                        $actCentroCostoCnf->DISTRIBUTABLE = $datos['Distribuible'];
                        $actCentroCostoCnf->save();
                    }
                }
            }
            return $this->handleAlert(__('messages.centro_costo_creado'), true);
        } catch (Exception $e) {
            Log::error('error al actualizar centro de costo ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }





    /**
     * Funcion para obtener los centros de costo
     */
    public function getCostCenterMobile(Request $request)
    {
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $query = CostCenter::where('Estado', 1)
                ->where('fk_id_project_Company', $proyecto)
                ->orderBy('id', 'desc');

            // $result = $query->get();
            $result = $query->paginate(100);
            //$result = $query->paginate(100);
            // return $this->handleResponse(
            //     $request,
            //     new CostCenterCollection($result),
            //     __('messages.consultado')
            // );

            return $this->handleResponse(
                $request,
                $this->CentrosCostoToArray($result),
                __('messages.consultado')
            );
        } catch (Exception $e) {
            Log::error('error al obtener centros de costo mobile ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }




    /***
     * Funcion para anular un centro de costo en el caso que este centro de costo tenga varios 
     * registros en la tabla cnf_cost_center se anularan todos los registros y en la tabla de wb_costos
     */
    public function AnularCentroCosto(Request $request)
    {
        try {
            $fecha_anulacion = $this->traitGetDateTimeNow();
            $fk_usuario_anulacion = $this->traitGetIdUsuarioToken($request);
            $codigo_centro_costo = $request->input('Codigo');
            if ($codigo_centro_costo == null) {
                return $this->handleAlert('el campo es requerido');
            }

            $anularCentroCosto = CostCenter::where('Codigo', $codigo_centro_costo)->get();
            if ($anularCentroCosto == null) {
                return $this->handleAlert(__('messages.centro_costo_no_existe'));
            }
            foreach ($anularCentroCosto as $centroCosto) {
                $centroCosto->fk_user_update = $fk_usuario_anulacion;
                $centroCosto->updated_at = $fecha_anulacion;
                $centroCosto->Estado = 0;
                $centroCosto->save();
            }
            $ANULARCNFCOSTCENTER = CnfCostCenter::where('COSYNCCODE', $codigo_centro_costo)->get();
            if ($ANULARCNFCOSTCENTER == null) {
                return $this->handleAlert(__('messages.centro_costo_no_existe'));
            }
            foreach ($ANULARCNFCOSTCENTER as $centroCostoView) {
                $centroCostoView->COCEENABLED = 0;
                $centroCostoView->save();
            }
            return $this->handleAlert(__('messages.centro_costo_anulado'), true);
        } catch (\Exception $e) {
            Log::error('error al anular centro de costo ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }
}
