<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbHitos extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    public $timestamps = false;
    protected $table='Wb_Hitos';
    protected $primaryKey='Id';
}
