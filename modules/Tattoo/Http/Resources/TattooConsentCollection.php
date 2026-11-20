<?php

namespace Modules\Tattoo\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Tattoo\Models\TattooConsent;

class TattooConsentCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function (TattooConsent $row) {
            return [
                'id' => $row->id,
                'tattoo_appointment_id' => $row->tattoo_appointment_id,
                'signed_date' => $row->signed_date,
                'accepted' => (bool) $row->accepted,
                'medical_notes' => $row->medical_notes,
                'allergies' => $row->allergies,
                'observations' => $row->observations,
                'signature_name' => $row->signature_name,
                'customer_name' => optional(optional($row->appointment)->person)->name,
                'artist_name' => optional(optional($row->appointment)->artist)->stage_name,
                'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
}