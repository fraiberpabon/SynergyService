<?php

namespace App\Models\ParteDiario;

use App\Models\CnfCostCenter;
use App\Models\WbCostos\WbCostos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbDistribucionesParteDiario extends  Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $connection = 'sqlsrv3';
    public $module = 'wbDistribucionesParteDiario';
    protected $table = 'Sy_distribuciones_parte_diario';
    protected $primaryKey = 'id_distribuciones';
    public $incrementing = true;
    public $timestamps = true;
    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3


    public function interrupciones()
    {
        return $this->hasOne(WbInterrupciones::class, 'id', 'fk_id_interrupcion');
    }

    public function centro_costo()
    {
        return $this->hasOne(
            WbCostos::class,
            'Codigo',
            'fk_id_centro_costo'
        );
    }


    public function parte_diario(){
        return $this->hasOne(
            WbParteDiario::class,
            'id_parte_diario',
            'fk_id_parte_diario'
        );
    }




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
