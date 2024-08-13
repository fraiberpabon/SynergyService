<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncRelacionVehiculoPeso extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='sync_relacion_VehiculosPesos';
    public $timestamps = false;
    public $incrementing = true;
}
