<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class Wb_Solicitud_Liberaciones_Act extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    public $timestamps = false;
    public $incrementing = false;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_Solicitud_Liberaciones_Act';
    protected $primaryKey = 'id_solicitud_liberaciones_act';

    protected $fillable = [
        'calificacion',
        'estado',
        'nota',
        'fk_id_usuario',
        'dateUpdate', 'ubicacionGPS'
    ];


    public $module = 'Solicitud Liberaciones Actividades';

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

    public function responsable(){
        return $this->hasMany(Wb_Liberaciones_Reponsable_M::class, 'fk_id_liberaciones_actividades', 'fk_id_liberaciones_actividades');
    }

    public function actividad(){
        return $this->hasOne(WbLiberacionesActividades::class, 'id_liberaciones_actividades', 'fk_id_liberaciones_actividades');
    }
}
