<?php

namespace Modules\Transporte\Models;

use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\ModelTenant;

class TransporteDestino extends ModelTenant
{
	protected $table = 'transporte_destinos';

	protected $fillable = ['nombre', 'district_id'];

	public function district()
	{
		return $this->belongsTo(District::class, 'district_id');
	}

}
