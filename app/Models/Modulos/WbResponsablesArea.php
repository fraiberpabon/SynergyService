<?php

namespace App\Models\Modulos;

use App\Models\Area;
use App\Models\usuarios_M;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class WbResponsablesArea extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    protected $connection = 'sqlsrv3';
    protected $table = 'Wb_responsables_area';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en pruebas
    public $module = 'Responsables area';

    public function getTable()
    {
        return $this->table;
    }

    public function generateTags(): array
    {
        return [
            $this->module
        ];
    }
    /* Inicio relaciones */
    public function modulo()
    {
        return $this->hasOne(WbModulos::class, 'id', 'fk_id_modulo');
    }

    public function usuario_responsable()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_id_usuario')
         ->select(['id_usuarios','Nombre','Apellido','Correo','matricula','celular']);
    }

     public function usuario_responsable_firma()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_id_usuario')
         ->select(['id_usuarios']);
    }


    public function usuario_created()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'created_user')
         ->select(['id_usuarios','Nombre','Apellido']);
    }

    public function usuario_updated()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'updated_user')
        ->select(['id_usuarios','Nombre','Apellido']);
    }

    public function area()
    {
        return $this->hasOne(Area::class, 'id_area', 'fk_id_area');
    }

    /**
     * - area relacion con Area
     * - usuario_created extrae el usuario creador
     * - usuario_updated extrae el usuario que actualizo
     * - usuario_responsable extrae el usuario que es responsable del area
     * - modulo extrae el modulo que es responsable ese usuario
     */

    /**Fin relaciones */


}
