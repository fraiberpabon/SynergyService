<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnviarCorreo extends BaseController
{

    private function limpiarAsunto($asunto)

    {
        $cadena = "Subject";
        $longitud = strlen($cadena) + 2;
        return substr(
            iconv_mime_encode(
                $cadena,
                $asunto,
                [
                    "input-charset" => "UTF-8",
                    "output-charset" => "UTF-8",
                ]
            ),
            $longitud
        );
    }

    public function material(Request $request) {
        $asunto = $this->limpiarAsunto("Team Webu Envio De Material Solicitud #   $request->solicitud");
        $destinatario = $request->correo;
        $encabezados = "MIME-Version: 1.0" . "\r\n";
        # ojo, es una concatenación:
        $encabezados .= "Content-type:text/html; charset=UTF-8" . "\r\n";
        $encabezados .= 'From: Materiales en Camino <no-reply@ariguani.com.co>' . "\r\n";
        $message  = "<html><body>";

        $message .= "<table width='100%' bgcolor='#e0e0e0' cellpadding='0' cellspacing='0' border='0'>";

        $message .= "<tr><td>";

        $message .= "<table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='max-width:650px; background-color:#fff; font-family:Verdana, Geneva, sans-serif;'>";

        $message .= "
        <thead>
          <tr height='80'>
            <th colspan='4' style='background-color:#f5f5f5; border-bottom:solid 1px #bdbdbd; font-family:Verdana, Geneva, sans-serif; color:#E60111; font-size:34px;' >Tu material está en camino</th>
          </tr>
        </thead>";
        $message .= "<tbody style='display: block;'>
        <tr align='center' height='50' style='font-family:Verdana, Geneva, sans-serif;'>
            <td style='background-color:#E60111; text-align:center;'><a href='' style='color:#fff; text-decoration:none;'></a></td>
            <td style='background-color:#E60111; text-align:center;'><a href='al:android:package' style='color:#fff; text-decoration:none;'></a></td>
            <td style='background-color:#E60111; text-align:center;'><a href='intent://#Intent;scheme=google-app-package;package=al:android:package' style='color:#fff; text-decoration:none;' ></a></td>
            <td style='background-color:#E60111; text-align:center;'><a href='' style='color:#fff; text-decoration:none;' ></a></td>
        </tr>
            <p style='font-size:18px;'></p>
            <hr />
        <tr>
            <td colspan='4' style='padding:15px;padding-left: 200px; padding-right: 200px; '>
                <b style='   text-align:center;    padding:20px 10px 20px 10px;display:table-cell;vertical-align:middle;width:230px;height:auto;background-color:#FFF9AF;font-size:20px;font-weight:600'>" . $request->solicitud . "</b>
                <tr>
                </tr>
            </td>
        </tr>
        <tr>
            <td colspan='4' style='padding:15px;padding-left: 200px; padding-right: 200px; '>
                <img src='https://i.ibb.co/gjPYL9p/Sin-t-tulo.png' alt='Webu' style='height:auto; width:100%; max-width:100%;' />
                <p style='font-size:15px; font-weight:bold; text-align:center;  font-family:Verdana, Geneva, sans-serif;'>Detalles de Envio</p>
                <img src='https://i.ibb.co/QF99WzM/research.png' alt='Webu' style='height:auto; text-align:center; width:30%; max-width:30%;padding-left: 40%;' />
                <p style='  font-size:13px; text-align:center; '>Ubicacion: " . $request->ubicacion . "</p>
                <p style='  font-size:13px; text-align:center; '>" . $request->resistencia . "</p>
                <p style='  font-size:13px; text-align:center; '>" . $request->volumen . "</p>
                <hr />
                <p style='font-size:15px; font-family:Verdana, Geneva, sans-serif;text-align:center'>De acuerdo a su Solicitud enviada,Tu Material llegará pronto.</p>
                <p style='font-size:9px; font-family:Verdana, Geneva, sans-serif;text-align:center'>Si tienes alguna duda en cuanto a su solicitud ponerse en contacto con nosotros: <a href=''>WeBuApp@outlook.com</a>.</p>
            </td>
        </tr>
        </tbody>";

        $message .= "</table>";
        $message .= "</td></tr>";
        $message .= "</table>";
        $message .= "</body></html>";
        $message = wordwrap($message, 70, "\r\n");
        $resultado = mail($destinatario, $asunto, $message, $encabezados); #Mandar al final los encabezados
        if ($resultado) {

        }
    }
}
