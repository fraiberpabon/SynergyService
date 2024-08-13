<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ts_Equipement extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table='Equipments';
    protected $primaryKey='EquipmentID';
    public $incrementing = false;
    public $timestamps = false;

}
