<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Asignacion';
    protected $primaryKey='fk_actividad';
    public $timestamps = false;
    public $incrementing = true;

    public function areas(){

        return $this->belongsTo(Area::class,'fk_area','id_Area');
    }
}
