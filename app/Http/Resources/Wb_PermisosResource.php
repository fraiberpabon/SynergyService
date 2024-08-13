<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Wb_PermisosResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return
        $this->collection->map(function($data) {
                return
                [
                    'ID'=>$data['id_permiso'],
                    'DESCRIPCION'=>$data['nombrePermiso'],
                    'ROL'=>$data['nombreRol']
                ];
            });
    }
}
