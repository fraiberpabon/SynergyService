<?php

namespace App\Models;

use App\Models\Equipos\WbEquipo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class Compania extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'compañia';
    protected $primaryKey = 'id_compañia';
    public $timestamps = false;

    public function compania_equipos()
    {
        return $this->hasOne(WbEquipo::class, 'fk_compania', 'id_compañia');
    }
}
