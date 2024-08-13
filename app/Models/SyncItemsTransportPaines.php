<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncItemsTransportPaines extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table='ItensTransportPainel';
    protected $primaryKey='TXCounter';
    public $timestamps = false;
    public $incrementing = true;
}
