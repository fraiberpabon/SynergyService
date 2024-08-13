<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCompany extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv2';
    protected $table='Global_Project_Company';
    protected $primaryKey='id_Project_Company';
    public $timestamps = false;


}
