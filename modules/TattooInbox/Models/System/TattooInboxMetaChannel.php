<?php

namespace Modules\TattooInbox\Models\System;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class TattooInboxMetaChannel extends Model
{
    use UsesSystemConnection;

    protected $table = 'tattoo_inbox_meta_channels';

    protected $fillable = [
        'tenant_website_id',
        'tenant_hostname_id',
        'tenant_fqdn',
        'platform',
        'meta_identifier_type',
        'meta_identifier_value',
        'label',
        'is_active',
        'last_seen_at',
        'last_payload_at',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_payload_at' => 'datetime',
        'meta' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}