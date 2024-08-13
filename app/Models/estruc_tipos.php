<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class estruc_tipos extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Estruc_tipos';
    protected $primaryKey='id';
}
