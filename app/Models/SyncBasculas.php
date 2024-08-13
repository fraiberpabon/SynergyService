<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncBasculas extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='sync_basculas';
    protected $primaryKey='id';
    public $timestamps = false;
}
