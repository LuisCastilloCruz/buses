<?php

namespace Modules\TattooInbox\Models;

use App\Models\Tenant\ModelTenant;

class TattooInboxQuoteRule extends ModelTenant
{
    protected $table = 'tattoo_inbox_quote_rules';

    protected $fillable = [
        'rule_key',
        'rule_value',
        'description',
        'active',
    ];

    protected $casts = [
        'rule_value' => 'decimal:4',
        'active' => 'boolean',
    ];
}