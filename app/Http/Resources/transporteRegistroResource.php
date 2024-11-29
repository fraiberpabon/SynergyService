<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;
class transporteRegistroResource extends ResourceCollection
{

    public function toArray($request)
    {
        return
        $this->collection->map(function($modelo) {
            return [
                'equipoId' => ($modelo->equipo) ? $modelo->equipo->equiment_id : '',
                'placa' => ($modelo->equipo) ? $modelo->equipo->placa : null,
            ];
        });
    }
}
