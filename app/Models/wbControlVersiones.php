<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wbControlVersiones extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='ControlVersiones';
    //protected $primaryKey='id_htrUsuarios';
    public $incrementing = false;
    public $timestamps = false;
}