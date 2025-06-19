<?php

namespace App\Models\Equipos;

use App\Models\Compania;
use App\Models\SyncRelacionVehiculoPesos;
use App\Models\ParteDiario\WbParteDiario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

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
        return $this->hasOne(WbParteDiario::class, 'fk_equiment_id', 'id')
            ->where('estado', 1)
            ->whereNotNull('horometro_final')
            ->ofMany([
                'fecha_registro' => 'max',
                'horometro_final' => 'max',
            ], function ($query) {
                $query->where('estado', 1)
                    ->whereNotNull('horometro_final');
            })
            ->select([
                'Sy_Parte_diario.id_parte_diario', // Especifica la tabla para la columna id_parte_diario
                'Sy_Parte_diario.fecha_registro', // Especifica la tabla para la columna fecha_registro
                'Sy_Parte_diario.fecha_creacion_registro', // Especifica la tabla para la columna fecha_creacion_registro
                'Sy_Parte_diario.horometro_final', // Especifica la tabla para la columna horometro_final
                'Sy_Parte_diario.fk_equiment_id', // Especifica la tabla para la columna fk_equiment_id
            ]);
    }



    public function parte_diario_kilometraje()
    {
        return $this->hasOne(WbParteDiario::class, 'fk_equiment_id', 'id')
            ->where('estado', 1)
            ->whereNotNull('kilometraje_final')
            ->ofMany([
                'fecha_registro' => 'max',
                'kilometraje_final' => 'max',
            ], function ($query) {
                $query->where('estado', 1)
                    ->whereNotNull('kilometraje_final');
            })
            ->select([
                'Sy_Parte_diario.id_parte_diario',
                'Sy_Parte_diario.fecha_registro',
                'Sy_Parte_diario.fecha_creacion_registro',
                'Sy_Parte_diario.fk_equiment_id',
                'Sy_Parte_diario.kilometraje_final',
            ]);
    }
}
