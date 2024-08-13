<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CnfCostCenter extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='CNFCOSTCENTER';
    protected $primaryKey='COCEIDENTIFICATION';
    public $incrementing = false;
    public $timestamps = false;
}
