<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Collection;

trait Resource
{
    /**
     * Transforma el recurso en un array
     *
     * @param $lista
     * @return Collection|\Illuminate\Support\Collection
     */
     function toArray($lista): Collection|\Illuminate\Support\Collection {
         return $lista->map(function($data) {
             return $this->toModel($data);
         });
     }

    /**
     *
     * @param $modelo
     * @return array
     */
    function toModel($modelo): array{
        return [];
    }
}
