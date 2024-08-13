<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbCentroProduccionHitos extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_CentroProduccion_Hitos';
    protected $primaryKey='id_centroProduccion_hito';
    public $timestamps = false;
}
