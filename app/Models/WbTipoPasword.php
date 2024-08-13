<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbTipoPasword extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Tipo_Password';
    protected $primaryKey='id';
    public $timestamps = false;
    public $incrementing = true;
}
