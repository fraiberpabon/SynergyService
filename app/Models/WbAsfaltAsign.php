<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbAsfaltAsign extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Asfalt_Asig';
    protected $primaryKey='id_asfalt_asig';
    public $timestamps = false;
}
