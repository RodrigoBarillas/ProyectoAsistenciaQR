<?php

namespace App\Http\Resources\Permission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->resource->id,
            'nombre'      => $this->resource->nombre instanceof \BackedEnum
                ? $this->resource->nombre->value
                : $this->resource->nombre,
            'descripcion' => $this->resource->descripcion,
            'created_at'  => $this->resource->created_at?->toDateTimeString(),
        ];
    }
}
