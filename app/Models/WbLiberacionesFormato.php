<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbLiberacionesFormato extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Liberaciones_Formatos';
    protected $primaryKey='id_liberaciones_formatos';
    public $incrementing = false;
    public $timestamps = false;
}
