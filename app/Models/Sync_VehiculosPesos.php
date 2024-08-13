<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sync_VehiculosPesos extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='sync_relacion_VehiculosPesos';
    protected $primaryKey='vehiculo';
    public $incrementing = false;
    public $timestamps = false;
}
