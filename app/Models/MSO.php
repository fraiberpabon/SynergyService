<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MSO extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table='MSO';
    protected $primaryKey='MSOID';
    protected $keyType = 'string';
}
