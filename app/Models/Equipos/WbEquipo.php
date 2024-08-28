<?php

namespace App\Models\Equipos;

use App\Models\Compania;
use App\Models\SyncRelacionVehiculoPeso;
use App\Models\SyncRelacionVehiculoPesos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class WbEquipo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_equipos';
    protected $primaryKey = 'id';
    /* public $incrementing = true;
    public $timestamps = true; */

    public $module = 'Equipos';

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

    // Relacion de la tabla Wb_Equipos con Wb_tipo_equipo
    public function tipo_equipo() {
        return $this->hasOne(wbTipoEquipo::class, 'id_tipo_equipo', 'fk_id_tipo_equipo');
    }

    // Relación con la tabla Compañia
    public function compania()
    {
        return $this->hasOne(Compania::class, 'id_compañia', 'fk_compania');
    }

    // Relacion con la tabla sync_relacion_vehiculosPesos
    public function vehiculos_pesos(){
        return $this->hasOne(SyncRelacionVehiculoPesos::class, 'vehiculo', 'equiment_id');
    }

    /* // Relacion con la tabla SyHorometrosUbicaciones para horometros
    public function horometros(){
        return $this->hasOne(SyHorometrosUbicaciones::class, 'vehiculo', 'equiment_id')->latest('created_at');
    }

    // Relacion con la tabla SyHorometrosUbicaciones para ubicaciones
    public function ubicacion(){
        return $this->hasOne(SyHorometrosUbicaciones::class, 'vehiculo', 'equiment_id');
    } */
}
