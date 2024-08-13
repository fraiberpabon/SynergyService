<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

use App\Http\trait\TokenHelpersTrait;

class UserIdResolver implements Resolver
{
    use TokenHelpersTrait;

    public static function resolve(Auditable $auditable)
    {
        $request = request();
        $resolverInstance = new UserIdResolver();
        $userId = $resolverInstance->traitGetIdUsuarioToken($request);
        return $userId;
    }
}
