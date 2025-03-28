<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbAsfaltFormula extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_Asfal_Formula';
    protected $primaryKey = 'id_asfal_formula';
    public $timestamps = false;

    public function scopeColsFormula($query)
    {
        return $query->select(
            'id_asfal_formula',
            'id_asfal_formula as identificador',
            'asfalt_formula as nombre',
            'mso_id as mso'
        );
    }

    public function transports()
    {
        return $this->morphOne(WbTransporteRegistro::class, 'formulas');
    }
}
