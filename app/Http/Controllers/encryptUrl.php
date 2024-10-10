<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Support\Facades\Config;

class encryptUrl extends BaseController
{
    private const ALGORITHM = 'AES-256-CBC'; // Equivalente a AES/CBC/PKCS5Padding en Java
    private const IV_LENGTH = 16; // Longitud del IV en bytes

    // Encriptar cuando está en modo offline
    public static function encryptMsg(string $message): string
    {
        // Generar clave secreta
        $secretKey = self::generateKey(Config::get('app.encrypt_key'));

        // Generar un IV aleatorio (16 bytes)
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ALGORITHM));

        // Cifrar el mensaje
        $cipherText = openssl_encrypt($message, self::ALGORITHM, $secretKey, OPENSSL_RAW_DATA, $iv);

        // Combinar IV y texto cifrado
        $ivAndCipherText = base64_encode($iv . $cipherText);

        return $ivAndCipherText;
    }

    // Desencriptar cuando está en modo offline
    public static function decryptMsg(string $cipherText): string
    {
        // Obtener la clave secreta desde la configuración
        $secretKey = self::generateKey(Config::get('app.encrypt_key'));

        // Decodificar Base64 para obtener el IV y el texto cifrado
        $ivAndCipherText = base64_decode($cipherText);

        // Verificar si el texto codificado tiene al menos el tamaño del IV
        if (strlen($ivAndCipherText) < self::IV_LENGTH) {
            throw new Exception('Datos cifrados no válidos');
        }

        // Extraer el IV (16 bytes) y el texto cifrado
        $iv = substr($ivAndCipherText, 0, self::IV_LENGTH);
        $encryptedText = substr($ivAndCipherText, self::IV_LENGTH);

        // Desencriptar el texto usando AES/CBC/PKCS5Padding
        $decryptedBytes = openssl_decrypt($encryptedText, self::ALGORITHM, $secretKey, OPENSSL_RAW_DATA, $iv);

        if ($decryptedBytes === false) {
            throw new Exception('Error al desencriptar: ' . openssl_error_string());
        }

        // Convertir los bytes desencriptados a string UTF-8
        return mb_convert_encoding($decryptedBytes, 'UTF-8', 'UTF-8');
    }

    // Generar clave secreta
    private static function generateKey(?string $key): string
    {
        if (is_null($key)) {
            throw new \InvalidArgumentException('La clave de encriptación no puede ser nula. Verifica que esté configurada en el archivo .env.');
        }

        // Asegurarse de que la clave tenga exactamente 32 bytes para AES-256
        return substr(hash('sha256', $key, true), 0, 32);
    }
}
