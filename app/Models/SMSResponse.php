<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSResponse extends Model
{
    use HasFactory;
    use HasFactory;
    protected $connection = 'mysql';
    protected $table = 'wb_sms_logs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'fk_id_usuario',
        'celular',
        'mensaje',
        'nota',
        'estado',
        'respuesta',
        'idTransaccion',
        'datecreate',
        'user_create',
        'fk_id_project_company',
        'metodo_envio',
    ];
}
