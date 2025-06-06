<?php

namespace App\Models\ParteDiario;

use App\Models\Compania;
use App\Models\Equipos\WbEquipo;
use App\Models\Equipos\wbTipoEquipo;
use App\Models\Transporte\WbConductores;
use App\Models\Turnos\SyTurnos;
use App\Models\Usuarios\usuarios_M;
use App\Models\WbCompanieProyecto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class WbParteDiario extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $connection = 'sqlsrv3';
    protected $table = 'Sy_Parte_diario';
    protected $primaryKey = 'id_parte_diario';
    public $incrementing = true;
    public $timestamps = true;
    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3
    public $module = 'wbParteDiario';

    public function usuario_creador()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_id_user_created');
    }

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
        return $this->hasMany(WbDistribucionesParteDiario::class, 'fk_id_parte_diario', 'id_parte_diario')
            ->where('estado', 1);
    }


     public function distribucionesGroupByFkIdInterrupcion()
    {
        return $this->hasMany(WbDistribucionesParteDiario::class, 'fk_id_parte_diario', 'id_parte_diario')
        ->where('estado', 1)
        ->whereNotNull('fk_id_interrupcion')
        ->select(
                'fk_id_parte_diario',
                'fk_id_interrupcion',
                DB::raw('SUM(hr_trabajo) as hr_trabajo')
            )
          ->groupBy(['fk_id_parte_diario', 'fk_id_interrupcion']);

    }


     public function distribucionesGroupByFkCentroCosto()
    {
        return $this->hasMany(WbDistribucionesParteDiario::class, 'fk_id_parte_diario', 'id_parte_diario')
        ->where('estado', 1)
        ->whereNotNull('fk_id_centro_costo')
        ->select(
                'fk_id_parte_diario',
                'fk_id_centro_costo',
                'descripcion_trabajo',
                DB::raw('SUM(hr_trabajo) as hr_trabajo')
            )
          ->groupBy(['fk_id_parte_diario', 'descripcion_trabajo', 'fk_id_centro_costo']);
    }



    public function compania()
    {
        return $this->hasOneThrough(
            Compania::class, // Modelo final al que quieres acceder (Compania)
            WbEquipo::class, // Modelo intermedio (WbEquipo)
            'id', // Clave primaria en el modelo intermedio (WbEquipo)
            'id_compañia', // Clave foránea en el modelo final (Compania)
            'fk_equiment_id', // Clave foránea en el modelo actual (Sy_Parte_diario)
            'fk_compania' // Clave foránea en el modelo intermedio (WbEquipo)
        );
    }





    public function tipo_equipo()
    {
        return $this->hasOneThrough(
            wbTipoEquipo::class, // Modelo final al que quieres acceder (Tipo equipo)
            WbEquipo::class, // Modelo intermedio (WbEquipo)
            'id', // Clave primaria en el modelo intermedio (WbEquipo)
            'id_tipo_equipo', // Clave foránea en el modelo final (Compania)
            'fk_equiment_id', // Clave foránea en el modelo actual (Sy_Parte_diario)
            'fk_id_tipo_equipo' // Clave foránea en el modelo intermedio (WbEquipo)
        );
    }
}

