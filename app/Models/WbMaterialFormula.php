<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbMaterialFormula extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Material_Formula';
    protected $primaryKey='id_material_formula';
    public $timestamps = false;
    public $incrementing = true;
}
