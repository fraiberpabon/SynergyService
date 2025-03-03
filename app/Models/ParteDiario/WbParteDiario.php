<?php

namespace App\Models\ParteDiario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class WbParteDiario extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv3';
    protected $table = 'Sy_Parte_diario';
    protected $primaryKey = 'id_parte_diario';
    public $incrementing = true;
    public $timestamps = true;
    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3

}
