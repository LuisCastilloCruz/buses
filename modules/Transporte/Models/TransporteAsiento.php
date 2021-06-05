<?php

namespace Modules\Transporte\Models;

use App\Models\Tenant\ModelTenant;

class TransporteAsiento extends ModelTenant
{
    //

    protected $table = 'transporte_vehiculo_asientos';

    protected $fillable = [
        'piso',
        'estado_asiento_id',
        'numero_asiento',
        'top',
        'left',
        'vehiculo_id',
        'fecha_desocupado',
        'type'
    ];

}
