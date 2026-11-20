<?php

namespace Modules\Tattoo\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Tattoo\Models\TattooArtist;

class TattooArtistCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function (TattooArtist $row) {
            return [
                'id' => $row->id,
                'user_id' => $row->user_id,
                'user_name' => optional($row->user)->name,
                'stage_name' => $row->stage_name,
                'specialty' => $row->specialty,
                'bio' => $row->bio,
                'instagram' => $row->instagram,
                'active' => (bool) $row->active,
                'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
}