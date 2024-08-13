<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncJobs extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table='Jobs';
    protected $primaryKey='JobId';
    public $timestamps = false;
    public $incrementing = true;
}
