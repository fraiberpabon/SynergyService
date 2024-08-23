<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbTipoCalzada extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Tipo_Calzada';
    protected $primaryKey='id_tipo_calzada';
    public $timestamps = false;
    public $incrementing = true;
}
