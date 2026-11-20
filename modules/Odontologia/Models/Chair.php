<?php

namespace Modules\Odontologia\Models;

class Chair extends \App\Models\Tenant\ModelTenant
{
    protected $table = 'odontologia_chairs';
    public $timestamps = false;
    protected $fillable = ['resource_id', 'room'];
}
