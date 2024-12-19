<?php

namespace App\Models\Transporte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class WbConductores extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv3';
    protected $table = 'Wb_conductores';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3

    public $module = 'Conductores';

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
