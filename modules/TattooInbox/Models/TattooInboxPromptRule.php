<?php

namespace Modules\TattooInbox\Models;

use App\Models\Tenant\ModelTenant;

class TattooInboxPromptRule extends ModelTenant
{
    protected $table = 'tattoo_inbox_prompt_rules';

    protected $fillable = [
        'rule_type',
        'rule_key',
        'rule_value',
        'description',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];
}