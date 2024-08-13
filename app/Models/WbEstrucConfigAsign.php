<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbEstrucConfigAsign extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Estruc_Config_Asign';
    protected $primaryKey='id_Estruc_Config_Asign';
    public $timestamps = false;
    public $incrementing = true;
}
