<?php

namespace App\Models\Materiales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbMaterialLista extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Material_Lista';
    protected $primaryKey='id_material_lista';
    public $timestamps = false;
    public $incrementing = true;

    public function tipo_material() {
        return $this->hasOne(WbMaterialTipos::class, 'id_material_tipo', 'fk_id_material_tipo');
    }
}
