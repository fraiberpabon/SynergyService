<?php

namespace App\Models\Transporte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class WbTransporteRegistro extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv3';
    protected $table = 'Wb_transporte_registro';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en pruebas

    public $module = 'Transporte registro';

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
