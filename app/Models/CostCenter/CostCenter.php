<?php

namespace App\Models\CostCenter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Compania;
use App\Models\Usuarios\usuarios_M;

class CostCenter extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv3';
    protected $table = 'wb_costos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;


    public function compania()
    {
        return $this->hasOne(Compania::class, 'id_compañia', 'fk_compania');
    }

    public function usuario()
    {
        return $this->hasOne(usuarios_M::class, 'id_usuarios', 'fk_user_creador');
    }
}
