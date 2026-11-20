<?php

namespace Modules\TattooInbox\Models;

use App\Models\Tenant\ModelTenant;

class TattooInboxQuote extends ModelTenant
{
    protected $table = 'tattoo_inbox_quotes';

    protected $fillable = [
        'conversation_id',
        'lead_id',
        'tattoo_artist_id',
        'body_zone',
        'style',
        'size_cm',
        'size_text',
        'is_color',
        'is_coverup',
        'detail_level',
        'has_reference',
        'reference_count',
        'customer_idea',
        'price_min',
        'price_max',
        'suggested_price',
        'currency',
        'status',
        'quoted_at',
        'notes',
        'meta',
    ];

    protected $casts = [
        'size_cm' => 'decimal:2',
        'is_color' => 'boolean',
        'is_coverup' => 'boolean',
        'has_reference' => 'boolean',
        'reference_count' => 'integer',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'suggested_price' => 'decimal:2',
        'quoted_at' => 'datetime',
        'meta' => 'array',
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