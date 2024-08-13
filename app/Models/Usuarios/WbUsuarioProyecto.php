<?php

namespace App\Models\Usuarios;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Roles\WbSeguriRolesPermiso;

class WbUsuarioProyecto extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_Usuario_Proyecto';
    protected $primaryKey = 'fk_usuario';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'fk_usuario',
        'fk_rol',
        'fk_compañia'
    ];

    // Relación con la tabla Wb_Seguri_Roles_Permisos
    public function rolesPermisos()
    {
        return $this->hasOne(WbSeguriRolesPermiso::class, 'fk_id_Rol', 'fk_rol');
    }

    // Relación con la tabla Usuarios_M
    public function usuario()
    {
        return $this->belongsTo(usuarios_M::class, 'fk_usuario', 'id_usuarios');
    }
}
