<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCode extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='CostCode';
    public $timestamps = false;
}
