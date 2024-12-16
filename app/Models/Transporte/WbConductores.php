<?php

namespace App\Models\Transporte;

use App\Models\CostCode;
use App\Models\Equipos\WbEquipo;
use App\Models\Materiales\WbMaterialLista;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbFormulaLista;
use App\Models\WbHitos;
use App\Models\WbSolicitudMateriales;
use App\Models\WbTramos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class WbConductores extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv3';
    protected $table = 'Wb_conductores';
    protected $primaryKey = 'dni';
    public $incrementing = true;
    public $timestamps = true;

    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3

    public $module = 'Conductores';

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
}
