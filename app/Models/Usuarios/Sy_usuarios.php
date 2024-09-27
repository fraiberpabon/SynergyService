<?php

namespace App\Models\Usuarios;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Contracts\Auditable;

class Sy_usuarios extends Model implements Auditable
{
    #Implementamos la interfaz Auditable
    use \OwenIt\Auditing\Auditable;

    # Creamos el tags del nombre del modulo
    public $module = 'Usuarios de synergy';

    use HasFactory;
    protected $connection = 'sqlsrv3';
    protected $table = 'Sy_usuarios';
    protected $primaryKey = 'id_sy_usuarios';
    public $timestamps = true;

    protected $dateFormat = 'd-m-Y H:i:s.v'; //activar solo en pruebas

    # Funcion para obtener el nombre de la tabla
    public function GetTable()
    {
        return $this->table;
    }

    # Funcion para generar los tags de la auditoria
    public function generateTags(): array
    {
        return [
            $this->module
        ];
    }
}
