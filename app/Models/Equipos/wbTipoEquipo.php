<?php

namespace App\Models\Equipos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wbTipoEquipo extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_tipo_equipo';
    protected $primaryKey='id_tipo_equipo';
    public $timestamps = false;
    public $incrementing = true;

    //protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en pruebas
}
