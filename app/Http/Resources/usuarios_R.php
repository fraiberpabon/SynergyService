<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class usuarios_R extends ResourceCollection
{

    public function toArray($request)
    {
        return
        $this->collection->map(function($data) {
                return
                [
                    'IDENTIFICADOR'=>$data['id_usuarios'],
                    'USU'=>$data['usuario'],
                    'CLAVE'=>$data['contraseña'],
                    'CODIGO'=>$data['matricula'],
                ];
            });
    }
}
