<?php

namespace App\Models\Usuarios;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wb_password_hash extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_password';
    protected $primaryKey='id';
    public $incrementing = true;
    public $timestamps = false;
}
