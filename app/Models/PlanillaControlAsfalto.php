<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanillaControlAsfalto extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='PlanillaControlAsfalto';
    protected $primaryKey='id_planilla';
    public $timestamps = false;
}
