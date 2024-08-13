<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class Area extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $connection = 'sqlsrv2';
    protected $table = 'Area';
    protected $primaryKey = 'id_area';
    public $timestamps = false;
    public $incrementing = true;

    public $module = 'Area';

    public  function GetTable()
    {
        return $this->table;
    }

    public function role()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'new_values.user_id')) {
            $oldRoleName = $this->role()->find($this->getOriginal('user_id'))->name;
            $newRoleName = $this->role()->find($this->getAttribute('user_id'))->name;
            $data['old_values']['role_name'] = $oldRoleName;
            $data['new_values']['role_name'] = $newRoleName;
        }

        return $data;
    }

    public function generateTags(): array
    {
        return [
            $this->module
        ];
    }
}
