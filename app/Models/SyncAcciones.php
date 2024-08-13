<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncAcciones extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='sync_acciones';
    protected $primaryKey='id';
    public $timestamps = false;
}
