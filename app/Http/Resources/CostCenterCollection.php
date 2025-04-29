<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CostCenterCollection extends ResourceCollection
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

            $this->collection->map(function ($modelo) {
                $distribuible = $modelo->Distribuible == 1 ? __('messages.si') : __('messages.no');
                return [
                    'id' => $modelo->id,
                    'Codigo' => $modelo->Codigo,
                    'Descripcion' => $modelo->Descripcion,
                    'Observacion' => $modelo->Observacion,
                    'UnidadMedida' => $modelo->UM,
                    'Grupo' => $modelo->Grupo,
                    'Distribuible' => $distribuible,
                    'Estado' => $modelo->Estado,
                    'created_at' => $modelo->created_at,
                    'usuario_creador' => $modelo->usuario ? $modelo->usuario->Nombre . " " . $modelo->usuario->Apellido : null,
                    'updated_at' => $modelo->updated_at,
                    'usuario_actualizacion' => $modelo->fk_user_update,
                    'proyecto' => $modelo->fk_id_project_Company,
                    'fk_compania' => $modelo->fk_compania,
                    'compania' => $modelo->compania ? $modelo->compania->nombreCompañia : null,
                ];
            });
    }
}
