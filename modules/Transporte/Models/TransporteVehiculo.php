<?php

namespace Modules\Transporte\Models;
use App\Models\Tenant\ModelTenant;

class TransporteVehiculo extends ModelTenant
{
    protected $table = 'transporte_vehiculos';

    protected $fillable = ['description','placa','nombre','asientos','pisos'];

}
