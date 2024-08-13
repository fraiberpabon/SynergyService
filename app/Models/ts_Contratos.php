<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts_Contratos extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table='SubcontractorTrans';
    protected $primaryKey='TXCounter';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable=[
        'ContractID',
        'SubContractorID'
    ];
}
