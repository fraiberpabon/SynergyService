<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

use App\Http\trait\TokenHelpersTrait;

class UserTypeResolver implements Resolver
{
    use TokenHelpersTrait;

    public static function resolve(Auditable $auditable)
    {
        $request = request();
        $resolverInstance = new UserTypeResolver();
        $userId = $resolverInstance->traitGetIdUsuarioToken($request);
        return $userId;
    }
}
