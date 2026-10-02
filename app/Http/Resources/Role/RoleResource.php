<?php

namespace App\Http\Resources\Role;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->resource->id,
            'nombre'      => $this->resource->nombre,
            'descripcion' => $this->resource->descripcion,
            'estado'      => $this->resource->estado,
            'permissions' => $this->when(
                $this->resource->relationLoaded('permissions'),
                fn () => $this->resource->permissions->pluck('nombre'),
            ),
            'created_at'  => $this->resource->created_at?->toDateTimeString(),
            'updated_at'  => $this->resource->updated_at?->toDateTimeString(),
        ];
    }
}
