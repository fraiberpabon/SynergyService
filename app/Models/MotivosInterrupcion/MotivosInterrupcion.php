<?php

namespace App\Models\MotivosInterrupcion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MotivosInterrupcion extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv3';
    protected $table = 'Sy_Motivos_Interrupcion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3

}
