<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbLiberacionesFormatosAct extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Liberaciones_Formatos_Act';
    protected $primaryKey='id_liberaciones_formatos_act';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable=[
        'id_liberaciones_formatos_act'
       ,'fk_id_liberaciones_actividades'
       ,'fk_id_liberaciones_formatos'
       ,'datecreate'
       ,'estado'
       ,'userCreator'
     ];

}
