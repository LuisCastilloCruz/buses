<?php

namespace Modules\Odontologia\Models;

class Specialty extends \App\Models\Tenant\ModelTenant
{
    protected $table = 'odontologia_specialties';
    public $timestamps = false;
    protected $fillable = ['name', 'active'];
}
