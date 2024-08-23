<?php

namespace App\Models;

use App\Models\Materiales\WbMaterialLista;
use App\Models\Usuarios\usuarios_M;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class WbSolicitudMateriales extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_Solicitud_Materiales';
    protected $primaryKey = 'id_solicitud_Materiales';
    public $timestamps = false;
    public $module = 'Solicitud Materiales';

    public function getTable()
    {
        return $this->table;
    }

    public function generateTags(): array
    {
        return [
            $this->module,
        ];
    }

    public function usuario()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_id_usuarios');
    }

    public function usuarioAprobador()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_id_usuarios_update');
    }

    public function tipoCalzada()
    {
        return $this->hasOne(WbTipoCalzada::class, 'id_tipo_calzada', 'fk_id_tipo_calzada');
    }

    public function materialLista()
    {
        return $this->hasOne(WbMaterialLista::class, 'id_material_lista', 'fk_id_material');
    }

    public function tramo()
    {
        return $this->hasOne(WbTramos::class, 'Id_Tramo', 'fk_id_tramo');
    }

    public function hitos()
    {
        return $this->hasOne(WbHitos::class, 'Id_Hitos', 'fk_id_hito');
    }

    public function formulaLista()
    {
        return $this->hasOne(WbFormulaLista::class, 'id_formula_lista', 'fk_id_formula');
    }

    public function tipoCapa()
    {
        return $this->hasOne(WbTipoCapa::class, 'id_tipo_capa', 'fk_id_tipo_capa');
    }

    public function tipoCarril()
    {
        return $this->hasOne(WbTipoCarril::class, 'id_tipo_carril', 'fk_id_tipo_carril');
    }

    public function plantas()
    {
        return $this->hasOne(UsuPlanta::class, 'id_plata', 'fk_id_planta');
    }

    public function plantaReasig()
    {
        return $this->hasOne(UsuPlanta::class, 'id_plata', 'fk_id_plantaReasig');
    }

    public function plantas_destino()
    {
        return $this->hasOne(UsuPlanta::class, 'id_plata', 'fk_id_planta_destino');
    }

    public function compania()
    {
        return $this->hasOne(Compania::class, 'id_compañia', 'fk_compañia');
    }

    public function globalProjectCompany()
    {
        return $this->hasOne(ProjectCompany::class, 'id_Project_Company', 'fk_id_project_Company');
    }

    public function estado()
    {
        return $this->hasOne(estado::class, 'id_estados', 'fk_id_estados');
    }

    public function formula_cdc()
    {
        return $this->hasOne(WbFormulaCentroProduccion::class, 'fk_id_formula_lista', 'fk_id_formula');
    }
}
