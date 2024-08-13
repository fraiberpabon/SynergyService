<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class WbEquipo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $connection = 'sqlsrv2';
    protected $table = 'Wb_equipos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    public $module = 'Equipos';

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

    /* public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'new_values.fk_compania')) {
            $data['old_values']['nombrecompania'] = Compania::find($this->getOriginal('fk_compania'))->nombreCompañia;
            $data['new_values']['nombrecompania'] = Compania::find($this->getAttribute('fk_compania'))->nombreCompañia;
        }

        if (Arr::has($data, 'new_values.fk_id_tipo_equipo')) {
            $data['old_values']['nombreequipo'] = wbTipoEquipo::find($this->getOriginal('fk_id_tipo_equipo'))->nombre;
            $data['new_values']['nombreequipo'] = wbTipoEquipo::find($this->getAttribute('fk_id_tipo_equipo'))->nombre;
        }

        return $data;
    }*/
}
