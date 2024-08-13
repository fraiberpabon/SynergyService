<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbEstructFirmas extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Estruc_Firmas';
    protected $primaryKey='id_estruc_firma';
    public $timestamps = false;
}
