<?php

namespace App\Models\Transporte;

use App\Models\CostCode;
use App\Models\Equipos\WbEquipo;
use App\Models\Materiales\WbMaterialLista;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbFormulaLista;
use App\Models\WbHitos;
use App\Models\WbSolicitudMateriales;
use App\Models\WbTramos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3

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

    /**** Relaciones ****/
    public function solicitud()
    {
        return $this->hasOne(WbSolicitudMateriales::class, 'id_solicitud_Materiales', 'fk_id_solicitud');
    }

    public function origenPlanta()
    {
        return $this->belongsTo(UsuPlanta::class, 'fk_id_planta_origen', 'id_plata');
    }

    public function origenTramo()
    {
        return $this->belongsTo(WbTramos::class, 'fk_id_tramo_origen', 'Id_Tramo');
    }

    public function origenHito()
    {
        return $this->belongsTo(WbHitos::class, 'fk_id_hito_origen', 'Id_Hitos');
    }

    public function origenTramoId()
    {
        return $this->belongsTo(WbTramos::class, 'id_tramo_origen', 'id');
    }

    public function origenHitoId()
    {
        return $this->belongsTo(WbHitos::class, 'id_hito_origen', 'Id');
    }

    public function destinoPlanta()
    {
        return $this->belongsTo(UsuPlanta::class, 'fk_id_planta_destino', 'id_plata');
    }

    public function destinoTramo()
    {
        return $this->belongsTo(WbTramos::class, 'fk_id_tramo_destino', 'Id_Tramo');
    }

    public function destinoHito()
    {
        return $this->belongsTo(WbHitos::class, 'fk_id_hito_destino', 'Id_Hitos');
    }

    public function destinoTramoId()
    {
        return $this->belongsTo(WbTramos::class, 'id_tramo_destino', 'id');
    }

    public function destinoHitoId()
    {
        return $this->belongsTo(WbHitos::class, 'id_hito_destino', 'Id');
    }

    public function cdc()
    {
        return $this->belongsTo(CostCode::class, 'fk_id_cost_center', 'CostCode');
    }

    public function material()
    {
        return $this->belongsTo(WbMaterialLista::class, 'fk_id_material', 'id_material_lista');
    }

    public function formula()
    {
        return $this->belongsTo(WbFormulaLista::class, 'fk_id_formula', 'id_formula_lista');
    }

    public function usuario_created()
    {
        return $this->belongsTo(usuarios_M::class, 'user_created');
    }

    public function usuario_updated()
    {
        return $this->belongsTo(usuarios_M::class, 'user_updated');
    }

    public function equipo()
    {
        return $this->belongsTo(WbEquipo::class, 'fk_id_equipo');
    }

    /* public function chofer() {
        return $this->belongsTo(WbSolicitudMateriales::class, 'id_solicitud_Materiales', 'chofer');
    } */
}
