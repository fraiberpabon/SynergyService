<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wbHallazgo extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_hallazgo';
    protected $primaryKey='id_hallazgo';
    public $timestamps = false;
    public $incrementing = true;
}
