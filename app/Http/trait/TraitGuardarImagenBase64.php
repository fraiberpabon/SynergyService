<?php

namespace App\Http\trait;
use Illuminate\Support\Facades\Storage;
trait TraitGuardarImagenBase64
{
    public function guardarImagenTrait($base64ImageData, $patch, $name) {
        /**
         * Se definen las variables $newWidth y $newHeight con el tamaño deseado para la imagen redimensionada.
         * La variable $maxSize define el tamaño máximo permitido para la imagen en bytes. La variable $imageData
         * decodifica la imagen codificada en base64.
         */
        $newWidth = 350;
        $newHeight = 350;
        $maxSize = 500000;
        $imageData = base64_decode($base64ImageData);
        /**
         * Se define la ruta temporal donde se guardará la imagen decodificada. Luego, se utiliza la
         * función Storage::put() para guardar la imagen en la ruta temporal.
         */
        $imagePathTemporal = 'img/temp-image.png';
        Storage::put($imagePathTemporal, $imageData);
        /**
         * Se verifica el tamaño de la imagen utilizando la función Storage::size(). Si el tamaño de la imagen es
         * mayor que el tamaño máximo permitido, se devuelve un mensaje de error.
         */
        $imageSize = Storage::size($imagePathTemporal);
        if ($imageSize > $maxSize) { // 500 KB
            return ['error' => true, 'mensaje' => 'La imagen es demasiado grande'];
        }
        /**
         * Se verifica el tipo de la imagen utilizando la función Storage::mimeType(). Si el tipo de la imagen no es
         * PNG, JPEG o JPG, se devuelve un mensaje de error.
         */
        $imageType = Storage::mimeType($imagePathTemporal);
        if (!in_array($imageType, ['image/png', 'image/jpeg', 'image/jpg'])) {
            return ['error' => true, 'mensaje' => 'Tipo de imagen no soportado'];
        }
        /**
         * Se obtienen las dimensiones de la imagen utilizando la función getimagesize(). Luego, se calcula el nuevo
         * ancho y alto de la imagen redimensionada en función de las dimensiones originales de la imagen.
         */
        $imageSize = getimagesize(storage_path('app/'.$imagePathTemporal));
        $imageWidth = $imageSize[0];
        $imageHeight = $imageSize[1];
        if ($imageWidth > $imageHeight) {
            $newHeight = $newWidth * ($imageHeight/$imageWidth);
        } else if ($imageWidth < $imageHeight){
            $newWidth = $newHeight * ($imageWidth/$imageSize);
        }
        /**
         * Se carga la imagen existente utilizando la función imagecreatefrompng() o imagecreatefromjpeg() segun la extencion de la imagen. Luego, se crea una nueva imagen
         * utilizando la función imagecreatetruecolor(). Se copia la imagen existente en la nueva imagen utilizando
         * la función imagecopyresampled(). Finalmente, se guarda la nueva imagen utilizando la función imagepng() o imagejpeg() segun la extencion de la imagen.
         */
        $existingImage = null;
        switch ($imageType) {
            case 'image/png':
                $existingImage = imagecreatefrompng(storage_path('app/'.$imagePathTemporal));
                break;
            case 'image/jpeg':
            case 'image/jpg':
                $existingImage = imagecreatefromjpeg(storage_path('app/'.$imagePathTemporal));
                break;
        }

        // Crear una nueva imagen
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        // Copiar la imagen existente en la nueva imagen
        imagecopyresampled($newImage, $existingImage, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
        // Guardar la imagen como PNG
        switch ($imageType) {
            case 'image/png':
                imagepng($newImage, storage_path($patch . $name), 0);
                break;
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($newImage, storage_path($patch . $name), 0);
                break;
        }


        /**
         * Se elimina la imagen temporal utilizando la función Storage::delete(). Se devuelve un mensaje de éxito si la
         * función se ejecuta correctamente.
         */
        Storage::delete('img/temp-image.png');
        // Liberar la memoria
        imagedestroy($existingImage);
        imagedestroy($newImage);
        return ['error' => false, 'mensaje' => ''];
    }
}
