<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Formula';
    Protected  $primaryKey='id';
    public $timestamps = false;
}