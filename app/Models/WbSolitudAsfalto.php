<?php

namespace App\Models;

use App\Models\Usuarios\usuarios_M;
use App\Models\Transporte\WbTransporteRegistro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class WbSolitudAsfalto extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'SolitudAsfalto';
    protected $primaryKey = 'id_solicitudAsf';
    public $timestamps = false;

    public $module = 'Solicitud_Asfalto';

    public function getTable()
    {
        return $this->table;
    }

    public function generateTags(): array
    {
        return [
            $this->module
        ];
    }

    public function usuario()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_id_usuario');
    }

    public function tipoVia()
    {
        return $this->hasOne(WbTipoVia::class, 'Via', 'calzada');
    }

    public function tramos()
    {
        return $this->hasOne(WbTramos::class, 'Id_Tramo', 'tramo');
    }

    public function hitos()
    {
        return $this->hasOne(WbHitos::class, 'Id_Hitos', 'hito');
    }

    public function plantas()
    {
        return $this->hasOne(UsuPlanta::class, 'NombrePlanta', 'CompañiaDestino');
    }

    public function cost_code() {
        return $this->hasOne(CnfCostCenter::class, 'COSYNCCODE', 'CostCode');
    }

    public function compania()
    {
        return $this->hasOne(Compania::class, 'id_compañia', 'fk_compañia');
    }

    public function globalProjectCompany()
    {
        return $this->hasOne(ProjectCompany::class, 'id_Project_Company', 'fk_id_project_Company');
    }

    public function formula_asf()
    {
        return $this->hasOne(WbAsfaltFormula::class, 'asfalt_formula', 'formula');
    }

    public function transporte() {
        return $this->hasMany(WbTransporteRegistro::class,  'fk_id_solicitud', 'id_solicitudAsf',);
    }

    public function transports() {
        return $this->morphOne(WbTransporteRegistro::class, 'solicitudes');
    }
}
