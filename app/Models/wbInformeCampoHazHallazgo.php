<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wbInformeCampoHazHallazgo extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_informe_campo_has_hallazgo';
    protected $primaryKey = 'id_informe_campo_has_hallazgo';
    public $timestamps = false;
    public $incrementing = true;

    public  function Hallazgos()
    {
        return $this->belongsTo(wbHallazgo::class, 'fk_id_hallazgo');
    }
}
