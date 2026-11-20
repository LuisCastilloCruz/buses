<?php

namespace Modules\Tattoo\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\User;

class TattooArtist extends ModelTenant
{
    protected $fillable = [
        'user_id',
        'stage_name',
        'specialty',
        'bio',
        'instagram',
        'photo_path',
        'active',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'active' => 'boolean',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(TattooAppointment::class, 'tattoo_artist_id');
    }

    public function portfolios()
    {
        return $this->hasMany(TattooArtistPortfolio::class, 'tattoo_artist_id')
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    public function getPhotoUrlAttribute()
    {
        if (!$this->photo_path) {
            return null;
        }

        return asset('storage/' . $this->photo_path);
    }
}