<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class solicitudConcreto extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $connection = 'sqlsrv2';
    protected $table = 'SolicitudConcreto';
    protected $primaryKey = 'id_solicitud';
    public $incrementing = true;
    public $timestamps = false;
    public $module = 'Solicitud Concreto';
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
