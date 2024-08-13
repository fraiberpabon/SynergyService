<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstructuraTipoElemento extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Estruc_tipo_elemento';
    protected $primaryKey='id';
    public $timestamps = false;
}
