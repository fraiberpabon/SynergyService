<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbFormulaCapa extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    public $timestamps = false;
    protected $table='Wb_Formula_Capa';
    protected $primaryKey='id_formula_capa';

         public function formula()
        {
            return $this->belongsTo(WbFormulaLista::class,'fk_id_formula_lista');
        }
}
