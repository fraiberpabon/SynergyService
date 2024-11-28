<?php

namespace App\Models\EquiposLiquidacion;

use App\Models\estado;
use App\Models\usuarios_M;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Contracts\Auditable;

class WbEquiposLiquidacion extends Model implements Auditable
{
    #Implementamos la interfaz Auditable
    use \OwenIt\Auditing\Auditable;

    # Creamos el tags del nombre del modulo
    public $module = 'Liquidacion de equipos';

    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_equipos_liquidacion';
    protected $primaryKey = 'id_equipos_liquidacion';
    public $timestamps = true;

    //protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en pruebas

    # Funcion para obtener el nombre de la tabla
    public function GetTable()
    {
        return $this->table;
    }

    # Funcion para generar los tags de la auditoria
    public function generateTags(): array
    {
        return [
            $this->module
        ];
    }
}
