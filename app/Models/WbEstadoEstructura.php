<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbEstadoEstructura extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_estado_estructura';
    public $timestamps = false;
}
