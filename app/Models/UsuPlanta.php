<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuPlanta extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='usuPlanta';
    protected $primaryKey='id_plata';
    public $timestamps = false;
    public $incrementing = true;
}
