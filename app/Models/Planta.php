<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planta extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Plantas';
    protected $primaryKey='id';
    public $timestamps = false;
}
