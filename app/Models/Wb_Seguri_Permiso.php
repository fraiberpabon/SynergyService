<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wb_Seguri_Permiso extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Seguri_Permisos';
    protected $primaryKey='id_permiso';
    public $timestamps = false;
}
