<?php

namespace App\Models;

use App\Models\Materiales\WbMaterialLista;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbMaterialCentroProduccion extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Material_CentroProduccion';
    protected $primaryKey='id_material_centroProduccion';
    public $timestamps = false;
    public $incrementing = true;

     public function centro()
    {
        return $this->belongsTo(UsuPlanta::class,'fk_id_planta');
    }

     public function material()
    {
        return $this->belongsTo(WbMaterialLista::class,'fk_id_material_lista');
    }
}
