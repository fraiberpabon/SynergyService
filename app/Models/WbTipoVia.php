<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbTipoVia extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Tipo_Via';
    protected $primaryKey='id_tipo_via';
    public $timestamps = false;
    public $incrementing = true;
}
