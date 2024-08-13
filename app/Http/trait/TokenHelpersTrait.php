<?php

namespace App\Http\trait;

use Laravel\Sanctum\PersonalAccessToken;

trait TokenHelpersTrait
{
    var $TIEMPO_BLOQUEO = 3600;
    var $NUMERO_SOLICITUDES = 2;
    var $TOKEN_NAME_HEADER = "cod_autch";
    var $PROYECTO_HEADER = "cod_proyecto";
    var $TOKEN_REFRESH_HEADER = "tokenRefresh";
    public function traitGetTokenCabecera($req)
    {
        $token = $req->headers->get($this->TOKEN_NAME_HEADER, null);
        return $token;
    }

    public function traitGetTokenRefreshCabecera($req)
    {
        $token = $req->headers->get($this->TOKEN_REFRESH_HEADER, null);
        return $token;
    }

    public function traitGetProyectoCabecera($req)
    {
        $token = $req->headers->get($this->PROYECTO_HEADER, null);
        return $token;
    }

    public function traitGetTokenPersonal($req)
    {
        return $tokenPersonal = PersonalAccessToken::findToken($this->traitGetTokenCabecera($req));
    }

    public function traitGetIdUsuarioToken($req)
    {
        $usuario = $this->traitGetTokenPersonal($req);
        if ($usuario != null) {
            return $usuario->tokenable_id;
        } else {
            return null;
        }
    }

}