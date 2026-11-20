<?php

namespace Modules\TattooInbox\Models;

use App\Models\Tenant\ModelTenant;

class TattooInboxMessage extends ModelTenant
{
    protected $table = 'tattoo_inbox_messages';

    protected $fillable = [
        'conversation_id',
        'lead_id',
        'channel',
        'direction',
        'message_type',
        'external_message_id',
        'message_text',
        'media_url',
        'media_mime',
        'payload',
        'sent_by_user_id',
        'is_automated',
        'status',
        'received_at',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_automated' => 'boolean',
        'received_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(TattooInboxConversation::class, 'conversation_id');
    }

    public function lead()
    {
        return $this->belongsTo(TattooInboxLead::class, 'lead_id');
    }
}