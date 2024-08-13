<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbConfiguracion extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_configuracion';
    protected $primaryKey='id_configuracion';
    public $timestamps = false;
    public $incrementing = true;
}
