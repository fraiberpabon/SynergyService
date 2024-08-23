<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbTipoCapa extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Tipo_Capa';
    protected $primaryKey='id_tipo_capa';
    public $timestamps = false;
    public $incrementing = true;
}
