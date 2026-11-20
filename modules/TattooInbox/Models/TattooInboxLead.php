<?php

namespace Modules\TattooInbox\Models;

use App\Models\Tenant\ModelTenant;

class TattooInboxLead extends ModelTenant
{
    protected $table = 'tattoo_inbox_leads';

    protected $fillable = [
        'channel',
        'external_user_id',
        'full_name',
        'phone',
        'instagram_username',
        'email',
        'status',
        'last_message_at',
        'notes',
        'meta',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'meta' => 'array',
    ];

    public function conversations()
    {
        return $this->hasMany(TattooInboxConversation::class, 'lead_id');
    }
}