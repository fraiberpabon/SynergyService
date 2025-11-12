<?php

namespace App\Models\Modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class WbModulos extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    protected $connection = 'sqlsrv3';
    protected $table = 'Wb_modulos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en pruebas
    public $module = 'Modulos';

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
