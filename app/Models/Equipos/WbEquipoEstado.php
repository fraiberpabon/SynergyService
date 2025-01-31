<?php

namespace App\Models\Equipos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class WbEquipoEstado extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv3';
    protected $table = 'Wb_equipo_estado';
    protected $primaryKey = 'id';
    /* public $incrementing = true;
    public $timestamps = true; */

    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar en produccion

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'fk_id_project_Company'
    ];

    public $module = 'Equipos Estado';

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
}
