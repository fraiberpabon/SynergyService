<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wbFotoInformeCampo extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_foto_informe_campo';
    protected $primaryKey='id_foto_informe_ampo';
    public $timestamps = false;
    public $incrementing = true;
}
