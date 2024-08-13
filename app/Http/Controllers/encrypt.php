<?php

namespace App\Http\Controllers;

use App\Http\trait\ChanguePasswordTrait;
use App\Models\Wb_password_hash;
use App\Models\WbTipoPasword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class encrypt extends BaseController
{
    use ChanguePasswordTrait;
    private $BLOQUEADO = 1;
    private $NO_BLOQUEADO = 0;
    private $IS_BLOQUEADO_NAME = 'isBloqueado';
    public $TIEMPO_BLOQUEO_PASSWORD = 1;

    // Generar password para que el cliente pueda encriptar sus datos
    public function index(Request $req, $tipoPassword = ''){
        //validamos si tiene una contraseña actual
        $this->eliminarPasswordTrait($this->getPasswordActualTrait(Request::capture()));
        
        $numeroPeticiones = 500;
        $ip = $this->getIp();
        //buscar tipo password
        $date = date('YMdiu');
        $numeroAzar = rand(1, 100);
        $extra = Hash::make($date.$numeroAzar, [
            'rounds' => 4,
        ]);
        $tipoPasswordConsultado =$this->traitGetTipoClave($extra);
        $idTipoPassword = $tipoPasswordConsultado->id;
        $idUsuario = $this->traitGetIdUsuarioToken($req);
        $consulta = Wb_password_hash::where('tipo_password', $idTipoPassword)->get();
        if(!$consulta->isEmpty()) {
            $modelo = $consulta[0];
            if($modelo->intentos < $numeroPeticiones) {
                $modelo->intentos += 1;
                $modelo->save();
                return ['data'=> $this->traitChanguePassword($modelo->nombre), $this->IS_BLOQUEADO_NAME => $this->NO_BLOQUEADO, 'gred'=> $tipoPasswordConsultado->nombre];
            } else {
                if($modelo->fechaBloqueo == null) {
                    $fecha = now()->addMinute($this->TIEMPO_BLOQUEO_PASSWORD);
                    $new_date = Carbon::createFromFormat('Y-m-d H:i:s', $fecha)->format('d-m-Y H:i:s');
                    $modelo->fechaBloqueo = $new_date;
                    $modelo->save();
                } else {
                    $date = Carbon::now()->toDateTimeString();
                    $new_date = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y H:i:s');
                    $fecha = strtotime($new_date);
                    $fecha2 = strtotime($modelo->fechaBloqueo);
                    if($fecha > $fecha2) {
                        $this->eliminarPasswordHash($ip);
                        $newModel = $this->crearNuevoPasswordHash($ip, $idTipoPassword, $idUsuario);
                        return ['data'=> $newModel->nombre, $this->IS_BLOQUEADO_NAME => $this->NO_BLOQUEADO, 'gred'=> $extra];
                    }
                }
                return ['data'=> $this->traitChanguePassword($modelo->nombre), $this->IS_BLOQUEADO_NAME => $this->BLOQUEADO, 'gred'=> $extra];
            }
        } else {
            $modelo = $this->crearNuevoPasswordHash($ip, $idTipoPassword, $idUsuario);
            /*if ($idTipoPassword != $modelo->tipo_password) {
                $modelo->tipo_password = $idTipoPassword;
                $modelo->save();
            }*/
            return ['data'=> $this->traitChanguePassword($modelo->nombre), $this->IS_BLOQUEADO_NAME => $this->NO_BLOQUEADO, 'gred'=> $extra];
        }
    }

    public function isHabilitado($contrasena){
        $ip = $this->getIp();
        $consulta = Wb_password_hash::where('nombre','=', $contrasena)->where('ip','=', $ip)->limit(1)->get();
        if(!$consulta->isEmpty()) {
            $modelo = $consulta[0];
            if($modelo->fechaBloqueo != null) {
                $fecha = strtotime(date("Y-d-m H:i:s",time()));
                $fecha2 = strtotime($modelo->fechaBloqueo);
                if($fecha > $fecha2) {
                    return ['cod'=> 1];
                }
            }
        } else {//no se puede encriptar, la contraseña no existe en la base de datos
            return ['cod'=> 1];
        }
    }

    public function decode($data, $contraseña){
        $ip = $this->getIp();
        $consulta = Wb_password_hash::where('nombre','=', $contraseña)->where('ip','=', $ip)->limit(1)->get();
        if(!$consulta->isEmpty()) {
            $modelo = $consulta[0];
            $hash = "";
            if($modelo->fechaBloqueo != null) {
                $hash = Hash::make($data);
            } else {
                $fecha = strtotime(date("Y-d-m H:i:s",time()));
                $fecha2 = strtotime($modelo->fechaBloqueo);
                if($fecha > $fecha2) {
                    return ['cod'=> 1];
                } else {
                    $hash = Hash::make($data);
                }
            }
            return ['cod'=> 0, 'data'=> $hash];
        } else {//no se puede encriptar, la contraseña no existe en la base de datos
            return ['cod'=> 1];
        }
    }

    public function eliminarPasswordHash($ip) {
        $consulta = Wb_password_hash::Where('ip','=', $ip)->limit(1)->get();
        if (!$consulta->isEmpty()) {
            $consulta[0]->delete();
        }
    }

    private function crearNuevoPasswordHash($ip, $tipoPassword, $usuario) {
        $encript = Crypt::encryptString(bin2hex(random_bytes(15)));
        $insert = new Wb_password_hash;
        $insert->nombre = $encript;
        $insert->ip = $ip;
        $insert->intentos = 0;
        $insert->tipo_password = $tipoPassword;
        if ($usuario != null) {
            $insert->usuario = $usuario;
        } else {
            $insert->usuario = 0;
        }
        $insert->fechaBloqueo = null;
        $insert->fechaRegistro = $this->traitGetDateNow();
        $insert->horaRegistro = $this->traitGetTime();
        $insert->save();
        $insert->id = $insert->latest('id')->first()->id;
        if ($insert) {
            return $insert;
        } else {
            return $this->crearNuevoPasswordHash($ip, $tipoPassword, $usuario);
        }
    }




}
