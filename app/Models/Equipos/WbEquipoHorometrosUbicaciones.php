<?php

namespace App\Models\Equipos;

use App\Models\Compania;
use App\Models\SyncRelacionVehiculoPeso;
use App\Models\SyncRelacionVehiculoPesos;
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
}
