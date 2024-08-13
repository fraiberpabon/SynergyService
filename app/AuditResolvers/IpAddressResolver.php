<?php

namespace App\AuditResolvers;

use App\Http\trait\IpTrait;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;
use Illuminate\Support\Facades\Auth;
use App\Http\trait\TokenHelpersTrait;

class IpAddressResolver implements Resolver
{
    //use TokenHelpersTrait;
    use IpTrait;
    public static function resolve(Auditable $auditable)
    {
        $ipTrait = new IpAddressResolver();
        return $ipTrait->getIp();
    }
}
