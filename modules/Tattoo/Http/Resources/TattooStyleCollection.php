<?php

namespace Modules\Tattoo\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Tattoo\Models\TattooStyle;

class TattooStyleCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function (TattooStyle $row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'description' => $row->description,
                'active' => (bool) $row->active,
                'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
}