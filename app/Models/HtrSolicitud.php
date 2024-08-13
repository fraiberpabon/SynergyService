<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HtrSolicitud extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='htrSolicitud';
    protected $primaryKey='id_htrSolicitud';
    public $incrementing = false;
    public $timestamps = false;



}
