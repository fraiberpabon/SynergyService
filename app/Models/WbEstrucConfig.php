<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbEstrucConfig extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    public $timestamps = false;
    protected $table='Wb_Estruc_Config';
    protected $primaryKey='id_estruc_config';
}
