<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

class personal_access_tokens extends PersonalAccessToken
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $connection = 'sqlsrv2';
    protected $table='personal_access_tokens';
    protected $primaryKey='id';

    protected $casts = [
        'created_at' => 'datetime:d-m-Y H:i:s' ,
        'updated_at' => 'datetime:d-m-Y H:i:s' ,
        'verdadero_o_falso'=> 'bool',
        'edad'=>'int'
    ];
}
