<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanillaControlConcreto extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='PlanillaControlConcreto';
    protected $primaryKey='id_planilla';
    public $timestamps = false;
}
