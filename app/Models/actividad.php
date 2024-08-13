<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class actividad extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Actividades';
    protected $primaryKey='id_Actividad';
    public $timestamps = false;

}
