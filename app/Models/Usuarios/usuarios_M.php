<?php

namespace App\Models\Usuarios;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;

class usuarios_M extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    protected $connection = 'sqlsrv2';
    protected $table = 'usuarioss';
    protected $primaryKey = 'id_usuarios';
    public $incrementing = true;
    public $timestamps = false;

    public $module = 'Gestion Usuarios';

    public function getTable()
    {
        return $this->table;
    }

    public function generateTags(): array
    {
        return [
            $this->module,
        ];
    }

    protected $fillable = [
        'usuario', 'contraseña', 'matricula', 'imeil', 'change_pass'
    ];

    protected $hidden = [
        'contraseña',
    ];

    // Relacion de la tabla Wb_usuarios con Sy_Users
    public function synergyUsers() {
        return $this->hasOne(Sy_usuarios::class, 'fk_wb_id_usuarios', 'id_usuarios');
    }

    // Relación con la tabla Wb_Usuario_proyecto
    public function usuarioProyecto()
    {
        return $this->hasMany(WbUsuarioProyecto::class, 'fk_usuario', 'id_usuarios');
    }
}
