<?php

namespace Modules\Odontologia\Models;

class Dentist extends \App\Models\Tenant\ModelTenant
{
    protected $table = 'odontologia_dentists';
    public $timestamps = false;
    protected $fillable = ['professional_id', 'specialty_id', 'registration', 'active'];
}
