<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbFormulaCentroProduccion extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    public $timestamps = false;
    protected $table='Wb_Formula_CentroProduccion';
    protected $primaryKey='id_formula_centroProduccion';

    public function centro()
    {
        return $this->belongsTo(UsuPlanta::class,'fk_id_planta');
    }

    public function formula()
    {
        return $this->belongsTo(WbFormulaLista::class,'fk_id_formula_lista');
    }
}
