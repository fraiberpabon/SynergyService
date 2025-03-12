<?php

namespace App\Models\Turnos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyTurnos extends Model
{
    protected $connection = 'sqlsrv3';
    protected $table='Sy_turno_equipos';
    protected $primaryKey='id_turnos';
    public $timestamps = true;
    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3
}
