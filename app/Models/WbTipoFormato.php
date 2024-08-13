<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbTipoFormato extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_tipo_formato';
    protected $primaryKey='id_tipo_formato';
    public $timestamps = false;
}
