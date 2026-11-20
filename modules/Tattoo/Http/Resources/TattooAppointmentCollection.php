<?php

namespace Modules\Tattoo\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Tattoo\Models\TattooAppointment;

class TattooAppointmentCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function (TattooAppointment $row) {
            return [
                'id' => $row->id,
                'person_id' => $row->person_id,
                'customer_name' => optional($row->person)->name,
                'customer_number' => optional($row->person)->number,

                'tattoo_artist_id' => $row->tattoo_artist_id,
                'artist_name' => optional($row->artist)->stage_name,

                'tattoo_style_id' => $row->tattoo_style_id,
                'style_name' => optional($row->style)->name,

                'appointment_date' => $row->appointment_date,
                'appointment_time' => $row->appointment_time,
                'status' => $row->status,
                'body_zone' => $row->body_zone,
                'tattoo_size' => $row->tattoo_size,
                'estimated_price' => $row->estimated_price,
                'deposit_amount' => $row->deposit_amount,
                'idea' => $row->idea,
                'observations' => $row->observations,

                'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
}