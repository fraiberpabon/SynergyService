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
            'tipo_id' => $modelo->fk_id_material_tipo,
            'tipo' => $modelo->tipo_material ? $modelo->tipo_material->tipoDescripcion : '',
            'compuesto' => $modelo->tipo_material ? $modelo->tipo_material->Compuesto : '',
            'solicitable' => $modelo->Solicitable,
            'proyecto' => $modelo->fk_id_project_company,
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
            'companiaNombre' => $modelo->compania ? $modelo->compania->nombreCompañia : '',
            'nombreTipoEquipo' => $modelo->tipo_equipo ? $modelo->tipo_equipo->nombre : '',
            'tipoEquipo' => $modelo->fk_id_tipo_equipo,
            'tipocontrato' => $modelo->tipocontrato,
            //'codigoExterno' => $modelo->codigo_externo,
            //'horometro' => $modelo->horometros ? $modelo->horometros->horometro : $modelo->horometro_inicial,
            //'fechaHorometro' => $modelo->horometros ? $modelo->horometros->create_at : $modelo->updated_at ?? $modelo->create_at,
            //'fechaUbicacion' => $modelo->ubicacion ? $modelo->ubicacion->created_at : null,
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
            'nombreUsuario' => $modelo['tipoUsuario'] != null ? $modelo['tipoUsuario']['Nombre'] . ' ' . $modelo['tipoUsuario']['Apellido'] : '',
            'nombreEstado' => $modelo['tipoEstado'] != null ? $modelo['tipoEstado']['descripcion_estado'] : '',
            'latitud' => $modelo['latitud'],
            'longitud' => $modelo['longitud'],
            'tipoHallazgos' => $modelo['tipoHallazgo'],
            'FechaCierre' => $modelo['fecha_cierre'],
            'ObservacionesCierre' => $modelo['observaciones_cierre'],
            'CerradoPor' => $modelo['tipoUsuarioAct'] != null ? $modelo['tipoUsuarioAct']['Nombre'] . ' ' . $modelo['tipoUsuarioAct']['Apellido'] : '',
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
            'ubicacion' => 'Tramo: ' . $modelo->tramo . ' Hito: ' . $modelo->hito,
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
            'idUsuario' => $modelo->id_usuarios,
            'usuario' => $modelo->usuario,
            'cedula' => $modelo->cedula,
            'codigo' => $modelo->matricula,
            'nombre' => $modelo->Nombre,
            'apellido' => $modelo->Apellido,
            'correo' => $modelo->Correo,
            'area' => $modelo->area,
            'firma' => $modelo->Firma,
            'imei' => $modelo->imeil,
            'proyecto' => $modelo->fk_id_project_Company,
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
            'ensayo_name' => $modelo->ensayo ? $modelo->ensayo->nombre . ' - ' . $modelo->ensayo->descripcion : '',
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

    public function wbSolicitudMaterialToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->wbSolicitudMaterialToModel($data);
        });
    }

    public function wbSolicitudMaterialToModel($modelo): array
    {
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

    public function solicitudesAppToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->solicitudesAppToModel($data);
        });
    }

    public function solicitudesAppToModel($modelo): array
    {
        return [
            'identificador' => $modelo->identificador,
            'tipo' => $modelo->tipo,
            'capa_id' => $modelo->fk_id_tipo_capa,
            'capa' => $modelo->tipoCapa ? $modelo->tipoCapa->Descripcion : null,
            'material_id' => $modelo->fk_id_material,
            'material' => $modelo->materialLista ? $modelo->materialLista->Nombre : null,
            'tramo' => $modelo->fk_id_tramo,
            'hito' => $modelo->fk_id_hito,
            'abscisaInicial' => $modelo->abscisaInicialReferencia,
            'abscisaFinal' => $modelo->abscisaFinalReferencia,
            'carril' => $modelo->tipoCarril ? $modelo->tipoCarril->Descripcion : null,
            'calzada' => $modelo->tipoCalzada ? $modelo->tipoCalzada->Descripcion : null,
            'formula_id' => $modelo->fk_id_formula,
            'fk_formula_cdp' => $modelo->fk_formula_cdp,
            'formula' => $modelo->formulaLista ? $modelo->formulaLista->Nombre : null,
            'formula_desc' => $modelo->formulaLista ? $modelo->formulaLista->formulaDescripcion : null,
            'planta_id' => $modelo->fk_id_plantaReasig ? $modelo->fk_id_plantaReasig : $modelo->fk_id_planta,
            'planta' => $modelo->plantaReasig ? $modelo->plantaReasig->NombrePlanta : $modelo->plantas->NombrePlanta,
            'cantidad' => $modelo->cantidad_real ? $modelo->cantidad_real : $modelo->Cantidad,
            'numeroCapa' => $modelo->numeroCapa,
            'planta_destino_id' => $modelo->fk_id_planta_destino,
            'planta_destino' => $modelo->plantas_destino ? $modelo->plantas_destino->NombrePlanta : null,
            'usuario_crea' => $modelo->usuario ? ($modelo->usuario->Nombre ?? '') . ' '. ($modelo->usuario->Apellido ?? '') : null,
            'notaUsuario' => $modelo->notaUsuario,
            'usuario_upd' => $modelo->usuarioAprobador ? ($modelo->usuarioAprobador->Nombre ?? '') . ' '. ($modelo->usuarioAprobador->Apellido ?? '') : null,
            'notaSU' => $modelo->notaSU,
            //'notaCDC' => $modelo->notaCenProduccion,
            //'cost_code' => $modelo->cost_code,
            //'estructura_id' => $modelo->notaCenProduccion,
            //'elemento_vaciar_id' => $modelo->cost_code,
            'fechaProgramacion' => $modelo->fechaProgramacion,
            'fechaCreacion' => $modelo->dateCreation,
            'proyecto' => $modelo->fk_id_project_Company,
        ];
    }

    public function WbFormulasToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->WbFormulasToModel($data);
        });
    }

    public function WbFormulasToModel($modelo): array
    {
        return [
            'identificador' => $modelo->identificador,
            'nombre' => $modelo->Nombre,
            'descripcion' => $modelo->formulaDescripcion,
            'unidad_medida' => $modelo->unidadMedida,
            'proyecto' => $modelo->fk_id_project_Company,
        ];
    }

    public function WbFormulasComposicionToArray($lista): Collection|\Illuminate\Support\Collection
    {
        return $lista->map(function ($data) {
            return $this->WbFormulasComposicionToModel($data);
        });
    }

    public function WbFormulasComposicionToModel($modelo): array
    {
        return [
            'identificador' => $modelo->identificador,
            'formula_id' => $modelo->fk_formula_CentroProduccion,
            'material_id' => $modelo->fk_material_CentroProduccion,
            'porcentaje' => $modelo->Porcentaje,
            'codigo_formula_cdp' => $modelo->fk_codigoFormulaCdp,
            'proyecto' => $modelo->fk_id_project_Company,
        ];
    }
}
