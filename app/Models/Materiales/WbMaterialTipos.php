<?php

namespace App\Models\Materiales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbMaterialTipos extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Material_Tipos';
    protected $primaryKey='id_material_tipo';
    public $timestamps = false;
}
