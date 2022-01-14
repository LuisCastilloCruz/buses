<?php

namespace Modules\Restaurante\Models;

use App\Models\Tenant\ModelTenant;

class Nivel extends ModelTenant
{
    protected $table = 'restaurante_niveles';
    protected $fillable = ['nombre', 'activo'];

	public function getActiveAttribute($value)
	{
		return $value ? true : false;
	}
}
