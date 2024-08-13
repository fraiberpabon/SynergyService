<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HtrUsuarios extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='htrlUsuarios';
    protected $primaryKey='id_htrUsuarios';
    public $incrementing = false;
    public $timestamps = false;
}
