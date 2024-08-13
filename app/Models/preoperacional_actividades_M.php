<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class preoperacional_actividades_M extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Preoperacional_Calificacion';
    protected $primaryKey='id_preoperacional_calificacion';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable=[
       'fk_id_preoperacional'
       ,'fk_liberaciones_actividades'
       ,'calificacion'
       ,'datecreate'
     ];
}
