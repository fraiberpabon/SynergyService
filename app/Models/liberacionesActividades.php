<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class liberacionesActividades extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $connection = 'sqlsrv2';
    protected $table = 'liberaciones_actividades';
    public $timestamps = false;
    protected $fillable = [
        'fk_estado',
        'fk_solicitud',
    ];

    public $module = 'Liberaciones Actividades';

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
