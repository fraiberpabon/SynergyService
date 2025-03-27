<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'Formula';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function diseno()
    {
        return $this->hasOne(TipoMezcla::class, 'Id', 'fk_tipoMezcla');
    }

    public function transports()
    {
        return $this->morphOne(WbTransporteRegistro::class, 'formulas');
    }
}
