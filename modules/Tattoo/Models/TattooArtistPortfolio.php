<?php

namespace Modules\Tattoo\Models;

use App\Models\Tenant\ModelTenant;

class TattooArtistPortfolio extends ModelTenant
{
    protected $fillable = [
        'tattoo_artist_id',
        'title',
        'description',
        'image_path',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'tattoo_artist_id' => 'integer',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function artist()
    {
        return $this->belongsTo(TattooArtist::class, 'tattoo_artist_id');
    }
}