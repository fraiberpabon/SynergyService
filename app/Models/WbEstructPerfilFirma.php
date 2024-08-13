<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbEstructPerfilFirma extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table='Wb_Estruc_Perfil_Firmas';
    protected $primaryKey='id_estruc_perfil';
    public $timestamps = false;
    public $incrementing = true;
}
