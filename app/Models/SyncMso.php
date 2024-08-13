<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncMso extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table='MSO';
    protected $primaryKey='MSOID';
    public $timestamps = false;
    public $incrementing = true;
}
