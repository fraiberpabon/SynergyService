<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbFormulaLista extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    public $timestamps = false;
    protected $table='Wb_Formula_Lista';
    protected $primaryKey='id_formula_lista';
}
