<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class estado extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='estados';
    protected $primaryKey='id_estados';
    public $timestamps = false;
}
