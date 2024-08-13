<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbAccionEstructura extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_accion_estructura';
    public $timestamps = false;
}
