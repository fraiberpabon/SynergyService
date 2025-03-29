<?php

namespace App\Models;

use App\Models\Transporte\WbTransporteRegistro;
use App\Models\Usuarios\usuarios_M;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class solicitudConcreto extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $connection = 'sqlsrv2';
    protected $table = 'SolicitudConcreto';
    protected $primaryKey = 'id_solicitud';
    public $incrementing = true;
    public $timestamps = false;
    public $module = 'Solicitud Concreto';
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
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_usuario');
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
        return $this->hasOne(UsuPlanta::class, 'NombrePlanta', 'PlantaDestino');
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

    public function formula_concreto()
    {
        return $this->hasOne(Formula::class, 'id', 'fk_id_formula');
    }

    public function transporte() {
        return $this->hasMany(WbTransporteRegistro::class,  'fk_id_solicitud', 'id_solicitud',);
    }

    public function transports() {
        return $this->morphOne(WbTransporteRegistro::class, 'solicitudes');
    }
}
