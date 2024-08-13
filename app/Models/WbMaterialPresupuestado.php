<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbMaterialPresupuestado extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_material_presupuestado';
    public $timestamps = false;
}
