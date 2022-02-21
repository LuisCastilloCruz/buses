<?php

namespace Modules\Restaurante\Models;

use App\Models\Tenant\ModelTenant;
use http\Env\Request;

class Nivel extends ModelTenant
{
    protected $table = 'restaurante_niveles';
    protected $fillable = ['id','nombre', 'activo'];

	public function getActiveAttribute($value)
	{
		return $value ? true : false;
	}
    public function mesas(Request $request){
        return Mesa::where('nivel_id', $request)->nivel_id;
    }
}
