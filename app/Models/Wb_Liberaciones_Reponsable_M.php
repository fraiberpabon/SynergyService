<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wb_Liberaciones_Reponsable_M extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Liberaciones_Reponsable';
    protected $primaryKey='id_liberacion_responsable';
}
