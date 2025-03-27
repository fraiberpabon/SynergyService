<?php

namespace App\Http\Controllers;

use App\Models\SMSResponse;
use App\Models\Usuarios\usuarios_M;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    private $client;
    private $soapUrl;
    private $username;
    private $password;

    /**
     * Esta función envia las cabeceras ademas de manejar una respuesta valida que
     * pueda interpretar el metodo handleresponse.
     */
    private function Enviar_Cabeceras($celular_usuario, $mensaje, $nota)
    {
        try {
            $usuario = env('SMS_USER');
            $contrasena = env('SMS_PASSWORD');
            $BASEURL = env('BASEURL');
            $token = $this->generarToken();
            $headers = [
                'Authorization' => $token,
            ];

            return Http::withHeaders($headers)->post($BASEURL, [
                'usuario' => $usuario,
                'clave' => $contrasena,
                'numero' => $celular_usuario,
                'mensaje' => $mensaje,
                'nota' => $nota,
            ]);
        } catch (RequestException $e) {
            Log::error('Error al enviar las cabeceras por favor verifique estas '.$e->getMessage());

            return $this->handleAlert__('messages.error_envio_cabeceras');
        }
    }

    /**
     * Esta funcion crea un contructor para usar el metodo de enviar cabeceras soap.
     */
    public function __construct()
    {
        $this->client = new Client(['verify' => false]);
        $this->soapUrl = env('BASEURLSOAP');
        $this->username = env('SMS_USER');
        $this->password = env('SMS_PASSWORD');
    }

    /**
     * Esta función envia el mensaje prepara para enviar el mensaje de texto ademas
     * de manejar la respuesta del xml para su posterior uso en el función handleresponseSoap.
     */
    public function Enviar_CabecerasSoap($numero, $mensaje, $nota = '')
    {
        // Diccionario de reemplazos
        $reemplazos = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N',
            '¿' => '',
            '¡' => '',
            '&' => 'y' // Reemplazar & por "y"
        ];
    
        // Limpieza del mensaje
        $mensaje = strtr($mensaje, $reemplazos);
        $nota = strtr($nota, $reemplazos);
    
        // Construcción del SOAP request
        $soapRequest = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <EnviarMensaje xmlns="urn:servicioweb">
                    <numero>' . $numero . '</numero>
                    <mensaje>' . $mensaje . '</mensaje>
                    <nota>' . $nota . '</nota>
                    <usuario>' . $this->username . '</usuario>
                    <clave>' . $this->password . '</clave>
                </EnviarMensaje>
            </soap:Body>
        </soap:Envelope>';
    
        try {
            // Enviar la solicitud SOAP
            $response = $this->client->post($this->soapUrl, [
                'headers' => [
                    'Content-Type' => 'text/xml; charset=UTF-8',
                    'SOAPAction' => $this->soapUrl . '/EnviarMensaje',
                ],
                'body' => $soapRequest,
            ]);
    
            $xmlResponse = $response->getBody()->getContents();
            $xmlResponse = html_entity_decode($xmlResponse, ENT_QUOTES, 'ISO-8859-1');
    
            $start = strpos($xmlResponse, '<respuesta xsi:type="xsd:string">') + 33;
            $end = strpos($xmlResponse, '</respuesta>');
            $length = $end - $start;
    
            $respuesta = substr($xmlResponse, $start, $length);
            $responseData = json_decode($respuesta, true);
    
            // Validación de la respuesta JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response');
            }
    
            Log::info(json_encode($responseData));
    
            return $responseData; // Retorna el array decodificado
        } catch (RequestException $e) {
            Log::error($e->getMessage());
    
            return ['envio' => false, 'mensaje' => $e->getMessage()];
        }
    }
    

    /**
     * Esta función genera un token valido para comenzar hacer el
     * envio de los mensajes de texto para enviar por API-REST
     * la cual requiere el usuario y contraseña validos que se encuentran ubicados
     * en el archivo enviroment de la aplicación de webu, ademas de contener asi mismo el
     * url donde se realiza la peticion para generar el token.
     */
    public function generarToken()
    {
        $client = new Client();

        // Obtener la URL del archivo .env
        $url_token = env('SMS_TOKEN_URL');

        // Obtener los valores de usuario y contraseña del archivo .env
        $usuario = env('SMS_USER');
        $contrasena = env('SMS_PASSWORD');

        // Validamos usuario y contraseña y definimos el tipo de contenido
        try {
            $response = $client->post($url_token, [
                'json' => [
                    'usuario' => $usuario,
                    'clave' => $contrasena,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
            // decodificamos la respuesta
            $data = json_decode($response->getBody()->getContents(), true);

            // Verificar si la solicitud fue exitosa y si se recibió un token
            if ($response->getStatusCode() === 200 && isset($data['token'])) {
                return $data['token'];
            } else {
                return null;
            }
        } catch (RequestException $e) {
            // Manejar errores de la solicitud HTTP
            if ($e->hasResponse()) {
            }

            return null;
        }
    }

    /**
     * Esta función envia un mensaje de texto por el permiso que se
     * este consultando por medio de parametros $id_permisos se  consulta en la
     * tabla Wb_Seguri_Roles_Permisos este solo envia estos mensajes de textos a
     * usuarios que contengan este permiso.
     */
    public function Enviar_Sms_Por_Permiso($mensaje, $nota, $id_permiso)
    {
        // Consultamos los números de celular
        $numeros = DB::table('usuarioss as u')
            ->leftJoin('Wb_Seguri_Roles_Permisos as wsrp', 'u.fk_rol', '=', 'wsrp.fk_id_Rol')
            ->where('wsrp.fk_id_permiso', '=', $id_permiso)
            ->where('u.estado', '=', 'A')
            ->where('u.celular', '!=', '')
            ->get();

        // Agregamos logs para verificar qué números de celular estamos obteniendo
        Log::info('Números de celular obtenidos:', $numeros->toArray());
        foreach ($numeros as $celular_usuario) {
            $this->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $celular_usuario->id_usuarios);
        }
    }

    /**
     * Esta función envia un mensaje de texto por el id del usuario este
     * mismo consulta el usuario y en caso que tenga un numero valido
     * lo envia por el servicio de mensajes  el cual se envia por dos funcións
     * por defecto se envia por WSDL** y en caso este servicio responda error
     *  HTTP **500** se envia por API-REST.
     */
    public function Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios)
    {
        $Sms_Exitosos = 0;
        $Sms_Fallidos = 0;

        // Verificar si el usuario existe
        $usuario = usuarios_M::where('id_usuarios', $id_usuarios)->first();

        if (!$usuario) {
            // Usuario no encontrado, enviar mensaje de error a handleResponse
            $response = [
                'res' => 'bad',
                'mensaje' => 'El usuario no existe',
                'validaciones' => ['El usuario no existe'],
            ];

            $this->handleResponse($response, $id_usuarios, null, $nota, $mensaje);

            return response()->json([
                'success' => false,
                'message' => 'El usuario no existe',
            ], 404);
        }

        // Verificar si el usuario está deshabilitado
        if ($usuario->habilitado == 0) {
            // Usuario deshabilitado, enviar mensaje de error a handleResponse
            $response = [
                'res' => 'bad',
                'mensaje' => 'El usuario está deshabilitado',
                'validaciones' => ['El usuario está deshabilitado'],
            ];
            $this->handleResponse($response, $id_usuarios, null, $nota, $mensaje);

            return response()->json([
                'success' => false,
                'message' => 'El usuario está deshabilitado',
            ], 404);
        }

        // Obtener el número de teléfono del usuario
        $celular_usuario = $usuario->celular;

        // Verificar si el número de celular está vacío
        if (empty($celular_usuario)) {
            // Número de celular vacío, enviar mensaje de error a handleResponse
            $response = [
                'res' => 'bad',
                'mensaje' => 'El número de celular está vacío',
                'validaciones' => ['El número de celular está vacío'],
            ];

            $this->handleResponse($response, $id_usuarios, null, $nota, $mensaje);

            return response()->json([
                'success' => false,
                'message' => 'El número de celular está vacío',
            ], 400);
        }

        // Procesar el número de celular encontrado
        try {
            $response = $this->Enviar_CabecerasSoap($celular_usuario, $mensaje, $nota, $id_usuarios);
        } catch (RequestException $e) {
            Log::error('Error al enviar la solicitud:', $e->getMessage());
        }
         $this->handleResponseSoap($response, $id_usuarios, $celular_usuario, $nota, $mensaje);

        // Procesar los números de teléfono encontrados
        // $response = $this->Enviar_Cabeceras($celular_usuario, $mensaje, $nota, $id_usuarios);
        // $this->handleResponse($response, $id_usuarios, $celular_usuario, $nota, $mensaje);
        // if ($response->successful()) {
        //     ++$Sms_Exitosos;
        // } else {
        //     ++$Sms_Fallidos;
        // }

        // Procesar el resultado del envío de SMS
        try {
            $responseBody = $response;

            return response()->json([
                'success' => true,
                'message' => 'Mensaje de confirmación enviado con éxito',
                // 'response' => $responseBody->getBody()->getContents(),
                'response' => $responseBody,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error sending confirmation message: '.$e->getMessage());
            if ($e instanceof RequestException && $e->hasResponse()) {
                Log::error('Respuesta del servidor: '.$e->getResponse()->getBody()->getContents());
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje de confirmación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Esta función verifica un usuario que no tenga verficado el numero de celular
     * envia el numero de celular segun el numero que reciba en la solicitud
     * sin consultar este del usuario ya que se pretende verificar.
     */
    public function Verificar_Usuario($mensaje, $nota, $id_usuarios, $celular_usuario)
    {
        $usuario = usuarios_M::where('id_usuarios', $id_usuarios)->first();
        if (!$usuario) {
            $response = [
                'success' => false,
                'res' => 'bad',
                'mensaje' => 'El usuario no existe',
                'validaciones' => ['El usuario no existe'],
            ];

            return response()->json($response, 404);
        }

        if (!is_string($mensaje) || empty($nota) || empty($celular_usuario)) {
            $response = [
                'success' => false,
                'res' => 'bad',
                'mensaje' => 'Los parámetros para el envío del mensaje son incorrectos. Verificar.',
                'validaciones' => ['Parámetros incorrectos'],
            ];

            return response()->json($response, 400);
        }

        try {
            $response = $this->Enviar_CabecerasSoap($celular_usuario, $mensaje, $nota);
        } catch (\Exception $e) {
            $response = [
                'res' => 'bad',
                'mensaje' => 'Error al enviar las cabeceras',
                'validaciones' => ['Error de envío'],
                'success' => false,
                'exception' => $e->getMessage(),
            ];

            return response()->json($response, 500);
        }

        $this->handleResponseSoap($response, $usuario->id_usuarios, $celular_usuario, $nota, $mensaje);
        $responseData = is_array($response) ? $response : $response->json();
        Log::info($usuario->id_usuarios);
        if ($responseData['envio'] === true) {
            return response()->json([
                'success' => true,
                'res' => 'ok',
                'message' => 'Mensaje de confirmación enviado con éxito',
            ], 200);
        } else {
            Log::info('Mensaje response: '.json_encode($responseData));

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje de confirmación',
                'res' => 'bad',
                'error' => $responseData['mensaje'] ?? 'Error desconocido',
            ], 500);
        }
    }

    /**
     * Esta función guarda la información en la base de datos de la siguiente manera:
     * Id -  Fk_id_usuario - Celular - Mensaje  - Nota -  Estado - Respuesta -  IdTransaccion  - Fk_id_project_company  - TimeStamp  - Metodo_envio
     * Este mismo almacena el tipo de respuesta que recibe del servicio y maneja la información que recibe
     * ya sea una respuesta con error o sin error, que contenga error codigo 500 o 200 ok ademas de guardar el metodo de envio en este caso API-REST.
     */
    public function handleResponse($response, $id_usuarios, $celular_usuario, $nota, $mensaje)
    {
        $metodo = 'API-REST';
        try {
            if (is_array($response)) {
                $responseData = $response;
            } else {
                // Extraer el response
                $responseData = $response->json();
            }
            Log::info('Mensaje response: '.json_encode($responseData));

            // Obtener el código de estado de la respuesta
            $statusCode = isset($responseData['status']) ? $responseData['status'] : null;

            if ($statusCode >= 500) {
                // Si el código de estado es 500, almacenar el error
                $responseData['res'] = 'false';
                $matchedValidations = [json_encode($responseData)];
                $idTransaccion = null;
            } else {
                $res = isset($responseData['res']) ? $responseData['res'] : 'bad';
                // Si la respuesta es ok en el Mensaje guardo Registro Almacenado
                if ($res === 'ok' && isset($responseData['mensaje']) && strpos($responseData['mensaje'], 'Registro almacenado:') === 0) {
                    $matchedValidations = ['Registro almacenado'];
                    $idTransaccion = intval(substr($responseData['mensaje'], strpos($responseData['mensaje'], ':') + 2));
                } else {
                    // Obtener todas las validaciones del array
                    $matchedValidations = $responseData['validaciones'] ?? [isset($responseData['mensaje']) ? $responseData['mensaje'] : ''];
                    // Seteamos la transaccion como null
                    $idTransaccion = null;
                }
            }
            SMSResponse::create([
                'fk_id_usuario' => $id_usuarios,
                'celular' => $celular_usuario,
                'mensaje' => $mensaje,
                'nota' => $nota,
                'estado' => $res,
                'respuesta' => $matchedValidations ? $matchedValidations[0] : '',
                'idTransaccion' => $idTransaccion,
                'fk_id_project_company' => 1,
                'metodo_envio' => $metodo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in handleResponse: '.$e->getMessage());
        }
    }

    /**
     * Esta función guarda la información en la base de datos de la siguiente manera:
     * Id -  Fk_id_usuario - Celular - Mensaje  - Nota -  Estado - Respuesta -  IdTransaccion  - Fk_id_project_company  - TimeStamp  - Metodo_envio
     * Este mismo almacena el tipo de respuesta que recibe del servicio y maneja la información que recibe
     * ya sea una respuesta con error o sin error, que contenga error codigo 500 o 200 ok ademas de guardar el metodo de envio en este caso WSDL.
     */
    public function handleResponseSoap($response, $id_usuarios, $celular_usuario, $nota, $mensaje)
    {
        try {
            // Suponiendo que $responseData ya está definido y contiene el JSON decodificado de la respuesta
            $responseData = $response; // Usa la respuesta directamente
            $statusCode = isset($responseData['envio']) ? ($responseData['envio'] === true ? 200 : 500) : null;
            $res = 'bad'; // Valor predeterminado en caso de error
            $metodo = 'WSDL';
            if ($statusCode >= 500) {
                // Si el código de estado es 500, almacenar el error
                $responseData['envio'] = 'error';
                $matchedValidations = [json_encode($responseData)];
                $idTransaccion = null;
            } else {
                $res = isset($responseData['envio']) ? $responseData['envio'] : 'false';
                // Si la respuesta es ok en el Mensaje guardo Registro Almacenado
                if ($res === true && isset($responseData['mensaje'])) {
                    $matchedValidations = ['Registro almacenado'];
                } else {
                    // Obtener todas las validaciones del array
                    $matchedValidations = [];
                    if (isset($responseData['mensaje'])) {
                        $matchedValidations[] = $responseData['mensaje'];
                    }
                    if (isset($responseData['numero'])) {
                        $matchedValidations[] = $responseData['numero'];
                    }
                    // Seteamos la transaccion como null
                    $idTransaccion = null;
                }
            }
            if ($res === true) {
                $res = 'ok';
            } else {
                $res = 'bad';
            }
            // Guardar en la base de datos
            SMSResponse::create([
                'fk_id_usuario' => $id_usuarios,
                'celular' => $celular_usuario,
                'mensaje' => $mensaje,
                'nota' => $nota,
                'estado' => $res,
                'respuesta' => $matchedValidations ? json_encode($matchedValidations) : 'Sin validaciones',
                'idTransaccion' => isset($responseData['id']) ? $responseData['id'] : null,
                'fk_id_project_company' => 1,
                'metodo_envio' => $metodo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in handleResponseSoap: '.$e->getMessage());
        }
    }
}
