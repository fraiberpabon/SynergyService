<?php

namespace App\Models\Equipos;

use App\Models\WbHitos;
use App\Models\WbTramos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class WbEquipoHorometrosUbicaciones extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_equipos_horometros_ubicaciones';
    protected $primaryKey = 'id_equipos_horometros_ubicaciones';
    /* public $incrementing = true;
    public $timestamps = true; */

    public $module = 'Equipos Horometros Ubicaciones';

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

    /**
     * Se excluye los campos de imagenes
     */
    public function getAuditExclude(): array
    {
        return [
            'horometro_foto',
        ];
    }

    // relacion con la tabla tramos
    public function tramo() {
        return $this->hasOne(WbTramos::class,'Id_Tramo' ,'fk_id_tramo');
    }

    // relacion con la tabla hitos
    public function hito() {
        return $this->hasOne(WbHitos::class,'Id_Hitos', 'fk_id_hito');
    }
}
