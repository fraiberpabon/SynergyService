<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbLiberacionesActividades extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Liberaciones_Actividades';
    protected $primaryKey='id_liberaciones_actividades';
    public $incrementing = false;
    public $timestamps = false;


}
