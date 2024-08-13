<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncIndicador extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Sync_indicador';
    protected $primaryKey='id_indicador';
    public $timestamps = false;
    public $incrementing = true;
}
