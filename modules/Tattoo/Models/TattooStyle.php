<?php

namespace Modules\Tattoo\Models;

use App\Models\Tenant\ModelTenant;

class TattooStyle extends ModelTenant
{
    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(TattooAppointment::class, 'tattoo_style_id');
    }
}