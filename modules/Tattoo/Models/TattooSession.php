<?php

namespace Modules\Tattoo\Models;

use App\Models\Tenant\ModelTenant;

class TattooSession extends ModelTenant
{
    protected $fillable = [
        'tattoo_appointment_id',
        'session_number',
        'session_date',
        'session_time',
        'status',
        'price',
        'payment_amount',
        'notes',
    ];

    protected $casts = [
        'tattoo_appointment_id' => 'integer',
        'session_number' => 'integer',
        'price' => 'float',
        'payment_amount' => 'float',
    ];

    public function appointment()
    {
        return $this->belongsTo(TattooAppointment::class, 'tattoo_appointment_id');
    }
}