<?php

namespace Modules\Tattoo\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;

class TattooAppointment extends ModelTenant
{
    protected $fillable = [
        'person_id',
        'tattoo_artist_id',
        'tattoo_style_id',
        'appointment_date',
        'appointment_time',
        'status',
        'body_zone',
        'tattoo_size',
        'estimated_price',
        'deposit_amount',
        'idea',
        'observations',
    ];

    protected $casts = [
        'person_id' => 'integer',
        'tattoo_artist_id' => 'integer',
        'tattoo_style_id' => 'integer',
        'estimated_price' => 'float',
        'deposit_amount' => 'float',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function artist()
    {
        return $this->belongsTo(TattooArtist::class, 'tattoo_artist_id');
    }

    public function style()
    {
        return $this->belongsTo(TattooStyle::class, 'tattoo_style_id');
    }

    public function sessions()
    {
        return $this->hasMany(TattooSession::class, 'tattoo_appointment_id');
    }

    public function consent()
    {
        return $this->hasOne(TattooConsent::class, 'tattoo_appointment_id');
    }
}