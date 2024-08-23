<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbTramos extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Tramos';
    protected $primaryKey='id';
    public $timestamps = false;
}
