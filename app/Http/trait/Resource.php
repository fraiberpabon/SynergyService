<?php

namespace App\Http\trait;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

trait Resource
{
    /**
     * Transforma el recurso en un array.
     */
    public function toArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->toModel($data);
        });
    }

    public function toModel($modelo): array
    {
        return [];
    }

    public function solicitudLiberacionFirmaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->solicitudLiberacionFirmaToModel($data);
        });
    }

    public function solicitudLiberacionFirmaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_solicitudes_liberaciones_firmas,
            'solicitudLiberacion' => $modelo->fk_id_solicitudes_liberaciones,
            'area' => $modelo->fk_id_area,
            'usuario' => $modelo->fk_id_usuario,
            'estado' => $modelo->estado,
            'fechaCreacion' => $modelo->dateCreate,
            'idArea' =>  $modelo->area ?  $modelo->area->id_area : '',
            'nombreArea' => $modelo->area ? $modelo->area->Area : '',
            'nota' => $modelo->nota,
            'panoramica' => $modelo->panoramica,
        ];
    }

    public function actividadToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->actividadToModel($data);
        });
    }

    /**
     * @return array
     *               Formato para la tabla Actividades
     */
    public function actividadToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_Actividad,
            'nombre' => $modelo->Actividad,
            'descripcion' => $modelo->descripcion,
        ];
    }

    public function wbAsfaltFormulaAsgignToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbAsfaltFormulaAsgignToModel($data);
        });
    }

    public function wbAsfaltFormulaAsgignToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_asfalt_asig,
            'formulaAsfalto' => $modelo->fk_asfal_formula,
            'objectFormula' => $modelo->objectFormula,
            'objectPlanta' => $modelo->objectPlanta,
            'planta' => $modelo->fk_planta,
            'estado' => $modelo->estado == 0 ? 'Inactivo' : 'Activo',
        ];
    }

    public function wbAsfaltFormulaAsgignToArray2($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbAsfaltFormulaAsgignToModel2($data);
        });
    }

    public function wbAsfaltFormulaAsgignToModel2($modelo): array
    {
        return [
            'identificador' => $modelo->id_asfalt_asig,
            'formulaAsfalto' => $modelo->fk_asfal_formula,
            'NombrePlanta' => $modelo->NombrePlanta,
            'asfalFormula' => $modelo->asfalt_formula,
            'planta' => $modelo->fk_planta,
            'estado' => $modelo->estado,
        ];
    }

    public function wbFormulaListaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbFormulaListaToModel($data);
        });
    }

    public function wbFormulaListaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_formula_lista,
            'nombre' => $modelo->Nombre,
            'formulaDescripcion' => $modelo->formulaDescripcion,
            'estado' => $modelo->Estado,
            'fecha' => $modelo->dateCreate,
            'unidadMedida' => $modelo->unidadMedida,
            'usuario' => $modelo->userCreator,
            'listaFormulaCapa' => $modelo->listaFormulaCapa,
            'listaFormulaCentroProduccion' => $modelo->listaFormulaCentroProduccion,
        ];
    }

    public function wbFormulaCentroProduccionToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbFormulaCentroProduccionToModel($data);
        });
    }

    public function wbFormulaCentroProduccionToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_formula_centroProduccion,
            'formulaLista' => $modelo->fk_id_formula_lista,
            'planta' => $modelo->fk_id_planta,
            'estado' => $modelo->Estado,
            'fecha' => $modelo->dateCreate,
            'usuario' => $modelo->userCreator,
            'codigo' => $modelo->codigoFormulaCdp,
            'nombrePlanta' => $modelo->NombrePlanta,
            'location' => $modelo->fk_LocationID,
            'objectUsuPlanta' => $modelo->objectUsuPlanta,
        ];
    }

    public function wbFormulaCapaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbFormulaCapaToModel($data);
        });
    }

    public function wbFormulaCapaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_formula_capa,
            'tipoCapa' => $modelo->fk_id_tipo_capa,
            'formulaLista' => $modelo->fk_id_formula_lista,
            'estado' => $modelo->Estado,
            'fecha' => $modelo->dateCreate,
            'usuario' => $modelo->userCreator,
            'objectTipoCapa' => $modelo->objectTipoCapa,
            'nombre' => $modelo->Nombre,
            'unidadMedida' => $modelo->unidadMedida,
        ];
    }

    public function wbMaterialCentroProduccionToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbMaterialCentroProduccionToModel($data);
        });
    }

    public function wbMaterialCentroProduccionToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_material_centroProduccion,
            'materialLista' => $modelo->fk_id_material_lista,
            'planta' => $modelo->fk_id_planta,
            'estado' => $modelo->Estado,
            'fecha' => $modelo->dateCreate,
            'objectUsuPlanta' => $modelo->objectUsuPlanta,
            'objectMaterialLista' => $modelo->objectMaterialLista,
        ];
    }

    public function wbMaterialFormulaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbMaterialFormulaToModel($data);
        });
    }

    public function wbMaterialFormulaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_material_formula,
            'formulaCentroProduccion' => $modelo->fk_formula_CentroProduccion,
            'materialCentroProduccion' => $modelo->fk_material_CentroProduccion,
            'porcentaje' => $modelo->Porcentaje,
            'estado' => $modelo->Estado,
            'fecha' => $modelo->dateCreate,
            'usuario' => $modelo->userCreator,
            'codigoFormula' => $modelo->fk_codigoFormulaCdp,
            'objectFormulaCentroProduccion' => $modelo->objectFormulaCentroProduccion,
            'objectMaterialCentroProduccion' => $modelo->objectMaterialCentroProduccion,
            'objectMaterialLista' => $modelo->objectMaterialLista,
            'objectUsuPlanta' => $modelo->objectUsuPlanta,
        ];
    }

    public function wbMaterialListaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbMaterialListaToModel($data);
        });
    }

    public function wbMaterialListaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_material_lista,
            'material' => $modelo->Nombre,
            'descripcion' => $modelo->Descripcion,
            'unidadMedida' => $modelo->unidadMedida,
            'tipo_id' => $modelo->Estado,
            'tipo' => $modelo->dateCreate,
            'solicitable' => $modelo->fk_id_material_tipo,
            'proyecto' => $modelo->Solicitable,
        ];
    }

    public function wbMaterialCapaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbMaterialCapaToModel($data);
        });
    }

    public function wbMaterialCapaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_material_capa,
            'tipoCapa' => $modelo->fk_id_tipo_capa,
            'materialLista' => $modelo->fk_id_material_lista,
            'estado' => $modelo->Estado,
            'fechaCreacion' => $modelo->dateCreate,
            'usuario' => $modelo->userCreator,
            'objectTipoCapa' => $modelo->objectTipoCapa,
        ];
    }

    public function wbCentroProduccionHitoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbCentroProduccionHitoToModel($data);
        });
    }

    public function wbCentroProduccionHitoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_centroProduccion_hito,
            'plantas' => $modelo->fk_id_planta,
            'hito' => $modelo->fk_id_Hito,
            'estado' => $modelo->Estado,
            'fechaCreacion' => $modelo->dateCreate,
            'fechaCierre' => $modelo->dateClose,
            'fechaActualizacion' => $modelo->dateUpdate,
            'usuario' => $modelo->userCreator,
            'objectHito' => $modelo->objectHito,
        ];
    }

    public function wbMaterialTipoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbMaterialTipoToModel($data);
        });
    }

    public function wbMaterialTipoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_material_tipo,
            'tipoDescripcion' => $modelo->tipoDescripcion,
            'compuesto' => $modelo->Compuesto,
            'estado' => $modelo->Estado,
            'fechaCreacion' => $modelo->dateCreate,
            'usuario' => $modelo->userCreator,
        ];
    }

    public function wbEstructPerfilFrimasToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbEstructPerfilFrimasToModel($data);
        });
    }

    public function wbEstructPerfilFrimasToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_estruc_perfil,
            'nombre' => $modelo->nombre_perfil,
            'descripcion' => $modelo->descripcion,
        ];
    }

    public function wbEstructFirmaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbEstructFirmaToModel($data);
        });
    }

    public function wbEstructFirmaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_estruc_firma,
            'estructPerfil' => $modelo->fk_estruc_perfil,
            'area' => $modelo->area,
            'firma' => $modelo->firma,
            'estado' => $modelo->estado,
        ];
    }

    public function wbEstructCriterioToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbEstructCriterioToModel($data);
        });
    }

    public function wbEstructCriterioToModel($modelo): array
    {
        switch ($modelo->operacion) {
            case '=':
                $operador = 'Igual';
                break;
            case '<':
                $operador = 'Menor';
                break;
            case '<=':
                $operador = 'Menor o Igual';
                break;
            case '>':
                $operador = 'Mayor';
                break;
            case '>=':
                $operador = 'Mayor o Igual';
                break;
            case '!=':
                $operador = 'Diferente';
                break;
            case '<>':
                $operador = 'Entre';
                break;
        }

        return [
            'identificador' => $modelo->id_estruc_criterio,
            'estructConfig' => $modelo->fk_estruc_config,
            'nombreCriterio' => $modelo->nombre_criterio,
            'criterio1' => $modelo->criterio1,
            'operacion' => $operador,
            'criterio2' => $modelo->criterio2,
            'estado' => $modelo->estado,
        ];
    }

    public function wbEstructConfigToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbEstructConfigToModel($data);
        });
    }

    public function wbEstructConfigToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_estruc_config,
            'estrucPerfil' => $modelo->fk_estruc_perfil,
            'empresa' => $modelo->empresa,
            'nombre' => $modelo->nombre_config,
            'estado' => $modelo->estado,
        ];
    }

    public function wbEstructConfigAsignToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbEstructConfigAsignToModel($data);
        });
    }

    public function wbEstructConfigAsignToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_Estruc_Config_Asign,
            'estructConfig' => $modelo->fk_estruc_config,
            'estructTipo' => $modelo->fk_estruc_tipo,
            'objectEstructConfig' => $modelo->objectEstructConfig,
            'objectEstructTipo' => $modelo->objectEstructTipo,
        ];
    }

    public function equiposToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->equipoToModel($data);
        });
    }

    public function equipoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id,
            'equipo' => $modelo->equiment_id,
            'descripcion' => $modelo->descripcion,
            'cubicaje' => $modelo->cubicaje,
            'marca' => $modelo->marca,
            'modelo' => $modelo->modelo,
            'placa' => $modelo->placa,
            'dueno' => $modelo->dueno,
            'estado' => $modelo->estado,
            'peso' => $modelo->vehiculos_pesos ? $modelo->vehiculos_pesos->peso : '',
            'compania' => $modelo->fk_compania,
            'compania_name' => $modelo->compania ? $modelo->compania->nombreCompañia : '',
            'nombreTipoEquipo' => $modelo->tipo_equipo ? $modelo->tipo_equipo->nombre : '',
            'tipoEquipo' => $modelo->fk_id_tipo_equipo,
            'tipocontrato' => $modelo->tipocontrato,
            'proyecto' => $modelo->fk_id_project_Company,
        ];
    }

    public function tipoEquipoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->tipoEquipoToModel($data);
        });
    }

    public function tipoEquipoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_tipo_equipo,
            'nombre' => $modelo->nombre,
        ];
    }

    public function equimentToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->equimentToModel($data);
        });
    }

    public function equimentToModel($modelo): array
    {
        return [
            'EQUIPO' => $modelo->EquipmentID,
            'DESCRIPCION' => $modelo->Description,
            'MARCA' => $modelo->Make,
            'MODELO' => $modelo->Model,
            'SERIAL' => $modelo->SerialNumber,
            'TIPO' => $modelo->Owned,
            'ESTADO' => $modelo->Status,
            'M3' => $modelo->Payload,
            'CONTRATO' => $modelo->SubcontractorDesc,
        ];
    }

    public function syncEmpleadoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->syncEmpleadoToModel($data);
        });
    }

    public function syncEmpleadoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->EmployeeID,
            'tipo' => $modelo->EmployeeType,
            'tipoOdeb' => $modelo->EmployeeTypeOdeb,
            'nombres' => $modelo->FirstName,
            'apellidos' => $modelo->LastName,
            'fechaCreacion' => $modelo->DateCreated,
            'estado' => $modelo->Status,
        ];
    }

    public function costCodeToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->costCodeToModel($data);
        });
    }

    public function costCodeToModel($modelo): array
    {
        return [
            'identificador' => $modelo->CostCode,
            'tipo' => $modelo->CostType,
            'desc' => $modelo->CostDesc,
            'fecha' => $modelo->DateCreated,
            'actividad' => $modelo->Activity,
            'grupo' => $modelo->Group,
            'subGrupo' => $modelo->SubGroup,
            'estado' => $modelo->Status,
            'idTrabajo' => $modelo->xJobIdxx,
            'timberlineCC' => $modelo->TimberlineCC,
            'workCond' => $modelo->WorkCond,
            'workCode' => $modelo->WorkCode,
            'um' => $modelo->UM,
            'cycleCC' => $modelo->CycleCC,
        ];
    }

    public function cnfCostControlToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->cnfCostControlToModel($data);
        });
    }

    public function cnfCostControlToModel($modelo): array
    {
        return [
            'identificacion' => $modelo->COCEIDENTIFICATION,
            'nombre' => $modelo->COCENAME,
            'observacion' => $modelo->COCEOBSERVATION,
            'costIdFather' => $modelo->COCECOSTIDFATHER,
            'habilitado' => $modelo->COCEENABLED,
            'fecha' => $modelo->COCECREATEDATE,
            'usuario' => $modelo->COCEUSERCREATE,
            'codigo' => $modelo->COSYNCCODE,
            'codigo2' => $modelo->COSYNCCODE2,
            'equivalente' => $modelo->COCEEQUIVALENT,
            'unidadNegocio' => $modelo->BUSINESSUNIT,
            'distribuible' => $modelo->DISTRIBUTABLE,
        ];
    }

    public function plantaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->plantaToModel($data);
        });
    }

    public function plantaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id,
            'planta' => $modelo->planta,
            'estado' => $modelo->estado,
        ];
    }

    public function wbAsfaltFormulaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbAsfaltFormulaToModel($data);
        });
    }

    public function wbAsfaltFormulaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_asfal_formula,
            'formula' => $modelo->asfalt_formula,
            'estado' => ($modelo->estado == 0 ? 'Inactivo' : 'Activo'),
        ];
    }

    public function hallazgoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->hallazgoToModel($data);
        });
    }

    public function hallazgoToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_hallazgo'],
            'nombre' => $modelo['nombre'],
            'necesitaDescripcion' => $modelo['necesita_descripcion'],
        ];
    }

    public function locationToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->locationToModel($data);
        });
    }

    public function locationToModel($modelo): array
    {
        return [
            'identificador' => $modelo->ID,
            'locationId' => $modelo->LocationID,
            'LocationDesc' => $modelo->LocationDesc,
            'hasTimeKeeper' => $modelo->hasTimeKeeper,
            'status' => $modelo->Status,
            'fecha' => $modelo->DateCreated,
        ];
    }

    public function solicitudMaterialAppToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->solicitudMaterialAppToModel($data);
        });
    }

    public function solicitudMaterialAppToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_solicitud_Materiales'],
            'usuario' => $modelo['fk_id_usuarios'],
            'descripcion' => $modelo['Descripcion'],
            'tramo' => $modelo['fk_id_tramo'],
            'hito' => $modelo['fk_id_hito'],
            'abscisaInicial' => $modelo['abscisaInicialReferencia'],
            'abscisaFinal' => $modelo['abscisaFinalReferencia'],
            'inicialfinal' => $modelo['inicialfinal'],
            'carril' => $modelo['Carril'],
            'calzada' => $modelo['Calzada'],
            'material' => $modelo['fk_id_material'],
            'nombreMaterial' => $modelo['Nombre'],
            'unidadMedida' => $modelo['unidadMedida'],
            'fechaProgramacion' => $modelo['fechaProgramacion'],
            'cantidad' => $modelo['Cantidad'],
            'numeroCapa' => $modelo['numeroCapa'],
            'notaUsuario' => $modelo['notaUsuario'],
            'notaSU' => $modelo['notaSU'],
            'notaCenProduccion' => $modelo['notaCenProduccion'],
            'estado' => $modelo['fk_id_estados'],
            'descripcionEstado' => $modelo['descripcion_estado'],
            'fechaCreacion' => $modelo['dateCreation'],
            'formula' => $modelo['fk_id_formula'],
            'nombrePlanta' => $modelo['NombrePlanta'],
            'nombreFormula' => $modelo['nombreFormula'],
            'plantadestino' => $modelo['plantadestino'],
        ];
    }

    public function solicitudMaterialToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->solicitudMaterialToModel($data);
        });
    }

    public function solicitudMaterialToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_solicitud_Materiales'],
            'usuario' => $modelo['fk_id_usuarios'],
            'tipoCapa' => $modelo['fk_id_tipo_capa'],
            'tramo' => $modelo['fk_id_tramo'],
            'hito' => $modelo['fk_id_hito'],
            'abscisaInicial' => $modelo['abscisaInicialReferencia'],
            'abscisaFinal' => $modelo['abscisaFinalReferencia'],
            'tipoCarril' => $modelo['fk_id_tipo_carril'],
            'tipoCalzada' => $modelo['fk_id_tipo_calzada'],
            'material' => $modelo['fk_id_material'],
            'fechaProgramacion' => $modelo['fechaProgramacion'],
            'cantidad' => $modelo['Cantidad'],
            'numeroCapa' => $modelo['numeroCapa'],
            'notaUsuario' => $modelo['notaUsuario'],
            'notaSu' => $modelo['notaSU'],
            'notaCentroProduccion' => $modelo['notaCenProduccion'],
            'estado' => $modelo['fk_id_estados'],
            'fechaCreaccion' => $modelo['dateCreation'],
            'formula' => $modelo['fk_id_formula'],
            'planta' => $modelo['fk_id_planta'],
            'plantaReasignada' => $modelo['fk_id_plantaReasig'],
            'usaurioActualizo' => $modelo['fk_id_usuarios_update'],
            'cantidadReal' => $modelo['cantidad_real'],
            'fechaCierre' => $modelo['fecha_cierre'],
            'notaCierre' => $modelo['nota_cierre'],
            'objectTipoCapa' => $modelo['objectTipoCapa'],
            'objectTipoCarril' => $modelo['objectTipoCarril'],
            'objectTipoCalzada' => $modelo['objectTipoCalzada'],
            'objectMaterialLista' => $modelo['objectMaterialLista'],
            'objectEstado' => $modelo['objectEstado'],
            'objectUsuPlanta' => $modelo['objectUsuPlanta'],
            'objectFormulaLista' => $modelo['objectFormulaLista'],
            'objectUsuario' => $modelo['objectUsuario'],
        ];
    }

    // posiblemente obsoleto
    public function configuracionesToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->configuracionesToModel($data);
        });
    }

    public function configuracionesToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id'],
            'porcentajeConcreto' => $modelo['porcentaje_concreto'],
            'cantidadMaximaDespacharConcreto' => $modelo['cantidad_maxima_despachar_concreto'],
            'cantidadMaximaDespacharAsfalto' => $modelo['cantidad_maxima_despachar_asfalto'],
        ];
    }

    // hasta aqui
    public function configuracionToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->configuracionToModel($data);
        });
    }

    public function configuracionToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_configuracion'],
            'configuracion' => $modelo['nombre'],
            'valor' => $modelo['valor'],
        ];
    }

    public function tipoFormatoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->tipoFormatoToModel($data);
        });
    }

    public function tipoFormatoToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_tipo_formato'],
            'nombre' => $modelo['nombre'],
        ];
    }

    public function plantillaReporteToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->areaToModel($data);
        });
    }

    public function plantillaReporteToModel($modelo): array
    {
        return [
            'identificaodr' => $modelo['id'],
            'nombre' => $modelo['nombre'],
            'url' => $modelo['url'],
            'tipoFormato' => $modelo['fk_tipo_formato'],
            'estado' => $modelo['estado'],
        ];
    }

    public function areaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->areaToModel($data);
        });
    }

    public function areaToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_area'],
            'nombre' => $modelo['Area'],
            'estado' => $modelo['estado'],
        ];
    }

    public function informeHallazgoHasHallazgoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->informeHallazgoHasHallazgoToModel($data);
        });
    }

    public function informeHallazgoHasHallazgoToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_informe_campo_has_hallazgo'],
            'informeCampo' => $modelo['fk_id_informe_campo'],
            'hallazgo' => $modelo['fk_id_hallazgo'],
            'descripcionOtros' => $modelo['descripcion_otros'],
            'nombreHallazgo' => $modelo['nombreHallazgo'],
        ];
    }

    public function rutaNAcionalToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->rutaNacionalToModel($data);
        });
    }

    public function rutaNacionalToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_ruta_nacional'],
            'codigo' => $modelo['codigo'],
            'pkInicial' => $modelo['pk_inicial'],
            'pkFinal' => $modelo['pk_final'],
            'nombre' => $modelo['nombre'],
            'estado' => $modelo['estado'],
        ];
    }

    public function informeCampoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->informeCampoToModel($data);
        });
    }

    public function informeCampoToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_informe_campo'],
            'idProyecto' => $modelo['id_proyecto'],
            'fechaRegistroDispositivo' => $modelo['fecha_registro_dispositivo'],
            'fechaRegistro' => $modelo['fecha_registro'],
            'tipoCalzada' => $modelo['fk_id_tipo_calzada'],
            'rutaNacional' => $modelo['fk_id_ruta_nacional'],
            'observacion' => $modelo['observacion'],
            'usuario' => $modelo['fk_id_usuarios'],
            'fotoUno' => $modelo['foto_uno'],
            'fotoDos' => $modelo['foto_dos'],
            'fotoTres' => $modelo['foto_tres'],
            'fotoCuatro' => $modelo['foto_cuatro'],
            'fotoCinco' => $modelo['foto_cinco'],
            'fotoSeis' => $modelo['foto_seis'],
            'ubicacionHallazgo' => $modelo['ubicacion_hallazgo'],
            'codigoRuta' => $modelo['codigoRuta'] != null ? $modelo['tipoRuta']['codigo'] : '',
            'pkInicialRuta' => $modelo['tipoRuta'] != null ? $modelo['tipoRuta']['pk_inicial'] : '',
            'pkFinalRuta' => $modelo['tipoRuta'] != null ? $modelo['tipoRuta']['pk_final'] : '',
            'nombreRuta' => $modelo['tipoRuta'] != null ? $modelo['tipoRuta']['nombre'] : '',
            'nombreCalzada' => $modelo['tipoCalzada'] != null ? $modelo['tipoCalzada']['Calzada'] : '',
            'descripcionCalzada' => $modelo['tipoCalzada'] != null ? $modelo['tipoCalzada']['Descripcion'] : '',
            'nombreUsuario' => $modelo['tipoUsuario'] != null ? $modelo['tipoUsuario']['Nombre'].' '.$modelo['tipoUsuario']['Apellido'] : '',
            'nombreEstado' => $modelo['tipoEstado'] != null ? $modelo['tipoEstado']['descripcion_estado'] : '',
            'latitud' => $modelo['latitud'],
            'longitud' => $modelo['longitud'],
            'tipoHallazgos' => $modelo['tipoHallazgo'],
            'FechaCierre' => $modelo['fecha_cierre'],
            'ObservacionesCierre' => $modelo['observaciones_cierre'],
            'CerradoPor' => $modelo['tipoUsuarioAct'] != null ? $modelo['tipoUsuarioAct']['Nombre'].' '.$modelo['tipoUsuarioAct']['Apellido'] : '',
            'foto_cierre1' => $modelo['foto_cierre1'],
            'foto_cierre2' => $modelo['foto_cierre2'],
        ];
    }

    public function informeCampoUpdateToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_informe_campo'],
            'nombreEstado' => $modelo['tipoEstado'] != null ? $modelo['tipoEstado']['descripcion_estado'] : '',
            'fecha_cierre' => $modelo['fecha_cierre'],
            'fk_user_update' => $modelo['fk_user_update'],
            'observaciones_cierre' => $modelo['observaciones_cierre'],
            'foto_cierre1' => $modelo['foto_cierre1'],
            'foto_cierre2' => $modelo['foto_cierre2'],
            'updated_at' => $modelo['updated_at'],
            'fecha_registro_dispositivo' => $modelo['fecha_registro_dispositivo'],
        ];
    }

    public function asignacionToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->asignacionToModel($data);
        });
    }

    public function asignacionToModel($modelo): array
    {
        return [
            'area' => $modelo['fk_area'],
            'actividad' => $modelo['fk_actividad'],
            'objectArea' => $modelo['objectArea'],
            'objectActividad' => $modelo['objectActividad'],
        ];
    }

    public function companiaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->companiaToModel($data);
        });
    }

    public function companiaToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_compañia'],
            'nombre' => $modelo['nombreCompañia'],
            'ubicacion' => $modelo['ubicacion'],
            'logo' => $modelo['logo'],
            'numeroIdentificacion' => $modelo['numero_identificacion'],
        ];
    }

    public function estadoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->estadoToModel($data);
        });
    }

    public function estadoToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_estados'],
            'nombre' => $modelo['descripcion_estado'],
        ];
    }

    public function itemTransportePanelToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->itemTransportePanelToModel($data);
        });
    }

    public function itemTransportePanelToModel($modelo): array
    {
        return [
            'identificador' => $modelo->TXCounter,
            'equipo' => $modelo->EquipmentID,
            'cedula' => '',
            'conductor' => '',
            'peso2' => '',
            'provedor' => $modelo['provedor'],
            'destino' => $modelo['destino'],
            'producto' => $modelo['producto'],
            'peso1' => $modelo['peso1'],
            'peso3' => $modelo['peso3'],
            'quien' => $modelo['quien'],
            'tipo' => $modelo['tipo'],
            'fecha' => $modelo['fecha'],
            'baucher' => $modelo['Vale'],
            'placa' => $modelo['placa'],
        ];
    }

    public function estructTiposToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->estructTipostoModel($data);
        });
    }

    public function estructTipostoModel($modelo): array
    {
        return [
            'identificador' => $modelo->id,
            'tipo' => $modelo->TIP_1,
            'descripcion' => $modelo->TIPO_DE_ESTRUCTURA,
            'actividad' => $modelo->actividad,
        ];
    }

    public function estructuraToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->estructuraToModel($data);
        });
    }

    public function estructuraToModel($modelo): array
    {
        return [
            'identificador' => $modelo['N'],
            'tramo' => $modelo['TRAMO'],
            'hito' => $modelo['HITO_OTRO_SI_10'],
            'obra' => $modelo['OBRA'],
            'nomenclatura' => $modelo['NOMENCLATURA'],
            'abscisa' => $modelo['ABSCISA'],
            'diametro' => $modelo['DIAMETRO'],
            'celdas' => $modelo['CELDAS'],
            'wbs' => $modelo['WBS'],
            'usuarioTerminar' => $modelo['usuario_terminar'],
            'fechaTerminar' => $modelo['fecha_terminar'],
            'actividad' => $modelo['actividad'],
            'localizacion' => $modelo['localizacion'],
            'tipoCalzada' => $modelo['fk_calzada'],
            'baseM' => $modelo['base_m'],
            'longitud' => $modelo['longitud'],
            'longitudTotal' => $modelo['longitud_total'],
            'observaciones' => $modelo['observaciones'],
            'documentoModificacion' => $modelo['documento_modificacion'],
            'coordenadaEste' => $modelo['coordenada_este'],
            'coordenadaNorte' => $modelo['coordenada_norte'],
            'accionAmbiental' => $modelo['accion_ambiental'],
            'statusAccionAmbiental' => $modelo['status_accion_ambiental'],
            'tipoDePasoDeFauna' => $modelo['tipo_de_paso_de_fauna'],
            'obraAdyacente' => $modelo['obra_adyacente'],
            'licenciaAmbiental' => $modelo['fk_licencia_ambiental'],
            'tipoAdaptacion' => $modelo['fk_tipo_adaptacion'],
            'accionEstructura' => $modelo['fk_accion_estructura'],
            'estado' => $modelo['fk_estado'],
            'materialPresupuestado' => $modelo['fk_material_presupuestado'],
            'descripcion' => $modelo['descripcion'],
            'tipoObra' => $modelo['fk_tipo_obra'],
            'tipoEstructura' => $modelo['fk_tipo_estructura'],
            'objectTipoEstructura' => $modelo['objectTipoEstructura'],
            'objectMaterialPresupuestado' => $modelo['objectMaterialPresupuestado'],
            'objectTipoAdaptacion' => $modelo['objectTipoAdaptacion'],
            'objectAccionEstructura' => $modelo['objectAccionEstructura'],
            'objectEstado' => $modelo['objectEstado'],
            'objectlicenciaAmbiental' => $modelo['objectlicenciaAmbiental'],
            'objectTipoCalzada' => $modelo['objectTipoCalzada'],
            'objectTipoObra' => $modelo['objectTipoObra'],
        ];
    }

    public function estructuraTipoElementoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->estructuraTipoElementotoModel($data);
        });
    }

    public function estructuraTipoElementotoModel($modelo): array
    {
        return [
            'identificador' => $modelo['id'],
            'nombre' => $modelo['Elemento'],
            'estado' => $modelo['estado'],
        ];
    }

    public function htrSolicitudToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->htrSolicitudToModel($data);
        });
    }

    public function htrSolicitudToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_htrSolicitud'],
            'solicitud' => $modelo['fk_id_solicitud'],
            'horaFechaEnvio' => $modelo['horaFechaEnvio'],
            'estado' => $modelo['estado'],
            'ip' => $modelo['ip'],
            'horaVisto' => $modelo['horaVisto'],
            'vistoSN' => $modelo['vistoSN'],
            'nombreEquipo' => $modelo['nomEquipo'],
            'usuario' => $modelo['fk_usuario'],
            'vistoPor' => $modelo['VistoPor_id_usuario'],
        ];
    }

    public function htrUsuarioToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->htrUsuarioToModel($data);
        });
    }

    public function htrUsuarioToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_htrUsuarios,
            'imei' => $modelo->imei,
            'fechaHoraIngreso' => $modelo->fechaHoraIngreso,
            'ip' => $modelo->ip,
            'accionRealizada' => $modelo->accionRealizada,
            'nomUsuario' => $modelo->nomUsuario,
            'ubicacion' => $modelo->Ubicacion,
            'proyecto' => $modelo->fk_id_project_Company,
            'compania' => $modelo->fk_compañia,
        ];
    }

    public function asfaltoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->asfaltoToModel($data);
        });
    }

   public function asfaltoToModel($modelo): array
    {
        return [
            'id_solicitudAsf' => $modelo->id_solicitudAsf,
            'fk_id_usuario' => $modelo->fk_id_usuario,
            'nombreCompañia' => $modelo->nombreCompañia,
            'fechaSolicitud' => $modelo->fechaSolicitud,
            'formula' => $modelo->formula,
            'abscisas' => $modelo->abscisas,
            'AbscisaInicial' => strpos($modelo->abscisas, 'Inicial ') === false ? str_replace('K', '', str_replace('+', '', str_replace(' ', '', $modelo['abscisas']))) : str_replace('K', '', str_replace('+', '', str_replace(' ', '', substr($modelo['abscisas'], strpos($modelo['abscisas'], 'Inicial ') + 8, 7)))),
            'AbscisaFinal' => strpos($modelo->abscisas, 'Final ') === false ? '' : str_replace('K', '', str_replace('+', '', str_replace(' ', '', substr($modelo['abscisas'], strpos($modelo['abscisas'], 'Final ') + 6, 7)))),
            'Hito' => $modelo->hito,
            'Tramo' => $modelo->tramo,
            'calzada' => $modelo->calzada,
            'cantidadToneladas' => $modelo->cantidadToneladas,
            'tipoMezcla' => $modelo->tipoMezcla,
            'FechaHoraProgramacion' => $modelo->FechaHoraProgramacion,
            'estado' => $modelo->estado,
            'observaciones' => $modelo->observaciones,
            'companiDestino' => $modelo->companiDestino,
            'companiaD' => $modelo->companiDestino,
            'fechaAceptacion' => $modelo->fechaAceptacion,
            'Nombre' => $modelo->Nombre,
            'Apellido' => $modelo->Apellido,
            'nombre' => $modelo['Nombre'] . ' ' . $modelo['Apellido'],
            'FechaSolicitudExcel' => date("M-d-Y H:i:s", strtotime(str_replace('/', '-', $modelo['fechaSolicitud']) . "+ 16 seconds")),
            'FechaProgramacionExcel' => str_replace(' ', '', $modelo['FechaHoraProgramacion']) === '' ? '' : date("M-d-Y h:i:s", strtotime(str_replace(' ', ' ', str_replace('/', '-', $modelo['FechaHoraProgramacion'])) . "+ 16 seconds")),
            'toneFaltante' => $modelo->toneFaltante,
            'CostCode' => $modelo->CostCode,
            'Correo' => $modelo->Correo,
            'fk_LocationID' => $modelo->fk_LocationID,
            'MSOID' => $modelo->MSOID,
            'Ubicacion' => 'Tramo: ' . $modelo['tramo'] . ' Hito: ' . $modelo['hito'],
        ];
    }

    public function liberacionesToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->liberacionesToModel($data);
        });
    }

    public function liberacionesToModel($modelo): array
    {
        return [
            'solicitud' => $modelo['fk_solicitud'],
            'usuario' => $modelo['fk_usuario'],
            'comentarios' => $modelo['comentarios'],
            'firmaTopografia' => $modelo['firmaTopografia'],
            'firmaLaboratorio' => $modelo['firmaLaboratorio'],
            'firmaProduccion' => $modelo['firmaProduccion'],
            'firmaCalidad' => $modelo['firmaCalidad'],
            'firmaSST' => $modelo['firmaSST'],
            'firmaAmbiental' => $modelo['firmaAmbiental'],
            'estado' => $modelo['estado'],
            'fechaHoraLiberacion' => $modelo['fechaHoraLiberacion'],
            'fechaHoraSolicitud' => $modelo['fechaHoraSolicitud'],
            'nombreCompañia' => $modelo['nombreCompañia'],
            'objectFirmaAmbiental' => $modelo['objectFirmaAmbiental'],
            'objectFirmaCalidad' => $modelo['objectFirmaCalidad'],
            'objectFirmaProduccion' => $modelo['objectFirmaProduccion'],
            'objectFirmaSst' => $modelo['objectFirmaSst'],
            'objectFirmaTopografia' => $modelo['objectFirmaTopografia'],
            'objectFirmaLaboratorio' => $modelo['objectFirmaLaboratorio'],
        ];
    }

    public function liberacionesResponsableToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->liberacionesResponsableToModel($data);
        });
    }

    public function liberacionesResponsableToModel($modelo): array
    {
        return [
            'responsable' => $modelo['id_liberacion_responsable'],
            'actividad' => $modelo['fk_id_liberaciones_actividades'],
            'area' => $modelo['Area'],
            'idArea' => $modelo['fk_id_area'],
            'estado' => $modelo['estado'],
        ];
    }

    public function liberacionesActividadToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->liberacionesActividadToModel($data);
        });
    }

    public function liberacionesActividadToModel($modelo): array
    {
        return [
            'IDSOLIACTIVIDAD' => $modelo['id_solicitud_liberaciones_act'],
            'IDSOLICITUD' => $modelo['fk_id_solicitud_liberaciones'],
            'IDACTIVIDAD' => $modelo['fk_id_liberaciones_actividades'],
            'CALIFICACION' => $modelo['calificacion'],
            'ESTADO' => $modelo['estado'],
            'IDUSUARIO' => $modelo['fk_id_usuario'],
            'NOMBRE' => $modelo['nombre'],
            'CRITERIO' => $modelo['criterios'],
            'NOTA' => $modelo['nota'],
        ];
    }

    public function logAllTableToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->logAllTableToModel($data);
        });
    }

    public function logAllTableToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_log,
            'hostId' => $modelo->host_id,
            'accion' => $modelo->Accion,
            'hostName' => $modelo->hostName,
            'serverName' => $modelo->serverName,
            'localNetAddress' => $modelo->local_net_address,
            'ipUser' => $modelo->IP_user,
            'usuarioCreador' => $modelo->UsuarioCreador,
            'fechaInsert' => $modelo->FechaInsert,
            'fechaDelete' => $modelo->FechaDelete,
            'fechaUpdate' => $modelo->FechaUpdate,
            'tablaAfectada' => $modelo->TablaAfectada,
            'campoAfectado' => $modelo->CampoAfectado,
            'datoAntiguo' => $modelo->DatoAntiguo,
            'datoNuevo' => $modelo->DatoNuevo,
            'datoBorrado' => $modelo->DatoBorrado,
        ];
    }

    public function planillaControlAsfaltoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->planillaControlAsfaltotoModel($data);
        });
    }

    public function planillaControlAsfaltoToArray2($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->planillaControlAsfaltotoModel2($data);
        });
    }

    public function planillaControlAsfaltotoModel2($modelo): array
    {
        return [
            'identificador' => $modelo->id_planilla,
            'solicitud' => $modelo->fk_solicitud,
            'placaVehiculo' => $modelo->placaVehiculo,
            'codigoVehiculo' => $modelo->codigoVehiculo,
            'hora' => $modelo->hora,
            'wbeDestino' => $modelo->wbeDestino,
            'descripDestino' => $modelo->descripDestino,
            'formula' => $modelo->formula,
            'cantidad' => $modelo->cantidad,
            'firma' => $modelo->firma,
            'observacion' => $modelo->observacion,
            'fecha' => $modelo->fecha,
            'cantiEnviada' => $modelo->cantiEnviada,
            'usuario' => $modelo->fk_id_usuario,
            'turno' => $modelo->turno,
            'dateCreate' => $modelo->dateCreate,
            'plantaDespacho' => $modelo->plantaDespacho,
            'proyecto' => $modelo->fk_id_project_Company,
            'compania' => $modelo->fk_compañia,
            'nombrePlanta' => $modelo->NombrePlanta,
            'objectUsuPlanta' => $modelo->objectUsuPlanta,
        ];
    }

    public function planillaControlAsfaltotoModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_planilla,
            'solicitud' => $modelo->fk_solicitud,
            'placaVehiculo' => $modelo->placaVehiculo,
            'codigoVehiculo' => $modelo->codigoVehiculo,
            'hora' => $modelo->hora,
            'wbeDestino' => $modelo->wbeDestino,
            'descripDestino' => $modelo->descripDestino,
            'formula' => $modelo->formula,
            'cantidad' => $modelo->cantidad,
            'firma' => $modelo->firma,
            'observacion' => $modelo->observacion,
            'fecha' => $modelo->fecha,
            'cantiEnviada' => $modelo->cantiEnviada,
            'usuario' => $modelo->fk_id_usuario,
            'turno' => $modelo->turno,
            'dateCreate' => $modelo->dateCreate,
            'plantaDespacho' => $modelo->plantaDespacho,
            'proyecto' => $modelo->fk_id_project_Company,
            'compania' => $modelo->fk_compañia,
            'nombrePlanta' => $modelo->NombrePlanta,
            'objectUsuPlanta' => $modelo->objectUsuPlanta,
            'motivo' => $modelo->motivo,
            'estado' => $modelo->estado,
        ];
    }

    public function SolicitudAsfaltoToModel($modelo): array
    {
        $search = ['-', '_', '.', ' ', ','];
        $replace = ['', '', '', '', ''];
        $costCode = str_replace($search, $replace, $modelo->formula);

        return [
            'identificador' => $modelo->id_solicitudAsf,
            'usuario' => $modelo->fk_id_usuario,
            'nombreCompañia' => $modelo->nombreCompañia,
            'fechaSolicitud' => $modelo->fechaSolicitud,
            'formula' => $modelo->formula,
            'abscisas' => $modelo->abscisas,
            'hito' => $modelo->hito,
            'tramo' => $modelo->tramo,
            'ubicacion' => 'Tramo: '.$modelo->tramo.' Hito: '.$modelo->hito,
            'costCode' => $costCode,
            'calzada' => $modelo->calzada,
            'cantidadToneladas' => $modelo->cantidadToneladas,
            'tipoMezcla' => $modelo->tipoMezcla,
            'fechaHoraProgramacion' => $modelo->FechaHoraProgramacion,
            'estado' => $modelo->estado,
            'observaciones' => $modelo->observaciones,
            'compañiaDestino' => $modelo->CompañiaDestino,
            'fechaAceptacion' => $modelo->fechaAceptacion,
            'toneFaltante' => $modelo->toneFaltante,
            'CostCode' => $modelo->CostCode,
            'toneladaReal' => $modelo->toneladaReal,
            'notaCierre' => $modelo->notaCierre,
            'objectUsuario' => $modelo->objectUsuario,
            'objectUsuPlanta' => $modelo->objectUsuPlanta,
            'msoid' => $modelo->msoid,
        ];
    }

    public function projectCompanyToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->projectCompanyToModel($data);
        });
    }

    public function projectCompanyToModel($modelo): array
    {
        // TODO: Implement toModel() method.
        return [
            'identificador' => $modelo->id_Project_Company,
            'nombre' => $modelo->Nombre,
            'estado' => $modelo->Estado,
            'fechaCreacion' => $modelo->dateCreate,
            'descripcion' => $modelo->descripcion,
            'tema' => $modelo->fk_tema_interfaz,
            'pais' => $modelo->fk_pais,
            'companias' => $modelo->companias,
            'companiaPrincipal' => $modelo->companiaPrincipal,
            'rol' => $modelo->rol,
            'companyUser' => $modelo->companyUser,
        ];
    }

    public function projectCompanyEscondidoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->projectCompanyEscondidoToModel($data);
        });
    }

    public function projectCompanyEscondidoToModel($modelo): array
    {
        // TODO: Implement toModel() method.
        return [
            'identificador' => $modelo->id_Project_Company,
            'nombre' => $modelo->Nombre,
        ];
    }

    public function usuarioToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->usuarioToModel($data);
        });
    }

    public function usuarioToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_usuarios'],
            'nombreCuenta' => $modelo['usuario'],
            'cedula' => $modelo['cedula'],
            'matricula' => $modelo['matricula'],
            'area' => $modelo['area'],
            'estado' => $modelo['estado'],
            'rol' => $modelo['fk_rol'],
            'imeil' => $modelo['imeil'],
            'compania' => $modelo['fk_compañia'],
            'proyecto' => $modelo['fk_id_project_Company'],
            'plantaAsignada' => $modelo['fk_Planta_asignada'],
            'nombres' => $modelo['Nombre'],
            'apellido' => $modelo['Apellido'],
            'firma' => $modelo['Firma'],
            'correo' => $modelo['Correo'],
            'version' => $modelo['version'],
            'celular' => $modelo['celular'],
            'objectRol' => $modelo['objectRol'],
            'habilitado' => $modelo['habilitado'],
            'cel_confirmado' => $modelo['cel_confirmado'],
            'objectUsuarioProyecto' => $modelo['objectUsuarioProyecto'],
            'objectPlanta' => $modelo['objectPlanta'],
            'objectCostCenter' => $modelo['objectCostCenter'],
        ];
    }

    public function usuPlantaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->usuPlantaToModel($data);
        });
    }

    public function usuPlantaToArraySimplificado($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->usuPlantaToModelSimplificado($data);
        });
    }

    public function usuPlantaToModelSimplificado($modelo): array
    {
        return [
            'identificador' => $modelo->id_plata,
            'ubicacion' => $modelo->ubicacion,
            'tipoPlanta' => $modelo->tipoPlanta,
            'NombrePlanta' => $modelo->NombrePlanta,
            'idCentroCosto' => $modelo->fk_id_centroCosto,
            'descripcion' => $modelo->descripcion,
            'estado' => $modelo->estado,
            'planta' => $modelo->fk_planta,
            'tipo' => $modelo->tipo,
            'Location' => $modelo->fk_LocationID,
        ];
    }

    public function usuPlantaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_plata,
            'ubicacion' => $modelo->ubicacion,
            'tipoPlanta' => $modelo->tipoPlanta,
            'compañia' => $modelo->fk_id_project_Company,
            'nombreCompañia' => $modelo->nombreCompania,
            'nombre' => $modelo->NombrePlanta,
            'centroCosto' => $modelo->fk_id_centroCosto,
            'descripcion' => $modelo->descripcion,
            'planta' => $modelo->fk_planta,
            'tipo' => $modelo->tipo,
            'location' => $modelo->fk_LocationID,
            'estado' => $modelo->estado,
            'objectCompania' => $modelo->objectCompania,
            'objectPlanta' => $modelo->objectPlanta,
            'objectCostControl' => $modelo->objectCostControl,
            'objectLocation' => $modelo->objectLocation,
        ];
    }

    public function MsoToModel($modelo): array
    {
        return [
            'TxCounter' => $modelo['TxCounter'],
            'JobId' => $modelo['JobId'],
            'MSOID' => $modelo['MSOID'],
            'LocationID' => $modelo['LocationID'],
            'EquipmentID' => $modelo['EquipmentID'],
            'CostCode' => $modelo['CostCode'],
            'TranDate' => $modelo['TranDate'],
            'ForemanID' => $modelo['ForemanID'],
            'TimeCreated' => $modelo['TimeCreated'],
            'Qty' => $modelo['Qty'],
            'DateCreated' => $modelo['DateCreated'],
            'ExportFlag' => $modelo['ExportFlag'],
            'ExportCount' => $modelo['ExportCount'],
            'Rate' => $modelo['Rate'],
            'Unit' => $modelo['Unit'],
            'ScanDevice' => $modelo['ScanDevice'],
            'RecID' => $modelo['RecID'],
            'TransactionType' => $modelo['TransactionType'],
            'upsize_ts' => $modelo['upsize_ts'],
        ];
    }

    public function permisoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->permisoToModel($data);
        });
    }

    public function permisoToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_permiso'],
            'nombre' => $modelo['nombrePermiso'],
        ];
    }

    public function estadoEstructuraToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->estadoEstructuraToModel($data);
        });
    }

    public function estadoEstructuraToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id'],
            'nombre' => $modelo['nombre'],
        ];
    }

    public function wbHitosAbcisaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbHitosAbcisaToModel($data);
        });
    }

    public function wbHitosAbcisaToModel($modelo): array
    {
        return [
            'identificador' => $modelo['Id'],
            'hito' => $modelo['fk_Hitos'],
            'inicio' => $modelo['Inicio'],
            'fin' => $modelo['Fin'],
            'calzada' => $modelo['Calzada'],
            'estado' => $modelo['Estado'],
            'convInicio' => $modelo['convInicio'],
            'convFin' => $modelo['convFin'],
            'coordenadaInicial' => $modelo['coordenada_inicial'],
            'coordenadaFinal' => $modelo['coordenada_final'],
            'fechaRegistro' => $modelo['DateCreate'],
        ];
    }

    public function wbHitosToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbHitosToModel($data);
        });
    }

    public function wbHitosToModel($modelo): array
    {
        return [
            'identificador' => $modelo['Id'],
            'hito' => $modelo['Id_Hitos'],
            'descripcion' => $modelo['Descripcion'],
            'apuntador' => $modelo['Apuntador'],
            'estado' => $modelo['Estado'],
            'fecha' => $modelo['DateCreated'],
        ];
    }

    public function wbLicenciaAmbientalToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbLicenciaAmbientalToModel($data);
        });
    }

    public function wbLicenciaAmbientalToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id'],
            'nombre' => $modelo['nombre'],
            'entidadDeLicencia' => $modelo['entidad_de_licencia'],
        ];
    }

    public function wbMaterialPresupuestadoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbMaterialPresupuestadoToModel($data);
        });
    }

    public function wbMaterialPresupuestadoToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id'],
            'nombre' => $modelo['nombre'],
        ];
    }

    public function wbSeguriRolesToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguriRolesToModel($data);
        });
    }

    public function wbSeguriRolesToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_Rol'],
            'nombre' => $modelo['nombreRol'],
            'estado' => $modelo['estado'],
        ];
    }

    public function tipoCarrilToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbTipoCarrilToModel($data);
        });
    }

    public function wbTipoCarrilToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_tipo_carril'],
            'carril' => $modelo['Carril'],
            'descripcion' => $modelo['Descripcion'],
            'estado' => $modelo['Estado'],
        ];
    }

    public function wbTipoCalzadaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbTipoCalzadaToModel($data);
        });
    }

    public function wbTipoCalzadaToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_tipo_calzada'],
            'calzada' => $modelo['Calzada'],
            'descripcion' => $modelo['Descripcion'],
            'estado' => $modelo['Estado'],
        ];
    }

    public function wbTipoCapaoArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbTipoCapaToModel($data);
        });
    }

    public function wbTipoCapaToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_tipo_capa'],
            'descripcion' => $modelo['Descripcion'],
            'estado' => $modelo['Estado'],
            'fechaCreacion' => $modelo['dateCreate'],
            'usuario' => $modelo['userCreator'],
        ];
    }

    public function syncBasculatoArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->syncBasculaToModel($data);
        });
    }

    public function syncBasculaToModel($modelo): array
    {
        return [
            'id' => $modelo['id'],
            'pc' => $modelo['pc'],
            'nombre' => $modelo['nombre'],
            'ubicacion' => $modelo['ubicacion'],
            'estado' => $modelo['estado'],
            'creado' => $modelo['creado'],
            'aprobado' => $modelo['aprobado'],
            'desactivado' => $modelo['desactivado'],
        ];
    }

    public function syncAccionestoArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->syncAccionesToModel($data);
        });
    }

    public function syncAccionesToModel($modelo): array
    {
        return [
            'tablaa' => $modelo['tablaa'],
            'accion' => $modelo['accion'],
            'idn' => $modelo['idn'],
            'fecha' => $modelo['fecha'],
            'pc' => $modelo['pc'],
            'id' => $modelo['id'],
            'quien' => $modelo['quien'],
        ];
    }

    public function wbUsuarioProyectoArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbUsuarioProyectoToModel($data);
        });
    }

    public function wbUsuarioProyectoToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id'],
            'usuario' => $modelo['fk_usuario'],
            'proyecto' => $modelo['fk_id_project_Company'],
            'compania' => $modelo['fk_compañia'],
            'rol' => $modelo['fk_rol'],
            'area' => $modelo['fk_area'],
            'objectRol' => $modelo['objectRol'],
            'objectEmpresa' => $modelo['objectEmpresa'],
        ];
    }

    public function wbTipoDeApdatacionToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbTipoDeApdatacionToModel($data);
        });
    }

    public function wbTipoDeApdatacionToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id'],
            'nombre' => $modelo['nombre'],
        ];
    }

    public function tramoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->tramoToModel($data);
        });
    }

    public function tramoToModel($modelo): array
    {
        return [
            'identificados' => $modelo->id,
            'idTramo' => $modelo['Id_Tramo'],
            'descripcion' => $modelo['Descripcion'],
            'estado' => $modelo['Estado'],
            'fechaRegistro' => $modelo['dateCreate'],
        ];
    }

    public function tramoToArrayMod($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->tramoToModelMod($data);
        });
    }

    public function tramoToModelMod($modelo): array
    {
        return [
            'identificados' => $modelo->Id_Tramo,
            'idTramo' => $modelo['id'],
            'descripcion' => $modelo['Descripcion'],
            'estado' => $modelo['Estado'],
            'fechaRegistro' => $modelo['dateCreate'],
        ];
    }

    public function tramoHitoAsignToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->tramoHitoAsignToModel($data);
        });
    }

    public function tramoHitoAsignToModel($modelo): array
    {
        return [
            'identificador' => $modelo->Id_tramos_hitos,
            'tramo' => $modelo->fk_id_Tramo,
            'hito' => $modelo->fk_id_Hitos,
            'estado' => $modelo->Estado,
            'fechaRegistro' => $modelo->dateCreate,
            'objectHito' => $modelo->objectHito,
        ];
    }

    public function wbTipoViaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbTipoViaToModel($data);
        });
    }

    public function wbTipoViaToModel($modelo): array
    {
        return [
            'identificador' => $modelo['id_tipo_via'],
            'via' => $modelo['Via'],
            'descripcion' => $modelo['Descripcion'],
        ];
    }

    public function wbTipoDeObraToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbTipoDeObratoModel($data);
        });
    }

    public function wbTipoDeObratoModel($modelo): array
    {
        return [
            'identificador' => $modelo->id,
            'nombre' => $modelo->nombre,
        ];
    }

    public function wbTipoDeCapaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbTipoDeCapaToModel($data);
        });
    }

    public function wbTipoDeCapaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_tipo_capa,
            'descripcion' => $modelo->Descripcion,
            'estado' => $modelo->Estado,
            'fechaRegistro' => $modelo->dateCreate,
            'Asfalto' => $modelo->isAsfalto,
            'Actividad' => $modelo->is_actividad ? $modelo->is_actividad : 0,
        ];
    }

    public function formulaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->formulaToModel($data);
        });
    }

    public function formulaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id,
            'formula' => $modelo->formula,
            'tipoMezcla' => $modelo->fk_tipoMezcla,
            'dmx' => $modelo->dmx,
            'resistencia' => $modelo->resistencia,
            'relacion' => $modelo->relacion,
            'estado' => $modelo->estado,
            'objectTipoMezcla' => $modelo->objectTipoMezcla,
        ];
    }

    public function tipoMezclaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->tipoMezclaToModel($data);
        });
    }

    public function tipoMezclaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->Id,
            'tipo' => $modelo->Tipo,
            'estado' => $modelo->Estado,
        ];
    }

    public function wbSeguridadSitioTurnoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguridadSitioTurnoToModel($data);
        });
    }

    public function wbSeguridadSitioTurnoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_seguridad_sitio_turno,
            'turno' => $modelo->nombre_turno,
            'horas' => $modelo->horas_turno,
            'hora_inicio' => $modelo->hora_inicio_turno,
            'hora_final' => $modelo->hora_final_turno,
            'estado' => $modelo->estado,
        ];
    }

    public function wbSeguridadSitioToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguridadSitioToModel($data);
        });
    }

    public function wbSeguridadSitioToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_registro_proyecto,
            'idTurno' => $modelo->fk_id_turno_seguridad_sitio,
            'fechaInicio' => $modelo->fecha_inicio,
            'fechaFinalizacion' => $modelo->fecha_finalizacion,
            'fechaModificacion' => $modelo->fecha_modificacion,
            'idTramo' => $modelo->fk_id_tramo,
            'idHito' => $modelo->fk_id_hito,
            'abscisa' => $modelo->abscisa,
            'otra_ubicacion' => $modelo->otra_ubicacion,
            'idCentroProduccion' => $modelo->fk_id_centro_produccion,
            'observaciones' => $modelo->observaciones,
            'idEstado' => $modelo->fk_id_estado,
            'usuario_crea' => $modelo->usuario_creacion,
            'proyecto' => $modelo->fk_id_project_Company,
            'maquinarias' => $modelo->maquinarias,
            'materiales' => $modelo->materiales,
            'evidencias' => $modelo->evidencias,
            'element_confirm' => $modelo->element_confirm,
            'evidence_confirm' => $modelo->evidence_confirm,
            'usuario_crea_name' => $modelo->usuario_crea_name,
            'idTraslado' => $modelo->id_traslado,
            'nombreTurno' => $modelo->nombre_turno,
            'coordenadas' => $modelo->coordenadas_de_solicitud,
        ];
    }

    public function wbSeguridadSitioWebToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguridadSitioWebToModel($data);
        });
    }

    public function wbSeguridadSitioWebToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_seguridad_sitio,
            'No' => $modelo->id_registro_proyecto,
            'Fecha_Inicio' => $modelo->fecha_inicio,
            'Fecha_Final' => $modelo->fecha_finalizacion,
            'Turno' => $modelo->nombre_turno,
            'Tramo' => $modelo->fk_id_tramo,
            'Hito' => $modelo->fk_id_hito,
            'Otra_ubicacion' => $modelo->otra_ubicacion,
            'Abscisa' => $modelo->abscisa,
            'Solicitante' => $modelo->usuario_crea_name,
            'observaciones' => $modelo->observaciones,
            'calificador' => $modelo->usuario_cal_name,
            'observaciones_calificador' => $modelo->observaciones_calificacion,
            'Fecha_Creacion' => $modelo->fecha_creacion,
            'maquinarias' => $modelo->maquinarias,
            'materiales' => $modelo->materiales,
            'Traslado' => $modelo->id_traslado,
            'Estado' => $modelo->estado_name,
            'prox_vencer' => $modelo->prox_vencer,
            'alert_evidencias_diarias' => $modelo->alert_evidencias_diarias,
            'evidencia_confirmacion' => $modelo->evidencia_confirmacion,
            'confirm_pdf' => $modelo->confirm_pdf,
            'Arma_fuego' => $modelo->arma_fuego,
            'Motorizado' => $modelo->motorizado,
        ];
    }

    public function wbSeguridadSitioExcelToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguridadSitioExcelToModel($data);
        });
    }

    public function wbSeguridadSitioExcelToModel($modelo): array
    {
        return [
            'No' => $modelo->id_registro_proyecto,
            'Fecha_Inicio' => $modelo->fecha_inicio,
            'Fecha_Final' => $modelo->fecha_finalizacion,
            'Turno' => $modelo->nombre_turno,
            'Tramo' => $modelo->fk_id_tramo,
            'Hito' => $modelo->fk_id_hito,
            'Abscisa' => $modelo->abscisa,
            'Ubicacion' => $modelo->otra_ubicacion,
            'Solicitante' => $modelo->usuario_crea_name,
            'observaciones' => $modelo->observaciones,
            'calificador' => $modelo->usuario_cal_name,
            'observaciones_calificador' => $modelo->observaciones_calificacion,
            'Fecha_Creacion' => $modelo->fecha_creacion,
            'maquinarias' => $modelo->maquinarias,
            'materiales' => $modelo->materiales,
            'Estado' => $modelo->estado_name,
            'prox_vencer' => $modelo->prox_vencer,
            'Coordenadas' => $modelo->coordenadas_de_solicitud,
            'Arma_fuego' => $modelo->arma_fuego,
            'Motorizado' => $modelo->motorizado,
        ];
    }

    public function wbSeguridadSitioEquipoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguridadSitioEquipoToModel($data);
        });
    }

    public function wbSeguridadSitioEquipoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_seguridad_sitio_equipo,
        ];
    }

    public function wbSeguridadSitioMaterialToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguridadSitioMaterialToModel($data);
        });
    }

    public function wbSeguridadSitioMaterialToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_seguridad_sitio_material,
            'seguridad_sitio' => $modelo->fk_id_seguridad_sitio,
            'material' => $modelo->material,
            'cantidad' => $modelo->cantidad,
            'unidadMedida' => $modelo->unidad_medida,
        ];
    }

    public function wbSeguridadSitioEvidenciaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguridadSitioEvidenciaToModel($data);
        });
    }

    public function wbSeguridadSitioEvidenciaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_seguridad_sitio_evidencia,
            'seguridad_sitio' => $modelo->fk_id_seguridad_sitio,
            'evidencia1' => $modelo->evidencia1,
            'evidencia2' => $modelo->evidencia2,
            'observaciones' => $modelo->observaciones,
            'tipo' => $modelo->tipo,
            'fecha_registro' => $modelo->fecha_registro,
        ];
    }

    public function wbSeguridadSitioHistorialToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSeguridadSitioHistorialToModel($data);
        });
    }

    public function wbSeguridadSitioHistorialToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_seguridad_sitio_historial,
            'evento' => $modelo->evento,
            'usuario' => $modelo->usuario_evento,
            'fecha' => $modelo->fecha_registro,
            'hora' => $modelo->hora_registro,
            'observaciones' => $modelo->observaciones,
            'id_evidencias' => $modelo->id_evidencia,
            'maquinarias' => $modelo->maquinarias,
            'materiales' => $modelo->materiales,
            'proyecto' => $modelo->proyecto,
            'usuario_evento_name' => $modelo->usuario_evento_name,
        ];
    }

    public function wbLaboratorioEnsayosToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbLaboratorioEnsayosToModel($data);
        });
    }

    public function wbLaboratorioEnsayosToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_ensayo,
            'nombre' => $modelo->nombre,
            'descripcion' => $modelo->descripcion,
            'estado' => $modelo->estado,
        ];
    }

    public function wbLaboratoriosToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbLaboratoriosToModel($data);
        });
    }

    public function wbLaboratoriosToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_laboratorio,
            'descripcion' => $modelo->descripcion,
            'ubicacion' => $modelo->ubicacion,
            'estado' => $modelo->estado,
        ];
    }

    public function wbLaboratorioTipoControlToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbLaboratorioTipoControlToModel($data);
        });
    }

    public function wbLaboratorioTipoControlToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_tipo_control,
            'descripcion' => $modelo->descripcion,
            'estado' => $modelo->estado,
        ];
    }

    public function wbLaboratorioTipoMuestreoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbLaboratorioTipoMuestreoToModel($data);
        });
    }

    public function wbLaboratorioTipoMuestreoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_tipo_muestreo,
            'nombre' => $modelo->nombre,
            'descripcion' => $modelo->descripcion,
            'esVia' => $modelo->es_via,
            'estado' => $modelo->estado,
        ];
    }

    public function wbProgramadorTareaToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbProgramadorTareaToModel($data);
        });
    }

    public function wbProgramadorTareaToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_programador_tareas,
            'descripcion' => $modelo->descripcion,
            'programacion' => $modelo->programacion,
            'intervalo' => $modelo->intervalo_semana,
            'diaSemana' => $modelo->dia_semana,
            'diaMes' => $modelo->dia_mes,
            'semanaMes' => $modelo->semana_mes,
            'mes' => $modelo->mes,
            'tipo' => $modelo->tipo,
            'tipoText' => $modelo->tipoText,
            'estado' => $modelo->estado,
        ];
    }

    public function wbTareasProgramadasToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbTareasProgramadasToModel($data);
        });
    }

    public function wbTareasProgramadasToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_tareas_programadas,
            'programador' => $modelo->fk_id_programador_tareas,
            'modulo' => $modelo->modulo,
            'moduloName' => $modelo->moduloName,
            'tarea' => $modelo->metodo,
            'tareaName' => $modelo->metodoName,
            'parametros' => $modelo->parametros,
            'proxima_ejecucion' => $modelo->prox_ejecucion,
            'descripcion' => $modelo->descripcion,
            'estado' => $modelo->estado,
        ];
    }

    public function wbLaboratorioSolicitudMuestraToArray($lista, $is_excel = false): Collection|\Illuminate\Support\Collection
    {
        if ($is_excel) {
            return $lista->map(function ($data) {
                return $this->wbLaboratorioSolicitudMuestraExcelToModel($data);
            });
        }

        return $lista->map(function ($data) {
            return $this->wbLaboratorioSolicitudMuestraToModel($data);
        });
    }

    public function wbLaboratorioSolicitudMuestraToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_muestra_solicitud,
            'identificador_proy' => $modelo->id_muestra_proyecto,
            'muestreo' => $modelo->fk_id_tipo_muestreo,
            'muestreo_name' => $modelo->muestreo_name,
            'es_via' => $modelo->es_via,
            'material' => $modelo->fk_id_material_lista,
            'material_name' => $modelo->material_name,
            'control' => $modelo->fk_id_tipo_control,
            'control_name' => $modelo->control_name,
            'observaciones' => $modelo->observaciones,
            'planta' => $modelo->fk_id_plata,
            'planta_name' => $modelo->planta_name,
            'tramo' => $modelo->fk_id_tramo,
            'hito' => $modelo->fk_id_hito,
            'abscisa' => $modelo->abscisa_inicial, // eliminar columna cuando todos los celulares esten actualizados
            'abscisa_inicial' => $modelo->abscisa_inicial,
            'abscisa_final' => $modelo->abscisa_final,
            'via' => $modelo->fk_tipo_via,
            'via_name' => $modelo->via_name,
            'capa' => $modelo->fk_tipo_capa,
            'capa_name' => $modelo->capa_name,
            'estado' => $modelo->fk_id_estado,
            'creador' => $modelo->fk_id_usuarios_creacion,
            'creador_name' => $modelo->creador_name,
            'fecha_creacion' => $modelo->fecha_creacion,
            'registroMuestras' => $modelo->registroMuestras,
            'modificador_name' => $modelo->modificador_name,
            'fecha_modificacion' => $modelo->fecha_edicion,
            'anular_finalizar_observaciones' => $modelo->anulado_observacion,
        ];
    }

    public function wbLaboratorioSolicitudMuestraExcelToModel($modelo): array
    {
        return [
            'Solicitud' => $modelo->id_muestra_proyecto,
            'Tipo muestreo' => $modelo->fk_id_tipo_muestreo,
            'Material' => $modelo->fk_id_material_lista,
            'Tipo control' => $modelo->fk_id_tipo_control,
            'Planta' => $modelo->fk_id_plata,
            'Tramo' => $modelo->fk_id_tramo,
            'Hito' => $modelo->fk_id_hito,
            'Abscisa inicial' => $modelo->abscisa_inicial,
            'Abscisa final' => $modelo->abscisa_final ? $modelo->abscisa_final : '',
            'Via' => $modelo->fk_tipo_via,
            'Capa' => $modelo->fk_tipo_capa,
            'Estado' => $modelo->fk_id_estado,
            'Solicitante' => $modelo->creador_name,
            'observaciones' => $modelo->observaciones,
            'Fecha solicitud' => $modelo->fecha_creacion,
            'Ensayos' => $modelo->ensayos,
            'Muestras' => $modelo->muestras_anexadas,
            'Total de muestras' => $modelo->total_muestras,
            'muestras entregadas' => $modelo->total_muestras_entregadas,
            'Fecha ultima muestra recolectada' => $modelo->ultima_muestra_recolectada,
            'usuario actualizacion' => $modelo->modificador_name,
            'fecha actualizacion' => $modelo->fecha_edicion,
            'motivo' => $modelo->anulado_observacion,
        ];
    }

    public function wbLaboratorioMuestraRegistroToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbLaboratorioMuestraRegistroToModel($data);
        });
    }

    public function wbLaboratorioMuestraRegistroToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_muestra_registro,
            'solicitud' => $modelo->fk_id_muestra_solicitud,
            'codigoQr' => $modelo->codigo,
            'color' => $modelo->color_material,
            'tamaño' => $modelo->tamaño,
            'tamano_web' => $modelo->tamaño,
            'dureza' => $modelo->dureza,
            'observacion' => $modelo->observacion,
            'foto' => $modelo->foto,
            'ubicacion' => $modelo->ubicacion_muestra,
            'recolector' => $modelo->recolector,
            'laboratorio' => $modelo->laboratorio,
            'laboratorista' => $modelo->laboratorista,
            'fecha_creacion' => $modelo->fecha_creacion,
            'fecha_recoleccion' => $modelo->fecha_entrega,
        ];
    }

    public function wbLaboratorioMuestraRegistroGestionToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbLaboratorioMuestraRegistroGestionToModel($data);
        });
    }

    public function wbLaboratorioMuestraRegistroGestionToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_muestra_registro,
            'solicitud' => $modelo->solicitud ? $modelo->solicitud->id_muestra_solicitud : '',
            'solicitud_codigo' => $modelo->solicitud ? $modelo->solicitud->id_muestra_proyecto : '',
            'codigoQr' => $modelo->codigo,
            'color' => $modelo->color_material,
            'tamano_web' => $modelo->tamaño,
            'dureza' => $modelo->dureza,
            'observacion' => $modelo->observacion,
            'foto' => $modelo->foto,
            'ubicacion' => $modelo->ubicacion_muestra,
            'recolector' => $modelo->recolector,
            'laboratorio' => $modelo->laboratorio,
            'laboratorista' => $modelo->laboratorista,
            'fecha_creacion' => $modelo->fecha_creacion,
            'fecha_recoleccion' => $modelo->solo_fech_entrega,
            'fecha_y_hora_recoleccion' => $modelo->fecha_entrega,
            'estado' => $modelo->estado,
            'activo' => $modelo->estado,
            'material' => $modelo->material,
        ];
    }

    public function wbLaboratorioMuestraRegistroEnsayoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbLaboratorioMuestraRegistroEnsayoToModel($data);
        });
    }

    public function wbLaboratorioMuestraRegistroEnsayoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_muestras_registros_ensayos,
            'ensayo' => $modelo->ensayo ? $modelo->ensayo->id_ensayo : '',
            'ensayo_name' => $modelo->ensayo ? $modelo->ensayo->nombre.' - '.$modelo->ensayo->descripcion : '',
            'estado' => $modelo->estado,
            'observacion' => $modelo->observacion,
            'usuario_creador' => $modelo->fk_id_usuario_creador,
            'fecha_creacion' => $modelo->fecha_creacion,
            'usuario_ultimo' => $modelo->usuario_ultimo,
            'fecha_ultimo' => $modelo->fecha_ultimo,
        ];
    }

    public function syncRegistroToArray($lista, $is_excel = false): Collection|\Illuminate\Support\Collection
    {
        if ($is_excel) {
            return $lista->map(function ($data) {
                return $this->syncRegistroToModel($data);
            });
        }

        return $lista;
    }

    public function syncRegistroToModel($modelo): array
    {
        return [
            'FECHA' => $modelo->fecha_string,
            'HORA' => $modelo->hora,
            'EQUIPO' => $modelo->equipo,
            'PLACA' => $modelo->placa,
            'CONDUCTOR' => $modelo->conductor,
            'CEDULA' => $modelo->cedula,
            'ORIGEN' => $modelo->provedor,
            'DESTINO' => $modelo->destino,
            'PRODUCTO' => $modelo->producto,
            'PESO VACIO' => $modelo->peso1,
            'PESO BRUTO' => $modelo->peso2,
            'PESO NETO' => $modelo->peso3,
            'TIPO' => $modelo->tipo,
            'USUARIO' => $modelo->quien,
            'TICKET' => $modelo->baucher,
            'OBSERVACIÓN' => $modelo->observacion,
        ];
    }

    public function WbMaterialAutorizadoToArray($lista, $autorizado): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) use ($autorizado) {
            return $this->WbMaterialAutorizadoToModel($data, $autorizado);
        });
    }

    public function WbMaterialAutorizadoToModel($modelo, $autorizado): array
    {
        return [
            'identificador' => $modelo->id_material_lista,
            'nombre' => $modelo->Nombre,
            'descripcion' => $modelo->Descripcion,
            'UnidadMedida' => $modelo->unidadMedida,
            'Autorizado' => $autorizado,
        ];
    }

    public function WbFormulaAutorizadoToArray($lista, $autorizado): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) use ($autorizado) {
            return $this->WbFormulaAutorizadoToModel($data, $autorizado);
        });
    }

    public function WbFormulaAutorizadoToModel($modelo, $autorizado): array
    {
        return [
            'identificador' => $modelo->id_formula_lista,
            'nombre' => $modelo->Nombre,
            'descripcion' => $modelo->formulaDescripcion,
            'UnidadMedida' => $modelo->unidadMedida,
            'Autorizado' => $autorizado,
        ];
    }

    public function WbPlantaAutorizadaToArray($lista, $autorizado): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) use ($autorizado) {
            return $this->WbPlantaAutorizadaToModel($data, $autorizado);
        });
    }

    public function WbPlantaAutorizadaToModel($modelo, $autorizado): array
    {
        return [
            'identificador' => $modelo->id_plata,
            'NombrePlanta' => $modelo->NombrePlanta,
            'descripcion' => $modelo->descripcion,
            'Autorizado' => $autorizado,
        ];
    }

    public function WbSolicitudLiberacionToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->WbSolicitudLiberacionToModel($data);
        });
    }

    public function WbSolicitudLiberacionToModel($modelo): array
    {
        return [
            'solicitud' => $modelo->id_solicitud_liberaciones,
            'usuario' => $modelo->fk_id_usuarios,
            'tramo' => $modelo->fk_id_tramo,
            'hito' => $modelo->fk_id_hito,
            'abscisaInicial' => $modelo->abscisaInicialReferencia,
            'abscisaFinal' => $modelo->abscisaFinalReferencia,
            'nota' => $modelo->notaUsuario,
            'espesor' => $modelo->espesor,
            'ubicacion' => $modelo->ubicacion,
            'fecha' => $modelo->date_create,
            'fecha_solicitud' => $modelo->Fecha_solicitud,
            'plantaId' => $modelo->fk_id_planta,
            'materialId' => $modelo->fk_id_material,
            'formulaId' => $modelo->fk_id_formula,
            'estadoId' => $modelo->fk_id_estados,
            'capa' => ($modelo->capa) ? $modelo->capa->Descripcion : '',
            'carril' => ($modelo->carril) ? $modelo->carril->Descripcion : '',
            'calzada' => ($modelo->calzada) ? $modelo->calzada->Descripcion : '',
            'estado' => ($modelo->estado) ? $modelo->estado->descripcion_estado : '',
            'planta' => ($modelo->planta) ? $modelo->planta->NombrePlanta : '',
            'material' => ($modelo->material) ? $modelo->material->Nombre : '',
            'formula' => ($modelo->formula) ? $modelo->formula->Nombre : '',
            'firmas' => ($modelo->firmas) ? $modelo->firmas->map(function ($data) {
                return [
                    'identificador' => $data->id_solicitudes_liberaciones_firmas,
                    'idArea' => $data->fk_id_area,
                    'nombreArea' => ($data->area) ? $data->area->Area : '',
                    'estado' => $data->estado,
                    'usuario' => $data->fk_id_usuario,
                    'nota' => $data->nota,
                    'panoramica' => $data->panoramica
                ];
            }) : '',
            'soli_lib_act' => ($modelo->lib_actividad) ? $modelo->lib_actividad->map(function ($data) {
                return [
                    'identificador' => $data->id_solicitud_liberaciones_act,
                    'idActividad' => ($data->actividad) ? $data->actividad->id_liberaciones_actividades : '',
                    'nombre' => ($data->actividad) ? $data->actividad->nombre : '',
                    'criterio' => ($data->actividad) ? $data->actividad->criterios : '',
                    'calificacion' => $data->calificacion,
                    'nota' => $data->nota,
                    //'idResponsable' => ($data->responsable) ? $data->responsable->id_liberacion_responsable : '',
                    'idArea' => ($data->responsable) ? $data->responsable->pluck('fk_id_area') : '',
                ];
            }) : '',
            'foto' => "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"
            //'foto' => null
        ];
    }

    public function wbMotivoRechazoToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbMotivoRechazoToModel($data);
        });
    }

    public function wbMotivoRechazoToModel($modelo): array
    {
        return [
            'identificador' => $modelo->id_motivo_rechazo,
            'motivo' => $modelo->motivo,
            'estado' => $modelo->estado,
            'es_obs' => $modelo->observacion,
            'es_global' => $modelo->es_global,
            'asignadas' => $modelo->motivo_rechazo_asignacion ? $modelo->motivo_rechazo_asignacion->map(function ($info) {
                return [
                    'identificador' => $info->id_motivo_rechazo_asignacion,
                    'area_id' => $info->area ? $info->area->id_area : '',
                    'area' => $info->area ? $info->area->Area : '',
                    'capa_id' => $info->capa ? $info->capa->id_tipo_capa : '',
                    'capa' => $info->capa ? $info->capa->Descripcion : '',
                ];
            }) : '',
        ];
    }

    public function wbMotivoRechazoAsignacionToArray($lista, $case = 0): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) use ($case) {
            return $this->wbMotivoRechazoAsignacionToModel($data, $case);
        });
    }

    public function wbMotivoRechazoAsignacionToModel($modelo, $case = 0): array
    {
        switch ($case) {
            case 0:
                $return = [
                    'motivo_id' => $modelo->motivo_rechazo ? $modelo->motivo_rechazo->id_motivo_rechazo : '',
                    'motivo' => $modelo->motivo_rechazo ? $modelo->motivo_rechazo->motivo : '',
                    'es_obs' => $modelo->motivo_rechazo ? $modelo->motivo_rechazo->observacion : '',
                    'es_global' => $modelo->motivo_rechazo ? $modelo->motivo_rechazo->es_global : '',
                    'area_id' => $modelo->area ? $modelo->area->id_area : '',
                    'area' => $modelo->area ? $modelo->area->Area : '',
                    'capa_id' => $modelo->capa ? $modelo->capa->id_tipo_capa : '',
                    'capa' => $modelo->capa ? $modelo->capa->Descripcion : '',
                ];
                break;
            case 1:
                $return = [
                    'motivo_id' => $modelo->id_motivo_rechazo,
                    'motivo' => $modelo->motivo,
                    'es_obs' => $modelo->observacion,
                    'es_global' => $modelo->es_global,
                    'area_id' => null,
                    'area' => null,
                    'capa_id' => null,
                    'capa' => null,
                ];
                break;
        }

        return $return;
    }

    public function wbSolicitudMaterialToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSolicitudMaterialToModel($data);
        });
    }

    public function wbSolicitudMaterialToModel($modelo): array
    {
        $destino = ($modelo->plantas_destino) ? $modelo->plantas_destino->NombrePlanta : $modelo->tramo->Id_Tramo.'-'.$modelo->hitos->Id_Hitos;
        if ($modelo->plantas_destino) {
            $abscisa = '';
        } else {
            $abscisaInicial = $modelo->abscisaInicialReferencia;
            $abscisaFinal = $modelo->abscisaFinalReferencia;
            $abscisa = 'K'.substr($abscisaInicial, 0, 2).'+'.substr($abscisaInicial, 2).
                       '-K'.substr($abscisaFinal, 0, 2).'+'.substr($abscisaFinal, 2);
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
        $usuario = $nombre.' '.$apellido;
        $nombreapro = ($modelo->usuarioaprobador) ? $modelo->usuarioaprobador->Nombre : '';
        $apellidoapro = ($modelo->usuarioaprobador) ? $modelo->usuarioaprobador->Apellido : '';
        $usuarioapro = $nombreapro.' '.$apellidoapro;
        $estado = ($modelo->estado) ? $modelo->estado->descripcion_estado : 'Sin Estado';
        $estado_descripcion = ($modelo->estado) ? $modelo->estado->descripcion_estado : 'Sin Estado';
        if ($modelo->estado->id_estados >= 7 && $modelo->estado->id_estados <= 11) {
            $estado = 'POR APROBAR';
        } elseif ($modelo->estado->id_estados == 13) {
            $estado = 'RECHAZADO';
        } elseif ($modelo->estado->id_estados == 14) {
            $estado = 'ANULADO';
        } else {
            $estado = 'APROBADO';
        }

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
        ];
    }
}
