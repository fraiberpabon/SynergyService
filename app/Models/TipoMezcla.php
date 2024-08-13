<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoMezcla extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='TipoMezcla';
    protected $primaryKey='Id';
    public $timestamps = false;
}
