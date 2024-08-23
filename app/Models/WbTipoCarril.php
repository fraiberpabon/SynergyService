<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbTipoCarril extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Tipo_Carril';
    protected $primaryKey='id_tipo_carril';
    public $timestamps = false;
    public $incrementing = true;
}
