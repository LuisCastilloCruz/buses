<?php

namespace Modules\Restaurante\Models;

use App\Models\Tenant\ModelTenant;
class Mesa extends ModelTenant
{
    protected $table = 'restaurante_mesas';
    protected $fillable = ['numero','nivel_id', 'activo'];

	public function getActiveAttribute($value)
	{
		return $value ? true : false;
	}
}
