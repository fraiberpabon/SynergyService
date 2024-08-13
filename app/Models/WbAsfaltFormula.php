<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbAsfaltFormula extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Asfal_Formula';
    protected $primaryKey='id_asfal_formula';
    public $timestamps = false;
}
