<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbMaterialCapa extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Material_Capa';
    protected $primaryKey='id_material_capa';
    public $timestamps = false;
    public $incrementing = true;

     public function material()
    {
        return $this->belongsTo(WbMaterialLista::class,'fk_id_material_lista');
    }
}
