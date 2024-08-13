<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class SyncIndicadorResource extends ResourceCollection
{

    public function toArray($request)
    {
        return
        $this->collection->map(function($data) {
                return
                [
                    'Id'=>$data['id_indicador'],
                    'Indicador'=>$data['indicador'],
                    'Limite'=>$data['limite'],
                    'Estable'=>$data['cod_estable'],
                    'Estado'=>$data['estado']
                ];
            });
    }
}

