<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liberaciones extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Liberaciones';
    protected $primaryKey='fk_solicitud';
    public $timestamps = false;
}
