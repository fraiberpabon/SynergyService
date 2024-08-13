<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncConfig extends Model
{
    use HasFactory;
    protected $table='sync_confi';
    Protected  $primaryKey='id';
    public $timestamps = false;
}
