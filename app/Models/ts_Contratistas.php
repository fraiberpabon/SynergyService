<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts_Contratistas extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table='Subcontractor';
    protected $primaryKey='SubcontractorID';
    public $incrementing = false;
    public $timestamps = false;
/*
    protected $visible=[
        'SubContractorID',
        'SubcontractorDesc'

    ];*/

    protected $fillable=[
        'SubcontractorDesc'
    ];
}
