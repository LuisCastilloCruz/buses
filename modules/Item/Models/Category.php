<?php

namespace Modules\Item\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;

class Category extends ModelTenant
{

    protected $fillable = [
        'name',
        'printer'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function scopeFilterForTables($query)
    {
        return $query->select('id', 'name')->orderBy('name');
    }

    public function getRowResourceApi()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'selected' => false,
        ];
    }
}
