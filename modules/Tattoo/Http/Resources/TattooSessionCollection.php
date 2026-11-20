<?php

namespace Modules\Tattoo\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Tattoo\Models\TattooSession;

class TattooSessionCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function (TattooSession $row) {
            return [
                'id' => $row->id,
                'tattoo_appointment_id' => $row->tattoo_appointment_id,
                'session_number' => $row->session_number,
                'session_date' => $row->session_date,
                'session_time' => $row->session_time,
                'status' => $row->status,
                'price' => $row->price,
                'payment_amount' => $row->payment_amount,
                'notes' => $row->notes,
                'customer_name' => optional(optional($row->appointment)->person)->name,
                'artist_name' => optional(optional($row->appointment)->artist)->stage_name,
                'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
}