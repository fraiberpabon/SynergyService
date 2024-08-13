<?php

namespace App\Models\Roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbSeguriRolesPermiso extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_Seguri_Roles_Permisos';
    //protected $primaryKey='id_roles_permisos';
    protected $primaryKey = 'fk_id_Rol';
    public $timestamps = false;
}
