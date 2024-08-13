<?php

namespace App\Models;

use App\Http\Resources\usuarios_R;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class wbInformeCampo extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_informe_campo';
    protected $primaryKey = 'id_informe_campo';
    public $timestamps = true;
    public $incrementing = true;
    protected $dateFormat = 'd-m-Y H:i:s.v';
    protected  $updated_at;
    protected  $created_at;

    public $module = 'Informes Campos';



    /**
     * Se excluyen las fotos del registro en base 64
     *   Tanto para crear informes de hallazgos como el cerrar
        esto solo aplica para el log
     */
    public function getAuditExclude(): array
    {
        return [
            'foto_uno',
            'foto_dos',
            'foto_tres',
            'foto_cuatro',
            'foto_cinco',
            'foto_seis',
            'foto_cierre1',
            'foto_cierre2',
        ];
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

    public function tipoCalzada()
    {
        return $this->belongsTo(WbTipoCalzada::class, 'fk_id_tipo_calzada');
    }
    public function tipoEstado()
    {
        return $this->belongsTo(estado::class, 'fk_estado');
    }


    public function tipoUsuario()
    {
        return $this->belongsTo(usuarios_M::class, 'fk_id_usuarios');
    }


    public function tipoUsuarioAct()
    {
        return $this->belongsTo(usuarios_M::class, 'fk_user_update');
    }

    public function tipoHallazgo(): HasManyThrough
    {
        return $this->hasManyThrough(
            wbHallazgo::class,
            wbInformeCampoHazHallazgo::class,
            'fk_id_informe_campo',
            'id_hallazgo',
            'id_informe_campo',
            'fk_id_hallazgo'
        );
    }

    public function HallazgosHasHallazgos()
    {
        return $this->belongsTo(wbInformeCampoHazHallazgo::class, 'fk_id_informe_campo');
    }

    public function concatenarHallazgos()
    {
        // Obtén el ID del informe de campo
        $idInformeCampo = $this->id_informe_campo;

        // Usa las relaciones Eloquent para obtener los hallazgos
        $hallazgos = $this->tipoHallazgo()->get()->map(function ($hallazgo) {
            return $hallazgo->descripcion_otros ?: $hallazgo->nombre;
        });

        var_dump($hallazgos);
        // Concatena los hallazgos en una cadena
        return $hallazgos->isNotEmpty() ? $hallazgos->implode(',') : 'N/A';
    }


    // public function concatenarHallazgos()
    // {

    //     $idInformeCampo = $this->id_informe_campo;

    //     $resultados = DB::select("
    //     SELECT COALESCE(wichh.descripcion_otros, wh.nombre) AS hallazgo
    //     FROM Wb_informe_campo_has_hallazgo wichh
    //     INNER JOIN Wb_hallazgo wh ON wichh.fk_id_hallazgo = wh.id_hallazgo
    //     WHERE wichh.fk_id_informe_campo = ?
    // ", [$idInformeCampo]);

    //     $hallazgos = array_map(function ($row) {
    //         return $row->hallazgo;
    //     }, $resultados);

    //     return !empty($hallazgos) ? implode(', ', $hallazgos) : 'N/A';
    // }


    // public function obtenerHallazgosConcatenados()
    // {
    //     $resultado = DB::select("
    //         SELECT STUFF(
    //             (SELECT ', ' + COALESCE(wichh.descripcion_otros, wh.nombre)
    //             FROM Wb_informe_campo_has_hallazgo wichh
    //             INNER JOIN Wb_hallazgo wh ON wichh.fk_id_hallazgo = wh.id_hallazgo
    //             WHERE wichh.fk_id_informe_campo = ?
    //             FOR XML PATH('')),
    //             1, 2, ''
    //         ) AS hallazgos
    //     ", [$this->id_informe_campo]);

    //     return $resultado[0]->hallazgos ?? 'N/A';
    // }

    public function tipoRuta()
    {
        return $this->belongsTo(wbRutaNacional::class, 'fk_id_ruta_nacional');
    }
}
