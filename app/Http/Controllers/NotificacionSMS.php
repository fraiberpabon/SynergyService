<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//enviar mensaje por udp
use Pikart\Goip\Sms\SocketSms;
//enviar mensaje por https
use Pikart\Goip\Sms\HttpSms;

class NotificacionSMS extends BaseController
{
    //variable globales

    //HTTPS
    /**
    * HttpSms constructor.
    *
    * @param string $host Goip host for example: http://192.168.0.11
    * @param int $line Goip line number,
    * @param string $login Goip login
    * @param string $password Goip password
    */
    protected $sms2;

    function __construct()
    {

        $this->sms2 = new HttpSms(env('SMS_SERVER'),1, env('SMS_USER'), env('SMS_PASSWORD'));
    }


    public function SMS(Request $request)
    {
        try{
            $datos=$request->all();
            $response = $this->sms2->send($datos['tel'], $datos['mensaje']);
            if ($response['status']=='send') {
                return $this->handleResponse($request, $response['id'],'Mensaje enviado');
            } else {
               return $this->handleAlert($response['id'].' : Estado - '.$response['status']);
            }
         }catch(\Exception $ex){
             return $this->handleError('Error al establecer conexion con el servidor de SMS');

         }

    }
}


