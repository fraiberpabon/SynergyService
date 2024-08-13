<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class TableNameResolver implements Resolver
{

    public static function resolve(Auditable $auditable)
    {
        if (method_exists($auditable, 'GetTable')) {
            return $auditable->GetTable();
        }
    }
}
