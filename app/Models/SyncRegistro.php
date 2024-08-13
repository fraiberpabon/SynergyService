<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncRegistro extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='sync_registros';
    protected $primaryKey='id';
    public $timestamps = false;
    public $incrementing = true;
}
