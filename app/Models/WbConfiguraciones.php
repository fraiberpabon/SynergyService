<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbConfiguraciones extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_configuraciones';
    protected $primaryKey='id';
    public $timestamps = false;
    public $incrementing = true;
}
