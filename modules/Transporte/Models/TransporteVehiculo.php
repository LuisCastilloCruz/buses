<?php

namespace Modules\Transporte\Models;
use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransporteVehiculo extends ModelTenant
{
    protected $table = 'transporte_vehiculos';

    protected $fillable = ['placa','nombre','asientos','pisos'];

    public function seats() : HasMany{
        //traigo solo
        return $this->hasMany(TransporteAsiento::class,'vehiculo_id','id');
    }

}
