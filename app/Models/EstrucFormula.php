<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstrucFormula extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Estruc_formula';
    protected $primaryKey='id';
    public $timestamps = false;
}
