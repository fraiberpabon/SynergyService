<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

use App\Http\trait\TokenHelpersTrait;

class ProjectCodeResolver implements Resolver
{
    use TokenHelpersTrait;

    public static function resolve(Auditable $auditable)
    {
        $req = request();
        $resolverInstance = new ProjectCodeResolver();
        $token = $resolverInstance->traitGetProyectoCabecera($req);
        if ($token == null) {
            $token = 1;
        }
        return $token;
    }
}
