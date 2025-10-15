<?php

namespace App\Models\HorometrosTaller;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\usuarios_M;
use App\Models\WbEquipo;
use Carbon\Carbon;
use App\Models\wbTipoEquipo;
class Wb_Indicadores_equipos extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $connection = 'sqlsrv3';
    protected $table = 'Wb_Indicadores_equipos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
   //protected $dateFormat = 'Y-m-d H:i:s.v';
    use \OwenIt\Auditing\Auditable;
    protected $module = 'HorometrosTaller';

     public  function GetTable()
    {
        return $this->table;
    }



    public function generateTags(): array
    {
        return [
            $this->module
        ];
    }

    protected $fillable = [
        'fk_equipment_id',
        'fecha_anterior_horometro',
        'fecha_anterior_kilometraje',
        'fecha_cambio_horometro',
        'fecha_cambio_kilometraje',
        'anterior_horometro',
        'anterior_kilometraje',
        'nuevo_horometro',
        'nuevo_kilometraje',
        'observacion',
        'fk_id_project_Company',
        'estado',
        'created_user',
        'updated_user'
    ];

      protected $casts = [
        'anterior_horometro' => 'decimal:2',
        'anterior_kilometraje' => 'decimal:2',
        'nuevo_horometro' => 'decimal:2',
        'nuevo_kilometraje' => 'decimal:2',
        'estado' => 'boolean'
    ];


      public function usuario_creador()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'created_user')
            ->select(['id_usuarios', 'Nombre', 'Apellido']);
    }

    public function equipos()
    {
        return $this->hasOne(WbEquipo::class, 'id', 'fk_equipment_id')
        ->select(['id','equiment_id', 'descripcion', 'fk_compania','modelo','marca','fk_id_tipo_equipo','placa']);
    }

     public function tipo_equipo()
    {
        return $this->hasOneThrough(
            wbTipoEquipo::class, // Modelo final al que quieres acceder (Tipo equipo)
            WbEquipo::class, // Modelo intermedio (WbEquipo)
            'id', // Clave primaria en el modelo intermedio (WbEquipo)
            'id_tipo_equipo', // Clave foránea en el modelo final (Compania)
            'fk_equipment_id', // Clave foránea en el modelo actual (Sy_Parte_diario)
            'fk_id_tipo_equipo' // Clave foránea en el modelo intermedio (WbEquipo)
        )->select(['id_tipo_equipo','nombre']); //borrar en en el caso que no se necesiten las columnas
    }



}
