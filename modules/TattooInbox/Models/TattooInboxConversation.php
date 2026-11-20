<?php

namespace Modules\TattooInbox\Models;

use App\Models\Tenant\ModelTenant;

class TattooInboxConversation extends ModelTenant
{
    protected $table = 'tattoo_inbox_conversations';

    protected $fillable = [
        'lead_id',
        'channel',
        'external_thread_id',
        'status',
        'last_message_at',
        'last_incoming_at',
        'last_outgoing_at',
        'quote_status',
        'assigned_user_id',
        'bot_enabled',
        'taken_by_user_id',
        'taken_at',
        'stage',
        'meta',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_incoming_at' => 'datetime',
        'last_outgoing_at' => 'datetime',
        'taken_at' => 'datetime',
        'bot_enabled' => 'boolean',
        'meta' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(TattooInboxLead::class, 'lead_id');
    }

    public function messages()
    {
        return $this->hasMany(TattooInboxMessage::class, 'conversation_id');
    }

    public function quotes()
    {
        return $this->hasMany(TattooInboxQuote::class, 'conversation_id');
    }

    public function latestQuote()
    {
        return $this->hasOne(TattooInboxQuote::class, 'conversation_id')->orderBy('id', 'desc');
    }
}
