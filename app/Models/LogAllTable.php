<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAllTable extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='LogAllTable';
    protected $primaryKey='id_log';
    public $timestamps = false;
}
