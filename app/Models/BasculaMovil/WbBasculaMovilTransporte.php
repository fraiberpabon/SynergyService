<?php

namespace App\Models\BasculaMovil;

use App\Models\CostCode;
use App\Models\Equipos\WbEquipo;
use App\Models\Materiales\WbMaterialLista;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbFormulaLista;
use App\Models\WbHitos;
use App\Models\WbAsfaltFormula;
use App\Models\WbTramos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;
use App\Models\Transporte\WbConductores;

class WbBasculaMovilTransporte extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv3';
    protected $table = 'Wb_bascula_movil_transporte';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en servidor 3

    public $module = 'Bascula movil transporte';

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
    public function origenPlanta()
    {
        return $this->belongsTo(UsuPlanta::class, 'fk_id_planta_origen', 'id_plata');
    }

    public function origenTramo()
    {
        return $this->belongsTo(WbTramos::class, 'fk_id_tramo_origen', 'id');
    }

    public function origenHito()
    {
        return $this->belongsTo(WbHitos::class, 'fk_id_hito_origen', 'Id');
    }

    public function destinoPlanta()
    {
        return $this->belongsTo(UsuPlanta::class, 'fk_id_planta_destino', 'id_plata');
    }

    public function destinoTramo()
    {
        return $this->belongsTo(WbTramos::class, 'fk_id_tramo_destino', 'id');
    }

    public function destinoHito()
    {
        return $this->belongsTo(WbHitos::class, 'fk_id_hito_destino', 'Id');
    }

    public function cdcOrigen()
    {
        return $this->belongsTo(CostCode::class, 'fk_id_cost_center_origen', 'CostCode');
    }

    public function cdcDestino()
    {
        return $this->belongsTo(CostCode::class, 'fk_id_cost_center_destino', 'CostCode');
    }

    public function material()
    {
        return $this->belongsTo(WbMaterialLista::class, 'fk_id_material', 'id_material_lista');
    }

    public function formula()
    {
        return $this->belongsTo(WbFormulaLista::class, 'fk_id_formula', 'id_formula_lista');
    }

    public function formulaAsf()
    {
        return $this->belongsTo(WbAsfaltFormula::class, 'fk_id_formula', 'id_asfal_formula');
    }

    public function usuario_creador()
    {
        return $this->belongsTo(usuarios_M::class, 'user_created' ,'id_usuarios' );
    }


    public function usuario_actualizador()
    {
        return $this->belongsTo(usuarios_M::class, 'user_updated' ,'id_usuarios' );
    }

    public function equipo()
    {
        return $this->belongsTo(WbEquipo::class, 'fk_id_equipo');
    }

    public function conductores() {
        return $this->belongsTo(WbConductores::class, 'conductor', 'dni');
    }
}
