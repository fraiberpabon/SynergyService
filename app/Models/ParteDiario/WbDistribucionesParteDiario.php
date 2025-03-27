<?php

namespace App\Models\ParteDiario;
use App\Models\CnfCostCenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class WbDistribucionesParteDiario extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv3';
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
            CnfCostCenter::class,'COSYNCCODE','fk_id_centro_costo');
    }
}
