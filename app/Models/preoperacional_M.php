<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class preoperacional_M extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Preoperacional';
    protected $primaryKey='id_preoperacional';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable=[
        'preoperacional_auto'
       ,'id_preoperacional'
       ,'fk_equipamentID'
       ,'tipoVehiculo'
       ,'fecha'
       ,'datecreate'
       ,'turno'
       ,'horometro'
       ,'odometro'
       ,'preoperacional'
       ,'observacion'
       ,'fk_id_usuario'
       ,'estado'
       ,'operativo'
       ,'fk_id_project_Company'
       ,'fk_compañia'
       ,'fk_id_liberaciones_formatos'
     ];
}
