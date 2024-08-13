<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbLicenciaAmbiental extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_licencia_ambiental';
    public $timestamps = false;
}
