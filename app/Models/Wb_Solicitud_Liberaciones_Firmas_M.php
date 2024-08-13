<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class Wb_Solicitud_Liberaciones_Firmas_M extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    public $timestamps = false;
    public $incrementing = false;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_Solicitud_Liberaciones_Firmas';
    protected $primaryKey = 'id_solicitudes_liberaciones_firmas';

    protected $fillable = [
        'id_solicitudes_liberaciones_firmas', 'fk_id_solicitudes_liberaciones', 'fk_id_area', 'fk_id_usuario', 'nota', 'panoramica', 'estado', 'dateCreate', 'ubicacionGPS'
    ];

    public $module = 'Solicitud Liberaciones Firmas';

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

         public function getAuditExclude(): array
    {
        return [
            'panoramica',
        ];
    }

    public function area()
    {
        return $this->hasOne(Area::class,'id_area','fk_id_area');
    }

    
}
