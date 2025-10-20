<?php

namespace App\Models\Equipos;

use App\Models\Compania;
use App\Models\SyncRelacionVehiculoPesos;
use App\Models\ParteDiario\WbParteDiario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Area;
use Illuminate\Support\Arr;
use App\Models\HorometrosTaller\Wb_Indicadores_equipos;

class WbEquipo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_equipos';
    protected $primaryKey = 'id';
    /* public $incrementing = true;
    public $timestamps = true; */

    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar en produccion

    public $module = 'Equipos';

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

    // Relacion de la tabla Wb_Equipos con Wb_tipo_equipo
    public function tipo_equipo()
    {
        return $this->hasOne(wbTipoEquipo::class, 'id_tipo_equipo', 'fk_id_tipo_equipo');
    }

    // Relación con la tabla Compañia
    public function compania()
    {
        return $this->hasOne(Compania::class, 'id_compañia', 'fk_compania');
    }



    // Relacion con la tabla sync_relacion_vehiculosPesos
    public function vehiculos_pesos()
    {
        return $this->hasOne(SyncRelacionVehiculoPesos::class, 'vehiculo', 'equiment_id');
    }

    // Relacion con la tabla SyHorometrosUbicaciones para horometros
    public function horometros()
    {
        return $this->hasOne(WbEquipoHorometrosUbicaciones::class, 'fk_id_equipo', 'id')
            ->whereNotNull('horometro')->latest(\DB::raw("CAST(REPLACE(fecha_registro, ' ', 'T') as datetime)"));
    }

    // Relacion con la tabla SyHorometrosUbicaciones para ubicaciones
    public function ubicacion()
    {
        return $this->hasOne(WbEquipoHorometrosUbicaciones::class, 'fk_id_equipo', 'id')
            ->latest(\DB::raw("CAST(REPLACE(fecha_registro, ' ', 'T') as datetime)"));
    }


    //Relacion con la tabla de syParteDiario

    // public function parte_diario()
    // {
    //     return $this->hasOne(WbParteDiario::class, 'fk_equiment_id', 'id')
    //         ->orderByDesc(\DB::raw("CAST(REPLACE(fecha_registro, ' ', 'T') as datetime)")) // Ordena por fecha_creacion_registro de forma descendente
    //         ->max('horometro_final') // Luego ordena por horometro_final de forma descendente
    //         ->limit(1); // Limita el resultado a solo un registro
    // }




    public function parte_diario()
    {
        // Optimizado para usar el método 'ofMany' de Laravel, ideal para relaciones "has one of many".
        // Esto es más eficiente, especialmente en cargas ansiosas (eager loading).
        return $this->hasOne(
            WbParteDiario::class,
            "fk_equiment_id",
            "id",
        )->ofMany(
            [
                "fecha_registro" => "max",
                "horometro_final" => "max",
            ],
            function ($query) {
                $query->where("estado", 1)->whereNotNull("horometro_final");
            },
        );
    }


    public function horometro_anterior($fecha_limite = null)
    {
        // Optimizado para usar el método 'ofMany' de Laravel, ideal para relaciones "has one of many".
        // Esto es más eficiente, especialmente en cargas ansiosas (eager loading).
        return $this->hasOne(
            WbParteDiario::class,
            "fk_equiment_id",
            "id",
        )->ofMany(
            [
                "fecha_registro" => "max",
                "horometro_final" => "max",
            ],
            function ($query) use ($fecha_limite) {
                $query->where("estado", 1)->whereNotNull("horometro_final");

                if ($fecha_limite) {
                    $query->where("fecha_registro", "<", $fecha_limite);
                }
            },
        );
    }



    public function parte_diario_kilometraje()
    {
        // Optimizado para usar el método 'ofMany' de Laravel, que es más eficiente
        // para relaciones "has one of many", especialmente en cargas ansiosas (eager loading).
        return $this->hasOne(
            WbParteDiario::class,
            "fk_equiment_id",
            "id",
        )->ofMany(
            [
                "fecha_registro" => "max",
                "kilometraje_final" => "max",
            ],
            function ($query) {
                $query->where("estado", 1)->whereNotNull("kilometraje_final");
            },
        );
    }


    public function kilometraje_anterior($fecha_limite = null)
    {
        // Optimizado para usar el método 'ofMany' de Laravel, ideal para relaciones "has one of many".
        // Esto es más eficiente, especialmente en cargas ansiosas (eager loading).
        return $this->hasOne(
            WbParteDiario::class,
            "fk_equiment_id",
            "id",
        )->ofMany(
            [
                "fecha_registro" => "max",
                "kilometraje_final" => "max",
            ],
            function ($query) use ($fecha_limite) {
                $query->where("estado", 1)->whereNotNull("kilometraje_final");

                if ($fecha_limite) {
                    $query->where("fecha_registro", "<", $fecha_limite);
                }
            },
        );
    }




    /**
     * Obtiene  el ultimo horometro de Wb_Indicadores_equipos
     */
    public function cambio_horometro()
    {
        return $this->hasOne(
            Wb_Indicadores_equipos::class,
            "fk_equipment_id",
            "id",
        )
            ->ofMany(
                ["fecha_cambio" => "max", "nuevo_horometro" => "max"],
                function ($query) {
                    $query->where("estado", 1)->whereNotNull("nuevo_horometro");
                },
            )
            ->select([
                "Wb_Indicadores_equipos.fk_equipment_id",
                "Wb_Indicadores_equipos.anterior_horometro",
                "Wb_Indicadores_equipos.nuevo_horometro",
                "Wb_Indicadores_equipos.fecha_cambio",
            ]);
    }

    /**
     * Obtiene  el ultimo kilometraje  de Wb_Indicadores_equipos
     */
    public function cambio_kilometraje()
    {
        return $this->hasOne(
            Wb_Indicadores_equipos::class,
            "fk_equipment_id",
            "id",
        )->ofMany(
            ["fecha_cambio" => "max", "nuevo_kilometraje" => "max"],
            function ($query) {
                $query->where("estado", 1)->whereNotNull("nuevo_kilometraje");
            },
        );
    }


    //Relacion con area

    public function area()
    {
        return $this->hasOne(Area::class, 'id_area', 'fk_id_area');
    }
}
