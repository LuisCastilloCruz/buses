<?php

namespace Modules\Tattoo\Models;

use App\Models\Tenant\ModelTenant;

class TattooConsent extends ModelTenant
{
    protected $fillable = [
        'tattoo_appointment_id',
        'signed_date',
        'accepted',
        'medical_notes',
        'allergies',
        'observations',
        'signature_name',
    ];

    protected $casts = [
        'tattoo_appointment_id' => 'integer',
        'accepted' => 'boolean',
    ];

    public function appointment()
    {
        return $this->belongsTo(TattooAppointment::class, 'tattoo_appointment_id');
    }
}