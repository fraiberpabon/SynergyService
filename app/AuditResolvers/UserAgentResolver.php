<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;
use Illuminate\Http\Request;

class UserAgentResolver implements Resolver
{

    public static function resolve(Auditable $auditable)
    {

        $request = request();

        $secChUaPlatform = $request->header('Sec-Ch-Ua-Platform');

        $request->sec_ch_ua_platform = $secChUaPlatform;
    }
}
