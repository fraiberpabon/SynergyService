<?php

namespace App\Http\trait;
use Illuminate\Support\Facades\Storage;

/**
 * elimina una imagen almacenada en una carpeta específica.
 */
trait TraitEliminarImagen
{
    /**
     * Elimina una imagen en una carpeta espécifica
     *
     * @param  string  $file
     * @param  string  $carpeta
     * @return void
     */
    public function eliminarImagenTrait($file, $carpeta) {
        if (Storage::disk(env('IMAGEN_STORAGE'))->exists($carpeta . $file)) {
            Storage::disk(env('IMAGEN_STORAGE'))->delete($carpeta . $file);
        }
    }
}
