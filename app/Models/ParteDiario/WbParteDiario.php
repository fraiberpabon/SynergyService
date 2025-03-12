<?php

namespace App\Models\ParteDiario;

use App\Models\Equipos\WbEquipo;
use App\Models\Transporte\WbConductores;
use App\Models\Turnos\SyTurnos;
use App\Models\Usuarios\usuarios_M;
use App\Models\WbCompanieProyecto;
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


    public function usuario_creador()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_id_user_created');
    }


    public function equipos()
    {
        return $this->hasOne(WbEquipo::class, 'id', 'fk_equiment_id');
    }

    public function turno()
    {
        return $this->hasOne(SyTurnos::class, 'id_turnos', 'fk_id_seguridad_sitio_turno');
    }

    public function operador()
    {
        return $this->hasOne(WbConductores::class, 'dni', 'fk_matricula_operador');
    }

    public function distribuciones()
    {
        return $this->hasMany(WbDistribucionesParteDiario::class, 'fk_id_parte_diario', 'id_parte_diario');
    }
    
}
